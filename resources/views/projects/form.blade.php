@extends('layouts.app')

@section('content')

{{-- Vue props passed cleanly via window object --}}
<script>
window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
window.__VUE_PROPS__['vue-project-form'] = {
    initialData: @json(array_merge(
        [
            'name' => '', 'domain' => '', 'description' => '',
            'stack' => 'laravel', 'php_version' => '8.3',
            'monitoring_interval_minutes' => 5,
            'server_type' => 'same_server', 'server_path' => '',
            'log_path' => 'storage/logs/laravel.log',
            'agent_url' => '', 'agent_secret' => $agent_secret,
            'alert_email' => auth()->user()->email,
            'slack_webhook_url' => '', 'discord_webhook_url' => '',
        ],
        $project ? $project->toArray() : []
    )),
    isEdit: @json($isEdit),
    submitUrl: @json($isEdit ? route('projects.update', $project) : route('projects.store')),
    cancelUrl: @json(route('projects.index')),
    csrf: @json(csrf_token()),
    testConnectionUrl: @json(route('projects.test-connection')),
    autoDetectUrl: @json(route('projects.auto-detect-log')),
};
</script>

{{-- Vue mounts here --}}
<div id="vue-project-form"></div>

@endsection
