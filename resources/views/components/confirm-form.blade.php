@props([
    'message',
    'confirmLabel' => null,
    'cancelLabel' => null,
    'danger' => false,
])

{{--
    Drop-in vervanging voor <form onsubmit="return confirm('...')">: zelfde
    functionaliteit, maar een grote, duidelijk leesbare modal i.p.v. het
    kleine, niet-stijlbare browser-dialoogvenster. De $slot bevat gewoon
    het volledige <form>...</form> zoals voorheen, enkel zonder de
    onsubmit-regel - de submit wordt hieronder onderschept via Alpine.

    Gebruik:
    <x-confirm-form :message="__('app.confirm_delete')">
        <form method="POST" action="...">
            @csrf @method('DELETE')
            <x-btn.danger-sec-btn>{{ __('app.delete') }}</x-btn.danger-sec-btn>
        </form>
    </x-confirm-form>
--}}
<div x-data="{ open: false }" x-ref="wrapper" x-on:submit.capture.prevent="open = true" class="contents">
    {{ $slot }}

    <template x-teleport="body">
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999]"
        x-on:keydown.escape.window="open = false">
        {{-- Zelfde donkere/blurred overlay als de algemene <x-modal>, zodat
             elke popup in de app er visueel identiek uitziet. --}}
        <div class="absolute inset-0 bg-black/75 backdrop-blur-md" x-on:click="open = false"></div>

        <div class="relative h-full flex items-center justify-center p-4" x-on:click="open = false">
            <div class="absolute w-[500px] h-[500px] bg-green-500/10 blur-[160px] rounded-full pointer-events-none"></div>
            <div class="absolute w-[400px] h-[400px] bg-blue-500/10 blur-[160px] rounded-full translate-x-24 translate-y-16 pointer-events-none"></div>

            <div x-on:click.stop
                x-transition:enter="transition duration-300 ease-out"
                x-transition:enter-start="opacity-0 scale-90 translate-y-10"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition duration-200 ease-in"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="relative w-full max-w-sm my-6 overflow-hidden rounded-3xl border-3 border-white/10 bg-[#0f172a]/95 backdrop-blur-xl shadow-[0_0_80px_rgba(0,0,0,0.8)] text-gray-100 !p-6 text-center">
                @if ($danger)
                <div class="mx-auto w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center mb-4">
                    <x-lucide-triangle-alert class="h-6 w-6 text-red-400" />
                </div>
                @endif

                <p class="text-gray-100 text-base leading-relaxed mb-6">{{ $message }}</p>

                <div class="flex justify-center gap-3">
                    <x-secondary-button type="button" x-on:click="open = false">
                        {{ $cancelLabel ?? __('app.cancel') }}
                    </x-secondary-button>
                    <x-secondary-button type="button"
                        x-on:click="open = false; $refs.wrapper.querySelector('form').submit()"
                        class="{{ $danger ? '!border-red-500/50 !text-red-300 hover:!border-red-400' : '' }}">
                        {{ $confirmLabel ?? __('app.confirm') }}
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>
