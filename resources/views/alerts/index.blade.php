@extends('layouts.app', ['title' => 'Alerts'])

@section('content')
@php
    $vueProps = [
        'initialLogs' => $logs->items(),
        'meta' => [
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'total' => $logs->total(),
            'per_page' => $logs->perPage()
        ],
        'initialFilterType' => $filterType,
        'endpoints' => [
            'index' => route('alerts.index'),
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-alert-log'] = @json($vueProps);
</script>

<div id="vue-alert-log"></div>
@endsection
