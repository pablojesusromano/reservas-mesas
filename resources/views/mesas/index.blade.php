<x-layouts::app :title="__('Mesas')">
    <div class="px-8">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Gestión de Mesas</flux:heading>
            <flux:button
                href="{{route('mesas.create')}}"
                icon:trailing="plus"
                variant="primary"
                color="green"
            >
                Nueva mesa
            </flux:button>
        </div>
        @if(count($mesas) > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>Ubicación</flux:table.column>
                    <flux:table.column>Número</flux:table.column>
                    <flux:table.column>Cantidad de Personas</flux:table.column>
                    <flux:table.column>Acción</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($mesas as $mesa)
                        <flux:table.row>
                            <flux:table.cell>{{ $mesa->id }}</flux:table.cell>
                            <flux:table.cell>{{ $mesa->ubicacion }}</flux:table.cell>
                            <flux:table.cell>{{ $mesa->numero }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ $mesa->cantidad_personas }} personas</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button href="{{ route('mesas.edit', $mesa->id) }}" icon="pencil" x-tooltip="Editar mesa" />
                                    <x-modal-confirmar
                                        titulo="Eliminar mesa"
                                        mensaje="¿Seguro que quieres eliminar la Mesa {{ $mesa->numero }} de Ubicación {{ $mesa->ubicacion }}?"
                                        action="{{ route('mesas.destroy', $mesa->id) }}"
                                        boton="Eliminar"
                                    >
                                        <flux:button icon="trash" variant="primary" color="red" x-tooltip="Eliminar mesa" />
                                    </x-modal-confirmar>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach 
                </flux:table.rows>
            </flux:table>
        @else
            <flux:card size="sm" class="p-6 mt-4">
                <flux:heading class="flex items-center gap-2">No hay mesas para mostrar en este momento.</flux:heading>
                <flux:text class="mt-2">Carga una mesa nueva para visualizar la tabla.</flux:text>
            </flux:card>
        @endif
    </div>
</x-layouts::app>
