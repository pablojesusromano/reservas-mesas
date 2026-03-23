<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @if(session('error'))
            <flux:callout variant="danger" class="mb-4">{{ session('error') }}</flux:callout>
        @endif
        @if(session('success'))
            <flux:callout variant="success" class="mb-4">{{ session('success') }}</flux:callout>
        @endif
        @if($errors->any())
            <flux:callout variant="danger" class="mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </flux:callout>
        @endif
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>