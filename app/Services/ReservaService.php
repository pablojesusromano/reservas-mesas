<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReservaService {

    private function validarHorarioPermitido($fecha, $horaInicio): bool
    {
        $inicio = Carbon::parse("$fecha $horaInicio");
        $finCalculado = $inicio->copy()->addHours(Reserva::DURACION_DEFAULT);

        if ($inicio->lessThan(now()->addMinutes(15))) {
            return false;
        }

        $dia = $inicio->dayOfWeek(); // 0: Domingo, 1 a 5: L a V, 6: Sábado.

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

    private function obtenerMesasDisponibles($fecha, $horaInicio, $horaFin, $cantidadPersonas): ?Collection
    {
        $cacheKey = "disponibilidad_{$fecha}_{$horaInicio}_{$horaFin}";

        $mesasPorUbicacion = Cache::remember($cacheKey, 60, function() use ($fecha, $horaInicio, $horaFin) {
            $idsOcupados = Reserva::where('fecha', $fecha)
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->with('mesas')
                ->get()
                ->pluck('mesas')
                ->flatten()
                ->pluck('id');

            return Mesa::whereNotIn('id', $idsOcupados)
                ->get()
                ->groupBy('ubicacion');
        });

        foreach (Mesa::UBICACIONES as $ubicacion) {
            if (!isset($mesasPorUbicacion[$ubicacion])) {
                continue;
            }

            $mesas = $mesasPorUbicacion[$ubicacion]->take(3);
            $capacidadTotal = $mesas->sum('cantidad_personas');

            if ($capacidadTotal >= $cantidadPersonas) {
                $seleccionadas = collect();
                $acumulado = 0;

                foreach ($mesas as $mesa) {
                    $seleccionadas->push($mesa);
                    $acumulado += $mesa->cantidad_personas;
                    if ($acumulado >= $cantidadPersonas) {
                        return $seleccionadas;
                    }
                }
            }
        }

        return null;
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

            $cacheKey = "disponibilidad_{$data['fecha']}_{$data['hora_inicio']}_{$data['hora_fin']}";
            Cache::forget($cacheKey);

            return $reserva;
        });
    }

    public function cancelarReserva(Reserva $reserva): void
    {
        DB::transaction(function() use ($reserva) {
            $reserva->mesas()->detach();
            $reserva->delete();

            $cacheKey = "disponibilidad_{$reserva->fecha}_{$reserva->hora_inicio}_{$reserva->hora_fin}";
            Cache::forget($cacheKey);
        });
    }
}