<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">Escanear con la cámara</x-slot>
            <x-slot name="description">El QR identifica al cliente; no autoriza una redención por sí solo.</x-slot>
            <div x-data="loyaltyScanner()" class="space-y-4">
                <div class="overflow-hidden rounded-xl bg-black">
                    <video x-ref="video" playsinline muted class="aspect-square w-full object-cover"></video>
                </div>
                <p x-text="message" class="text-sm text-gray-500">Presiona el botón para activar la cámara.</p>
                <x-filament::button type="button" color="gray" x-on:click="start()" icon="heroicon-o-camera">Activar cámara</x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Captura manual</x-slot>
            <x-slot name="description">Si esta computadora no tiene cámara, captura la Clave SR que aparece debajo del QR.</x-slot>
            <form wire:submit="identify" class="space-y-4">
                <input type="text" wire:model="code" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/5" required>
                <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">Identificar cliente</x-filament::button>
            </form>
        </x-filament::section>
    </div>

    @script
    <script>
        Alpine.data('loyaltyScanner', () => ({
            stream: null,
            timer: null,
            message: 'Presiona el botón para activar la cámara.',
            async start() {
                if (!('BarcodeDetector' in window)) {
                    this.message = 'Este navegador no permite escaneo automático. Usa Chrome/Edge actualizado o captura el código manualmente.';
                    return;
                }
                try {
                    this.stream?.getTracks().forEach(track => track.stop());
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    this.$refs.video.srcObject = this.stream;
                    await this.$refs.video.play();
                    this.message = 'Apunta la cámara al QR de Origen Rewards.';
                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    this.timer = setInterval(async () => {
                        const codes = await detector.detect(this.$refs.video).catch(() => []);
                        if (codes.length) {
                            clearInterval(this.timer);
                            this.stream.getTracks().forEach(track => track.stop());
                            $wire.set('code', codes[0].rawValue);
                            await $wire.identify();
                        }
                    }, 500);
                } catch (error) {
                    this.message = 'No fue posible abrir la cámara. Revisa el permiso del navegador o usa la captura manual.';
                }
            },
        }));
    </script>
    @endscript
</x-filament-panels::page>
