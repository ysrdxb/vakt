@extends('layouts.app', ['title' => 'Daily Logs'])

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
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'initialSelectedDate' => $selectedDate,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('daily-logs.index'),
            'base' => url('/daily-logs') // Will append /{id}/note in Vue
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-daily-log-calendar'] = @json($vueProps);
</script>

<div id="vue-daily-log-calendar"></div>
@endsection
