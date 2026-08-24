@extends('layouts.app', ['title' => 'File Integrity Monitor'])

@section('content')
@php
    $vueProps = [
        'initialSnapshots' => $snapshots->items(),
        'initialStats' => $stats,
        'meta' => [
            'current_page' => $snapshots->currentPage(),
            'last_page' => $snapshots->lastPage(),
            'total' => $snapshots->total(),
            'per_page' => $snapshots->perPage()
        ],
        'projects' => $projects,
        'initialProjectId' => $projectId,
        'csrf' => csrf_token(),
        'endpoints' => [
            'index' => route('file-integrity.index'),
            'initScan' => url('/file-integrity/scan'),
            'approveChange' => url('/file-integrity') // We'll append /{id}/approve in Vue
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-file-integrity-view'] = @json($vueProps);
</script>

<div id="vue-file-integrity-view"></div>
@endsection
