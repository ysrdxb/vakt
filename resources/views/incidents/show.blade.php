@extends('layouts.app', ['title' => 'Incident: ' . $incident->title])

@section('content')
@php
    $vueProps = [
        'incident' => $incident,
        'csrf' => csrf_token(),
        'endpoints' => [
            'transitionStatus' => route('incidents.transition-status', $incident),
            'saveNotes' => route('incidents.save-notes', $incident),
            'executeCommand' => route('incidents.command', $incident)
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-incident-detail'] = @json($vueProps);
</script>

<div id="vue-incident-detail"></div>
@endsection
