<x-layouts::app :title="__('Editar Mesa')">
    <div class="px-8">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Editar Mesa {{ $mesa->numero }} - Ubicación {{ $mesa->ubicacion }}</flux:heading>
            <flux:button
                type="button"
                href="{{ route('mesas.index') }}"
                icon:trailing="arrow-left"
                variant="primary"
            >
                Volver
            </flux:button>
        </div>
        <form action="{{ route('mesas.update', $mesa->id) }}" method="POST">
            @csrf
            @method('PUT')
            <flux:select class="mb-4" label="Ubicación" name="ubicacion" placeholder="Elige ubicación...">
                @foreach($ubicaciones as $ubicacion)
                    <flux:select.option value="{{ $ubicacion }}" :selected="$mesa->ubicacion === $ubicacion">
                        {{ $ubicacion }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:input label="Cantidad de personas" name="cantidad_personas" value="{{ $mesa->cantidad_personas }}" />
            <flux:button class="mt-4" type="submit" variant="primary" color="cyan">Guardar cambios</flux:button>
        </form>
    </div>
</x-layouts::app>