<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReservaService {

    private const CACHE_TTL_MINUTOS = 15;
    private const MAX_MESAS_POR_RESERVA = 3;
    private const MINUTOS_EN_UN_DIA = 1440;

    private function validarHorarioPermitido($fecha, $horaInicio): bool
    {
        $inicio = Carbon::parse("$fecha $horaInicio");
        $finCalculado = $inicio->copy()->addHours(Reserva::DURACION_DEFAULT);

        if ($inicio->lessThan(now()->addMinutes(15))) {
            return false;
        }

        $dia = $inicio->dayOfWeek();

        $horarioPermitido = false;

        if($dia >= 1 && $dia <= 5) {
            $horarioPermitido = $inicio->format('H:i') >= '10:00' && $finCalculado <= Carbon::parse($fecha . ' 00:00:00')->addDay();
        } elseif($dia == 6) {
            $horarioPermitido = $inicio->format('H:i') >= '22:00' && $finCalculado <= Carbon::parse($fecha . ' 02:00:00')->addDay();
        } elseif($dia == 0) {
            $horarioPermitido = $inicio->format('H:i') >= '12:00' && $finCalculado->format('H:i') <= '16:00';
        }

        return $horarioPermitido;
    }

    private function ladosSinOcuparDeMesa(int $capacidad): int
    {
        return max(0, 4 - $capacidad);
    }

    private function lugaresQuePierdeMesa(int $capacidadDeLaMesa, int $unionesEnQueParticipa): int
    {
        $ladosSinOcupar = $this->ladosSinOcuparDeMesa($capacidadDeLaMesa);
        return max(0, $unionesEnQueParticipa - $ladosSinOcupar);
    }

    private function calcularCapacidadCombinada(Collection $mesas): int
    {
        $cantidadDeMesas = $mesas->count();

        if ($cantidadDeMesas <= 1) {
            return $mesas->sum('cantidad_personas');
        }

        $capacidades = $mesas->pluck('cantidad_personas')->values();
        $sumaTotal = $capacidades->sum();

        if ($cantidadDeMesas === 2) {
            $penalizacion = $this->lugaresQuePierdeMesa($capacidades[0], 1)
                          + $this->lugaresQuePierdeMesa($capacidades[1], 1);
            return $sumaTotal - $penalizacion;
        }

        $menorPenalizacion = null;

        foreach ($capacidades as $indiceMedio => $capacidadDelMedio) {
            $penalizacion = $this->lugaresQuePierdeMesa($capacidadDelMedio, 2);

            foreach ($capacidades as $indice => $capacidad) {
                if ($indice !== $indiceMedio) {
                    $penalizacion += $this->lugaresQuePierdeMesa($capacidad, 1);
                }
            }

            if ($menorPenalizacion === null || $penalizacion < $menorPenalizacion) {
                $menorPenalizacion = $penalizacion;
            }
        }

        return $sumaTotal - $menorPenalizacion;
    }

    private function horaAMinutos(string $hora): int
    {
        [$horas, $minutos] = explode(':', $hora);
        return (int)$horas * 60 + (int)$minutos;
    }

    private function horariosSeSuperponen(string $inicioA, string $finA, string $inicioB, string $finB): bool
    {
        $inicioAEnMinutos = $this->horaAMinutos($inicioA);
        $finAEnMinutos = $this->horaAMinutos($finA);
        $inicioBEnMinutos = $this->horaAMinutos($inicioB);
        $finBEnMinutos = $this->horaAMinutos($finB);

        if ($finAEnMinutos <= $inicioAEnMinutos) {
            $finAEnMinutos += self::MINUTOS_EN_UN_DIA;
        }

        if ($finBEnMinutos <= $inicioBEnMinutos) {
            $finBEnMinutos += self::MINUTOS_EN_UN_DIA;
        }

        return $inicioAEnMinutos < $finBEnMinutos && $finAEnMinutos > $inicioBEnMinutos;
    }

    private function obtenerMesasDisponiblesPorUbicacion(string $fecha, string $horaInicio, string $horaFin): Collection
    {
        $cacheKey = "disponibilidad_{$fecha}_{$horaInicio}_{$horaFin}";

        return Cache::remember($cacheKey, self::CACHE_TTL_MINUTOS * 60, function() use ($fecha, $horaInicio, $horaFin) {
            $reservasDelDia = Reserva::where('fecha', $fecha)->with('mesas')->get();

            $idsOcupados = $reservasDelDia->filter(function($reserva) use ($horaInicio, $horaFin) {
                return $this->horariosSeSuperponen($horaInicio, $horaFin, $reserva->hora_inicio, $reserva->hora_fin);
            })->pluck('mesas')->flatten()->pluck('id');

            return Mesa::whereNotIn('id', $idsOcupados)
                ->get()
                ->groupBy('ubicacion');
        });
    }

    private function obtenerMesasDisponibles($fecha, $horaInicio, $horaFin, $cantidadPersonas): ?Collection
    {
        $mesasPorUbicacion = $this->obtenerMesasDisponiblesPorUbicacion($fecha, $horaInicio, $horaFin);

        foreach (Mesa::UBICACIONES as $ubicacion) {
            if (!isset($mesasPorUbicacion[$ubicacion])) {
                continue;
            }

            $mesas = $mesasPorUbicacion[$ubicacion]->take(self::MAX_MESAS_POR_RESERVA);
            $capacidadTotal = $this->calcularCapacidadCombinada($mesas);

            if ($capacidadTotal >= $cantidadPersonas) {
                $seleccionadas = collect();

                foreach ($mesas as $mesa) {
                    $seleccionadas->push($mesa);
                    $acumulado = $this->calcularCapacidadCombinada($seleccionadas);
                    if ($acumulado >= $cantidadPersonas) {
                        return $seleccionadas;
                    }
                }
            }
        }

        return null;
    }

    private function invalidarCache(string $fecha, string $horaInicio, string $horaFin): void
    {
        $cacheKey = "disponibilidad_{$fecha}_{$horaInicio}_{$horaFin}";
        Cache::forget($cacheKey);
    }

    public function consultarDisponibilidad(string $fecha, string $horaInicio): array
    {
        $horaFin = Carbon::parse($horaInicio)->addHours(Reserva::DURACION_DEFAULT)->format('H:i');
        $mesasPorUbicacion = $this->obtenerMesasDisponiblesPorUbicacion($fecha, $horaInicio, $horaFin);

        $resultado = [];

        foreach (Mesa::UBICACIONES as $ubicacion) {
            $mesas = $mesasPorUbicacion[$ubicacion] ?? collect();
            $mesasLimitadas = $mesas->take(self::MAX_MESAS_POR_RESERVA);

            $resultado[$ubicacion] = [
                'mesas_disponibles' => $mesas->count(),
                'capacidad_maxima' => $this->calcularCapacidadCombinada($mesasLimitadas),
                'mesas' => $mesas->map(fn($mesa) => [
                    'numero' => $mesa->numero,
                    'cantidad_personas' => $mesa->cantidad_personas,
                ]),
            ];
        }

        return $resultado;
    }

    public function validarDisponibilidad($fecha, $horaInicio, $horaFin, $cantidadPersonas): bool
    {
        if(!$this->validarHorarioPermitido($fecha, $horaInicio, $horaFin)) {
            return false;
        }

        return $this->obtenerMesasDisponibles($fecha, $horaInicio, $horaFin, $cantidadPersonas) !== null;
    }

    public function crearReserva(array $data): Reserva
    {
        $data['hora_fin'] = Carbon::parse($data['hora_inicio'])->addHours(Reserva::DURACION_DEFAULT)->format('H:i');

        if (!$this->validarHorarioPermitido($data['fecha'], $data['hora_inicio'])) {
            throw new \RuntimeException('El horario solicitado no está permitido.');
        }

        $mesas = $this->obtenerMesasDisponibles($data['fecha'], $data['hora_inicio'], $data['hora_fin'], $data['cantidad_personas']);

        if (!$mesas) {
            throw new \RuntimeException('No hay disponibilidad para la fecha y hora solicitadas.');
        }

        return DB::transaction(function() use ($data, $mesas) {
            $reserva = Reserva::create($data);
            $reserva->mesas()->attach($mesas->pluck('id'));

            $this->invalidarCache($data['fecha'], $data['hora_inicio'], $data['hora_fin']);

            return $reserva;
        });
    }

    public function cancelarReserva(Reserva $reserva): void
    {
        DB::transaction(function() use ($reserva) {
            $reserva->mesas()->detach();
            $reserva->delete();

            $this->invalidarCache($reserva->fecha, $reserva->hora_inicio, $reserva->hora_fin);
        });
    }
}