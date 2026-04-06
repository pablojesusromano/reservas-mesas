<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservaRequest;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function __construct(private ReservaService $reservaService) {}
    
    public function index()
    {
        $reservasPorFecha = Reserva::select(
                'reservas.*',
                'mesas.ubicacion',
                DB::raw('GROUP_CONCAT(mesas.numero) as numeros_mesas')
            )
            ->join('mesa_reserva', 'reservas.id', '=', 'mesa_reserva.reserva_id')
            ->join('mesas', 'mesa_reserva.mesa_id', '=', 'mesas.id')
            ->whereNull('mesas.deleted_at')
            ->whereNull('reservas.deleted_at')
            ->where('reservas.fecha', '>=', now()->toDateString())
            ->groupBy('reservas.id', 'mesas.ubicacion')
            ->orderBy('reservas.fecha')
            ->orderBy('mesas.ubicacion')
            ->orderBy('reservas.hora_inicio')
            ->get()
            ->groupBy(['fecha', 'ubicacion']);

        return view('reservas.index', [
            'reservas' => $reservasPorFecha,
        ]);
    }

    public function create()
    {
        return view('reservas.create');
    }

    public function disponibilidad(Request $request)
    {
        $fecha = $request->query('fecha');
        $horaInicio = $request->query('hora_inicio');

        $disponibilidad = null;

        if ($fecha && $horaInicio) {
            $disponibilidad = $this->reservaService->consultarDisponibilidad($fecha, $horaInicio);
        }

        return view('reservas.disponibilidad', [
            'disponibilidad' => $disponibilidad,
            'fecha' => $fecha,
            'horaInicio' => $horaInicio,
        ]);
    }

    public function store(StoreReservaRequest $request)
    {
        try {
            $data = $request->validated();
            $this->reservaService->crearReserva($data);
            return redirect()->route('reservas.index')->with('success', 'Reserva creada exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Reserva $reserva)
    {
        try {
            $this->reservaService->cancelarReserva($reserva);
            return redirect()->route('reservas.index')->with('success', 'Reserva cancelada exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

