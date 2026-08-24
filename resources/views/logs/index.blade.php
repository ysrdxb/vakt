@extends('layouts.app', ['title' => 'System Logs'])

@section('content')
@php
    $vueProps = [
        'initialEntries' => $entries->items(),
        'meta' => [
            'current_page' => $entries->currentPage(),
            'last_page' => $entries->lastPage(),
            'total' => $entries->total(),
            'per_page' => $entries->perPage()
        ],
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('logs.index'),
            'markReviewed' => url('/logs'), // We'll append /{id}/review in Vue
            'analyzeWithAI' => url('/logs') // We'll append /{id}/analyze in Vue
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-log-viewer'] = @json($vueProps);
</script>

<div id="vue-log-viewer"></div>
@endsection
