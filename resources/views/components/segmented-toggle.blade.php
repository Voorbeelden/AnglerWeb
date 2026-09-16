@props([
    // Alpine-variabele (als string) die de huidige waarde bijhoudt.
    'model',
    // ['waarde' => 'label', ...]
    'options',
    // Optionele extra Alpine-code na het klikken (bv. 'refresh()').
    'onchange' => null,
])

<div {{ $attributes->merge(['class' => 'flex rounded-xl border-3 border-white/20 overflow-hidden text-xs']) }}>
    @foreach ($options as $value => $label)
    <button type="button"
        x-on:click="{{ $model }} = {{ Illuminate\Support\Js::from((string) $value) }}; {{ $onchange }}"
        class="px-3 py-2 transition"
        x-bind:class="{{ $model }} === {{ Illuminate\Support\Js::from((string) $value) }} ? 'bg-green-500 text-black font-semibold' : 'text-gray-300 hover:bg-white/10'">
        {{ $label }}
    </button>
    @endforeach
</div>
