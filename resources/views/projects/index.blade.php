@extends('layouts.app', ['title' => 'Projects'])

@section('content')

@php
$vueProps = [
    'initialProjects' => $projects,
    'endpoints' => [
        'index' => route('projects.index'),
        'create' => route('projects.create'),
        'toggleActive' => url('/projects'), // We will append /{id}/toggle-active in Vue
        'destroy' => url('/projects'), // We will append /{id} in Vue
        'show' => url('/projects'), // We will append /{id} in Vue
        'edit' => url('/projects'), // We will append /{id}/edit in Vue
    ],
    'csrf' => csrf_token(),
];
@endphp

<script>
window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
window.__VUE_PROPS__['vue-project-list'] = @json($vueProps);
</script>

<div id="vue-project-list"></div>

@endsection
