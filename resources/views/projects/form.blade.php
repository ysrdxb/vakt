@extends('layouts.app')

@section('content')

@php
$formDefaults = [
    'name' => '',
    'domain' => '',
    'description' => '',
    'stack' => 'laravel',
    'php_version' => '8.3',
    'monitoring_interval_minutes' => 5,
    'server_type' => 'same_server',
    'server_path' => '',
    'log_path' => 'storage/logs/laravel.log',
    'agent_url' => '',
    'agent_secret' => $agent_secret,
    'alert_email' => auth()->user()->email,
    'slack_webhook_url' => '',
    'discord_webhook_url' => '',
];
$initialData = $project ? array_merge($formDefaults, $project->toArray()) : $formDefaults;
$submitUrl   = $isEdit ? route('projects.update', $project) : route('projects.store');
$cancelUrl   = route('projects.index');
$testUrl     = route('projects.test-connection');
$detectUrl   = route('projects.auto-detect-log');
@endphp

{{-- Vue props passed cleanly via window object --}}
<script>
window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
window.__VUE_PROPS__['vue-project-form'] = {
    initialData:       @json($initialData),
    isEdit:            @json($isEdit),
    submitUrl:         @json($submitUrl),
    cancelUrl:         @json($cancelUrl),
    csrf:              @json(csrf_token()),
    testConnectionUrl: @json($testUrl),
    autoDetectUrl:     @json($detectUrl),
};
</script>

{{-- Vue mounts here --}}
<div id="vue-project-form"></div>

@endsection
