@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    // Aditivo (não existe no componente original do DPEMT): ícone/HTML
    // antes ou depois do texto do badge.
    'startIcon' => null,
    'endIcon' => null,
])

@php
    $base = 'inline-flex items-center font-semibold rounded-full';

    $variants = [
        'default' => 'border',
        'primary' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        'warning' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        'danger' => 'bg-red-500/10 text-red-400 border border-red-500/20',
        'info' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
        'success' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    ];

    $variantStyle = $variant === 'default' ? 'background-color: var(--bg-elevated); color: var(--text-secondary); border-color: var(--border-color);' : '';

    $sizes = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-xs',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} @if($variantStyle) style="{{ $variantStyle }}" @endif>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 {{ $variant === 'primary' ? 'animate-pulse' : '' }}"></span>
    @endif
    @if($startIcon)
        <span class="mr-1 inline-flex items-center">{!! $startIcon !!}</span>
    @endif
    {{ $slot }}
    @if($endIcon)
        <span class="ml-1 inline-flex items-center">{!! $endIcon !!}</span>
    @endif
</span>
