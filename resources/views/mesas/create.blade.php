<x-layouts::app :title="__('Crear Mesa')">
    <div class="px-8">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Agregar nueva mesa</flux:heading>
            <flux:button
                type="button"
                href="{{ route('mesas.index') }}"
                icon:trailing="arrow-left"
                variant="primary"
            >
                Volver
            </flux:button>
        </div>
        <form action="{{ route('mesas.store') }}" method="POST">
            @csrf
            <flux:select class="mb-4" label="Ubicación" name="ubicacion" placeholder="Elige ubicación..." >
                @foreach($ubicaciones as $ubicacion)
                    <flux:select.option value="{{ $ubicacion }}" :selected="old('ubicacion') === $ubicacion">
                        {{ $ubicacion }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input label="Cantidad de personas" name="cantidad_personas" value="{{ old('cantidad_personas') }}" />

            <flux:button class="mt-4" type="submit" variant="primary" color="cyan">Agregar mesa</flux:button>
        </form>
    </div>
</x-layouts::app>
