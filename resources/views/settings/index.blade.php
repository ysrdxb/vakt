@extends('layouts.app', ['title' => 'System Settings'])

@section('content')
@php
    $vueProps = [
        'initialUser' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'initialClientUser' => $clientUser ? [
            'email' => $clientUser->email
        ] : null,
        'csrf' => csrf_token(),
        'endpoints' => [
            'saveProfile' => url('/settings/profile'),
            'changePassword' => url('/settings/password'),
            'updateClientPassword' => url('/settings/client-password')
        ]
    ];
@endphp

<script>
    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-settings-page'] = @json($vueProps);
</script>

<div id="vue-settings-page"></div>

@push('styles')
<style>
    .settings-tab {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        width: 100%;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 12px;
        color: var(--color-text);
        text-align: left;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .settings-tab:hover:not(.active) {
        background: rgba(255,255,255,0.03);
    }

    .settings-tab.active {
        background: linear-gradient(135deg, rgba(0,212,255,0.1), rgba(0,149,179,0.1));
        border: 1px solid rgba(0,212,255,0.2);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .settings-tab .tab-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(0,0,0,0.2);
        color: var(--color-muted);
        transition: all 0.3s ease;
    }

    .settings-tab.active .tab-icon {
        background: var(--color-primary);
        color: #000;
        box-shadow: 0 4px 12px rgba(0,212,255,0.4);
    }

    .settings-tab .tab-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--color-text);
        transition: color 0.2s;
    }

    .settings-tab.active .tab-title {
        color: var(--color-primary);
    }

    .settings-tab .tab-sub {
        font-size: 0.75rem;
        color: var(--color-muted);
        margin-top: 2px;
    }

    /* Premium Cards */
    .premium-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .premium-header {
        background: linear-gradient(to bottom, rgba(255,255,255,0.03), transparent);
        border-bottom: 1px solid var(--color-border);
        padding: 24px 32px;
    }

    .premium-card .card-body {
        padding: 32px;
    }

    .premium-card .card-footer {
        background: rgba(0,0,0,0.15);
        border-top: 1px solid var(--color-border);
        padding: 16px 32px;
    }

    .danger-glass {
        background: rgba(255,71,87,0.08);
        border: 1px solid rgba(255,71,87,0.2);
        color: #ff6b81;
        border-radius: 12px;
        padding: 16px 20px;
    }

    .max-w-lg { max-width: 32rem; }
</style>
@endpush
@endsection
