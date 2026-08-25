@extends('layouts.app', ['title' => 'Improvements'])

@section('content')
@php
    $vueProps = [
        'initialColumnedItems' => $columnedItems,
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('improvements.index'),
            'store' => route('improvements.store'),
            'base' => url('/improvements') // Will append /{id}/status
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-improvement-kanban'] = @json($vueProps);
</script>

<div id="vue-improvement-kanban"></div>
@endsection
