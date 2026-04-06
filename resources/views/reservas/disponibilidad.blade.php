<x-layouts::app :title="__('Disponibilidad')">
    <div class="px-8">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Consultar Disponibilidad</flux:heading>
            <flux:button
                type="button"
                href="{{ route('reservas.index') }}"
                icon:trailing="arrow-left"
                variant="primary"
            >
                Volver
            </flux:button>
        </div>

        <form method="GET" action="{{ route('reservas.disponibilidad') }}" class="mb-6">
            <div class="flex gap-4 items-end flex-wrap">
                <flux:input label="Fecha" name="fecha" type="date" min="{{ today()->format('Y-m-d') }}" value="{{ $fecha ?? '' }}" />
                <flux:input label="Hora de inicio" name="hora_inicio" type="time" value="{{ $horaInicio ?? '' }}" />
                <flux:button type="submit" variant="primary" color="cyan">Consultar</flux:button>
            </div>
        </form>

        @if($disponibilidad)
            <flux:card size="sm" class="p-4 mb-4">
                <flux:text>
                    Disponibilidad para el
                    <strong>{{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F \d\e Y') }}</strong>
                    a las <strong>{{ $horaInicio }} hs</strong>
                </flux:text>
            </flux:card>

            @foreach($disponibilidad as $ubicacion => $info)
                <div class="mb-6">
                    <flux:heading size="sm" class="mb-2">Ubicación {{ $ubicacion }}</flux:heading>

                    @if($info['mesas_disponibles'] === 0)
                        <flux:callout variant="danger">Sin mesas disponibles en esta ubicación.</flux:callout>
                    @else
                        <div class="flex gap-4 mb-3 flex-wrap">
                            <flux:badge color="green">{{ $info['mesas_disponibles'] }} {{ $info['mesas_disponibles'] === 1 ? 'mesa disponible' : 'mesas disponibles' }}</flux:badge>
                            <flux:badge color="blue">Capacidad máxima combinada: {{ $info['capacidad_maxima'] }} personas</flux:badge>
                        </div>

                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Mesa Nº</flux:table.column>
                                <flux:table.column>Capacidad Individual</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($info['mesas'] as $mesa)
                                    <flux:table.row>
                                        <flux:table.cell>Mesa {{ $mesa['numero'] }}</flux:table.cell>
                                        <flux:table.cell variant="strong">{{ $mesa['cantidad_personas'] }} personas</flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @endif
                </div>
            @endforeach
        @elseif($fecha || $horaInicio)
            <flux:callout variant="warning">Completá ambos campos para consultar la disponibilidad.</flux:callout>
        @else
            <flux:card size="sm" class="p-6 mt-4">
                <flux:heading>Seleccioná una fecha y hora para consultar la disponibilidad.</flux:heading>
                <flux:text class="mt-2">Los resultados se almacenan en caché por 15 minutos para optimizar el rendimiento.</flux:text>
            </flux:card>
        @endif
    </div>
</x-layouts::app>
