<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-filament::section>
                <x-slot name="heading">Buscar cliente</x-slot>
                <x-slot name="description">Busca por nombre, teléfono, correo o Clave de SoftRestaurant.</x-slot>

                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    @disabled($customerId !== null)
                    placeholder="Escribe al menos 2 caracteres"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:bg-gray-100 dark:border-white/10 dark:bg-white/5"
                >

                @if ($customerId === null && $search !== '')
                    <div class="mt-3 divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/10 dark:border-white/10">
                        @forelse ($this->searchResults as $result)
                            <button type="button" wire:click="selectCustomer({{ $result->id }})" class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/5">
                                <span>
                                    <span class="block font-medium">{{ $result->name }}</span>
                                    <span class="block text-sm text-gray-500">{{ $result->phone ?: $result->email ?: 'Sin contacto' }}</span>
                                </span>
                                <span class="font-semibold text-primary-600">{{ number_format((float) $result->points_balance, 2) }} puntos</span>
                            </button>
                        @empty
                            @if (mb_strlen(trim($search)) >= 2)
                                <p class="px-4 py-3 text-sm text-gray-500">No encontramos clientes.</p>
                            @endif
                        @endforelse
                    </div>
                @endif
            </x-filament::section>

            @if ($this->customer)
                <x-filament::section>
                    <x-slot name="heading">Registrar redención</x-slot>

                    <form wire:submit="redeem" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block text-sm font-medium">Puntos a redimir</span>
                                <input type="number" min="0.01" step="0.01" wire:model="points" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/5" required>
                                @error('points') <span class="mt-1 block text-sm text-danger-600">{{ $message }}</span> @enderror
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-sm font-medium">Referencia (opcional)</span>
                                <input type="text" wire:model="reference" maxlength="100" placeholder="Ticket o autorización" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/5">
                            </label>
                        </div>
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium">Notas (opcional)</span>
                            <textarea wire:model="notes" rows="3" maxlength="1000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/5"></textarea>
                        </label>
                        <div class="flex gap-3">
                            <x-filament::button type="submit" icon="heroicon-o-gift">Confirmar redención</x-filament::button>
                            <x-filament::button type="button" color="gray" wire:click="clearCustomer">Cambiar cliente</x-filament::button>
                        </div>
                    </form>
                </x-filament::section>
            @endif
        </div>

        <div>
            <x-filament::section>
                <x-slot name="heading">Cliente seleccionado</x-slot>
                @if ($this->customer)
                    <dl class="space-y-3">
                        <div><dt class="text-sm text-gray-500">Nombre</dt><dd class="font-semibold">{{ $this->customer->name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Clave SR</dt><dd>{{ $this->customer->external_id ?: 'Pendiente' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Saldo disponible</dt><dd class="text-2xl font-bold text-primary-600">{{ number_format((float) $this->customer->points_balance, 2) }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm text-gray-500">Selecciona un cliente para consultar su saldo.</p>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
