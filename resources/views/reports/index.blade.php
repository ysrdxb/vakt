@extends('layouts.app', ['title' => 'SQA Reports'])

@section('content')
@php
    $vueProps = [
        'initialReports' => $reports->items(),
        'meta' => [
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
            'total' => $reports->total(),
            'per_page' => $reports->perPage()
        ],
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('reports.index'),
            'store' => route('reports.store'),
            'base' => url('/reports') // Will append /{id}/mark-sent
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-sqa-report'] = @json($vueProps);
</script>

<div id="vue-sqa-report"></div>
@endsection
