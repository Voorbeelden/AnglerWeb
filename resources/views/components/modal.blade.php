@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
][$maxWidth];
@endphp

{{--
    Herbruikbare modal-shell: donkere kaart, afgeronde hoeken,
    backdrop-blur. Props: name/show/maxWidth/focusable, events:
    open-modal/close-modal/close - dezelfde API voor elke aanroep in de app.

    Klik-buiten-om-te-sluiten heeft één overlay met een eigen
    click-handler (show = false); de kaart zelf gebruikt @click.stop zodat
    een klik binnenin nooit per ongeluk sluit.

    Alles gaat via x-teleport naar <body>. Zonder dat blijft de modal
    (position: fixed) soms opgesloten binnen de afmetingen van een
    omliggende glass-card: backdrop-filter creëert in moderne browsers een
    nieuw containment-blok voor fixed-nakomelingen, los van de
    fixed/inset-0-styling zelf. Door naar <body> te teleporteren ontsnapt
    de modal daaraan, ongeacht in welke kaart hij geplaatst wordt.
--}}
<template x-teleport="body">
<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[999]"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    {{-- Overlay: klik hierop = sluiten. Dit is de enige click-to-close
         handler; de kaart zelf stopt propagatie (@click.stop) zodat een
         klik binnenin de modal nooit per ongeluk sluit. --}}
    <div class="absolute inset-0 bg-black/75 backdrop-blur-md" x-on:click="show = false"></div>

    <div class="relative h-full flex items-center justify-center p-4 sm:p-6 overflow-y-auto" x-on:click="show = false">
        <div class="absolute w-[500px] h-[500px] bg-green-500/10 blur-[160px] rounded-full pointer-events-none"></div>
        <div class="absolute w-[400px] h-[400px] bg-blue-500/10 blur-[160px] rounded-full translate-x-24 translate-y-16 pointer-events-none"></div>

        <div
            x-on:click.stop
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="opacity-0 scale-90 translate-y-10"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition duration-200 ease-in"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="relative w-full {{ $maxWidth }} my-6 overflow-hidden rounded-3xl border-3 border-white/10 bg-[#0f172a]/95 backdrop-blur-xl shadow-[0_0_80px_rgba(0,0,0,0.8)] text-gray-100"
        >
            <button
                type="button"
                x-on:click="show = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition z-10"
                aria-label="Close"
            >
                <x-lucide-x class="h-5 w-5" />
            </button>

            {{ $slot }}
        </div>
    </div>
</div>
</template>
