@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')

@php
$vueProps = [
    'projects'      => $projects,
    'openIncidents' => $openIncidents,
    'p1Count'       => $p1Count,
    'overallScore'  => $overallScore,
    'scoreColor'    => $scoreColor,
    'recentChecks'  => $recentChecks,
    'chartData'     => $chartData,
    'agentStatus'   => $agentStatus,
];
@endphp

<script>
window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
window.__VUE_PROPS__['vue-operator-dashboard'] = @json($vueProps);
</script>

<div id="vue-operator-dashboard"></div>

@endsection
