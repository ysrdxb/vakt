@extends('layouts.app', ['title' => 'Security Audit Checklist'])

@section('content')
@php
    $vueProps = [
        'initialItemsByCategory' => $itemsByCategory,
        'initialScore' => $score,
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('audit.index'),
            'seed' => url('/audit/seed'),
            'base' => url('/audit') // Will append /{id}/status or /{id}/notes in Vue
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-audit-tracker'] = @json($vueProps);
</script>

<div id="vue-audit-tracker"></div>
@endsection
