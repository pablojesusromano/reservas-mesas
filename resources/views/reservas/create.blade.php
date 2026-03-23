<x-layouts::app :title="__('Crear Reserva')">
    <div class="px-8">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Registrar Reserva</flux:heading>
            <flux:button
                type="button"
                href="{{ route('reservas.index') }}"
                icon:trailing="arrow-left"
                variant="primary"
            >
                Volver
            </flux:button>
        </div>
        <form action="{{ route('reservas.store') }}" method="POST">
            @csrf
            <flux:input class="mb-4" label="Nombre del solicitante" name="nombre_solicitante" value="{{ old('nombre_solicitante') }}" />
            <flux:input class="mb-4" label="Fecha" name="fecha" type="date" min="{{ today()->format('Y-m-d') }}" value="{{ old('fecha') }}" />
            <flux:input class="mb-4" label="Hora de inicio" name="hora_inicio" type="time" value="{{ old('hora_inicio') }}" />
            <flux:input class="mb-4" label="Cantidad de personas" name="cantidad_personas" type="number" min="1" value="{{ old('cantidad_personas') }}" />
            <flux:button class="mt-4" type="submit" variant="primary" color="cyan">Registrar reserva</flux:button>
        </form>
    </div>
</x-layouts::app>