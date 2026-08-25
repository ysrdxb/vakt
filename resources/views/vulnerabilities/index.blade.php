@extends('layouts.app', ['title' => 'Vulnerabilities'])

@section('content')
@php
    $vueProps = [
        'initialVulnerabilities' => $vulnerabilities->items(),
        'initialStats' => $stats,
        'meta' => [
            'current_page' => $vulnerabilities->currentPage(),
            'last_page' => $vulnerabilities->lastPage(),
            'total' => $vulnerabilities->total(),
            'per_page' => $vulnerabilities->perPage()
        ],
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'initialFilterStatus' => $filterStatus,
        'initialFilterSeverity' => $filterSeverity,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('vulnerabilities.index'),
            'base' => url('/vulnerabilities') // Will append /{id}/patched or /{id}/accept-risk
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-vulnerability-list'] = @json($vueProps);
</script>

<div id="vue-vulnerability-list"></div>
@endsection
