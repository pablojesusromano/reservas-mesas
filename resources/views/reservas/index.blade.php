<x-layouts::app :title="__('Reservas')">
    <div class="px-8">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Gestión de Reservas</flux:heading>
            <flux:button
                href="{{ route('reservas.create') }}"
                icon:trailing="plus"
                variant="primary"
                color="green"
            >
                Registrar Reserva
            </flux:button>
        </div>

        @if($reservas->isEmpty())
            <flux:card size="sm" class="p-6 mt-4">
                <flux:heading>No hay reservas para mostrar en este momento.</flux:heading>
                <flux:text class="mt-2">Realiza una reserva nueva para visualizar la tabla.</flux:text>
            </flux:card>
        @else
            @foreach($reservas as $fecha => $ubicaciones)
                <div class="mb-8">
                    <flux:card color="blue" size="lg" class="mb-4">
                        <flux:heading size="lg">{{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F \d\e Y') }}</flux:heading>
                    </flux:card>

                    @foreach($ubicaciones as $ubicacion => $reservasDelGrupo)
                        <div class="mb-4">
                            <flux:heading size="sm" class="mb-2">Ubicación {{ $ubicacion }}</flux:heading>
                            <flux:table>
                            <flux:table.columns>
                                <flux:table.column style="width: 25%">A nombre de</flux:table.column>
                                <flux:table.column style="width: 100px">Hora Inicio</flux:table.column>
                                <flux:table.column style="width: 100px">Hora Fin</flux:table.column>
                                <flux:table.column style="width: 100px">Personas</flux:table.column>
                                <flux:table.column style="width: 25%">Mesas</flux:table.column>
                                <flux:table.column style="width: 80px">Acción</flux:table.column>
                            </flux:table.columns>
                                <flux:table.rows>
                                    @foreach($reservasDelGrupo as $reserva)
                                        <flux:table.row>
                                            <flux:table.cell>{{ $reserva->nombre_solicitante }}</flux:table.cell>
                                            <flux:table.cell>{{ $reserva->hora_inicio }}</flux:table.cell>
                                            <flux:table.cell>{{ $reserva->hora_fin }}</flux:table.cell>
                                            <flux:table.cell variant="strong">{{ $reserva->cantidad_personas }} personas</flux:table.cell>
                                            <flux:table.cell>
                                                {{ collect(explode(',', $reserva->numeros_mesas))->map(fn($n) => 'Mesa ' . $n)->join(', ') }}
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <div class="flex gap-2">
                                                    <x-modal-confirmar
                                                        titulo="Cancelar reserva"
                                                        mensaje="¿Seguro que quieres cancelar la Reserva de {{ $reserva->nombre_solicitante }} del {{ \Carbon\Carbon::parse($reserva->fecha)->translatedFormat('l d \d\e F \d\e Y') }} de {{ $reserva->hora_inicio }} a {{ $reserva->hora_fin }}?"
                                                        action="{{ route('reservas.destroy', $reserva->id) }}"
                                                        boton="Cancelar"
                                                    >
                                                        <flux:button icon="trash" variant="primary" color="red" x-tooltip="Cancelar reserva" />
                                                    </x-modal-confirmar>
                                                </div>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</x-layouts::app>