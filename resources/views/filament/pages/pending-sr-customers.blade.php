<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Flujo manual y seguro</x-slot>
        <x-slot name="description">La plataforma ya asignó la Clave SR. Cópiala junto con los datos, crea el cliente manualmente en SoftRestaurant y marca el registro como sincronizado. Esta pantalla nunca escribe en SR.</x-slot>
    </x-filament::section>

    <div class="space-y-4">
        @forelse ($this->customers as $customer)
            <x-filament::section>
                <div class="grid gap-5 lg:grid-cols-3">
                    <div class="space-y-3 lg:col-span-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nombre</p>
                            <div class="flex items-center gap-2">
                                <p class="text-lg font-semibold">{{ $customer->name }}</p>
                                <x-filament::icon-button icon="heroicon-o-clipboard" color="gray" size="sm" x-on:click="navigator.clipboard.writeText(@js($customer->name))" label="Copiar nombre" />
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div><p class="text-xs text-gray-500">Teléfono</p><p>{{ $customer->phone ?: '—' }}</p></div>
                            <div><p class="text-xs text-gray-500">Correo</p><p class="break-all">{{ $customer->email ?: '—' }}</p></div>
                            <div><p class="text-xs text-gray-500">Cumpleaños</p><p>{{ $customer->birthday?->format('d/m/Y') ?: '—' }}</p></div>
                        </div>
                    </div>

                    <form wire:submit="markSynced({{ $customer->id }})" class="space-y-3">
                        <div>
                            <span class="mb-1 block text-sm font-medium">Clave para SoftRestaurant</span>
                            <div class="flex items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                <strong class="font-mono text-lg tracking-wider">{{ $customer->external_id }}</strong>
                                <x-filament::icon-button icon="heroicon-o-clipboard" color="gray" size="sm" x-on:click="navigator.clipboard.writeText(@js($customer->external_id))" label="Copiar Clave SR" />
                            </div>
                        </div>
                        <x-filament::button type="submit" icon="heroicon-o-check">Marcar sincronizado</x-filament::button>
                    </form>
                </div>
            </x-filament::section>
        @empty
            <x-filament::section>
                <div class="py-8 text-center">
                    <p class="text-lg font-semibold">Todo está al día</p>
                    <p class="mt-1 text-sm text-gray-500">No hay clientes pendientes de captura en SoftRestaurant.</p>
                </div>
            </x-filament::section>
        @endforelse
    </div>
</x-filament-panels::page>
