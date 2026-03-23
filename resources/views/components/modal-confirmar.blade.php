@props(['titulo' => '¿Estás seguro?', 'mensaje', 'action', 'boton' => 'Confirmar'])

<div x-data="{ abierto: false }">
    <span @click="abierto = true">{{ $slot }}</span>

    <template x-teleport="body">
        <div x-show="abierto" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <flux:card class="w-full max-w-md p-6" style="background-color: #313135;">
                <flux:heading size="lg" class="mb-2">{{ $titulo }}</flux:heading>
                <flux:text class="mb-6">{{ $mensaje }}</flux:text>
                <div class="flex justify-end gap-3">
                    <flux:button @click="abierto = false" variant="ghost">Cancelar</flux:button>
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" icon="trash" variant="primary" color="red">{{ $boton }}</flux:button>
                    </form>
                </div>
            </flux:card>
        </div>
    </template>
</div>