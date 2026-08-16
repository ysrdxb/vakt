@props(['type' => 'muted'])

@php
$map = [
    'critical' => 'critical',
    'danger'   => 'danger',
    'p1'       => 'critical',
    'warning'  => 'warning',
    'p2'       => 'warning',
    'info'     => 'info',
    'primary'  => 'primary',
    'p3'       => 'info',
    'success'  => 'success',
    'p4'       => 'success',
    'muted'    => 'muted',
];
$cls = $map[$type] ?? $type;
@endphp

<span {{ $attributes->merge(['class' => "badge {$cls}"]) }}>
    {{ $slot }}
</span>
