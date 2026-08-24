@extends('layouts.app', ['title' => 'Incidents'])

@section('content')
@php
    $vueProps = [
        'initialIncidents' => $incidents->items(),
        'meta' => [
            'current_page' => $incidents->currentPage(),
            'last_page' => $incidents->lastPage(),
            'total' => $incidents->total(),
            'per_page' => $incidents->perPage()
        ],
        'projects' => $projects,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('incidents.index')
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-incident-list'] = @json($vueProps);
</script>

<div id="vue-incident-list"></div>
@endsection
