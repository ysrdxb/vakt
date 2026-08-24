@extends('layouts.app', ['title' => $project->domain])

@section('content')

@php
$vueProps = [
    'project' => $project,
    'latestReport' => $latestReport,
    'uptimeLogs' => $uptimeLogs,
    'endpoints' => [
        'confirmWhitelist' => route('projects.confirm-whitelist', $project), // Wait, does this exist? Need to add to controller
        'runScan' => route('projects.run-scan', $project),
        'sendTestReport' => route('projects.test-report', $project),
        'edit' => route('projects.edit', $project),
    ],
    'csrf' => csrf_token(),
];
@endphp

<script>
window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
window.__VUE_PROPS__['vue-project-detail'] = @json($vueProps);
</script>

<div id="vue-project-detail"></div>

@endsection
