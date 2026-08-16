<div>
    <x-page-header title="System Settings" subtitle="Manage your profile, security, and client access" icon="cog" />

    <div class="grid" style="grid-template-columns: 280px 1fr; gap: 32px; align-items: flex-start">
        
        {{-- Premium Sidebar Tabs --}}
        <div style="position: sticky; top: 24px;">
            <div class="card" style="padding: 12px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
                <nav style="display: flex; flex-direction: column; gap: 8px;">
                    <button wire:click="$set('activeTab', 'profile')" 
                            class="settings-tab {{ $activeTab === 'profile' ? 'active' : '' }}">
                        <div class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div class="tab-content">
                            <div class="tab-title">My Profile</div>
                            <div class="tab-sub">Personal information</div>
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'security')" 
                            class="settings-tab {{ $activeTab === 'security' ? 'active' : '' }}">
                        <div class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <div class="tab-content">
                            <div class="tab-title">Security</div>
                            <div class="tab-sub">Password & authentication</div>
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'client')" 
                            class="settings-tab {{ $activeTab === 'client' ? 'active' : '' }}">
                        <div class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div class="tab-content">
                            <div class="tab-title">Client Access</div>
                            <div class="tab-sub">Shared view-only account</div>
                        </div>
                    </button>
                </nav>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="settings-content-area" style="position: relative;">
            
            {{-- Profile Tab --}}
            @if($activeTab === 'profile')
            <div class="tab-pane">
                <div class="card premium-card">
                    <div class="card-header premium-header">
                        <div class="card-title">Profile Information</div>
                        <p class="text-sm text-muted mt-1">Update your account's profile information and email address.</p>
                    </div>
                    <form wire:submit="saveProfile">
                        <div class="card-body">
                            <div class="grid grid-2 gap-6">
                                <x-input name="name" label="Full Name" wire:model="name" required />
                                <x-input name="email" label="Email Address" type="email" wire:model="email" required />
                            </div>
                        </div>
                        <div class="card-footer" style="display:flex; justify-content: flex-end; align-items: center; gap: 16px;">
                            <x-btn variant="primary" type="submit"  >
                                <span wire:loading.remove wire:target="saveProfile">Save Profile</span>
                                <span wire:loading wire:target="saveProfile">Saving...</span>
                            </x-btn>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Security Tab --}}
            @if($activeTab === 'security')
            <div class="tab-pane">
                <div class="card premium-card">
                    <div class="card-header premium-header">
                        <div class="card-title">Update Password</div>
                        <p class="text-sm text-muted mt-1">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                    <form wire:submit="changePassword">
                        <div class="card-body">
                            <div class="mb-6 max-w-lg">
                                <x-input name="currentPassword" label="Current Password" type="password" wire:model="currentPassword" required />
                            </div>
                            <div class="grid grid-2 gap-6">
                                <x-input name="newPassword" label="New Password" type="password" wire:model="newPassword" required />
                                <x-input name="newPasswordConfirm" label="Confirm New Password" type="password" wire:model="newPasswordConfirm" required />
                            </div>
                        </div>
                        <div class="card-footer" style="display:flex; justify-content: flex-end; align-items: center; gap: 16px;">
                            <x-btn variant="primary" type="submit"  >
                                <span wire:loading.remove wire:target="changePassword">Update Password</span>
                                <span wire:loading wire:target="changePassword">Updating...</span>
                            </x-btn>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Client Account Tab --}}
            @if($activeTab === 'client')
            <div class="tab-pane">
                <div class="card premium-card">
                    <div class="card-header premium-header">
                        <div class="card-title">Client Dashboard Access</div>
                        <p class="text-sm text-muted mt-1">Manage the credentials for the read-only client dashboard.</p>
                    </div>
                    <div class="card-body">
                        <div class="alert info-glass mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>This is the shared account your clients use to access the Security Dashboard without modifying settings.</div>
                        </div>
                        
                        @if($clientUser)
                            <div class="mb-6 max-w-lg">
                                <label class="form-label">Client Email / Username</label>
                                <input type="text" class="form-control" value="{{ $clientUser->email }}" readonly style="background: rgba(0,0,0,0.2); cursor: not-allowed; color: var(--color-muted);" />
                            </div>
                            
                            <div class="max-w-lg">
                                <label class="form-label">Set New Client Password</label>
                                <div style="display:flex;gap:12px; align-items: stretch;">
                                    <input type="password" wire:model="newClientPassword" class="form-control" placeholder="New password (min 8 chars)" style="flex:1" />
                                    <x-btn variant="primary" wire:click="updateClientPassword"  >
                                        <span wire:loading.remove wire:target="updateClientPassword">Update Key</span>
                                        <span wire:loading wire:target="updateClientPassword">Saving...</span>
                                    </x-btn>
                                </div>
                                @error('newClientPassword') <div class="text-sm text-muted mt-2" style="color: var(--color-danger);">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="alert danger-glass">
                                No client account found in the system.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

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

        /* Transitions */
        .tab-enter { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .tab-enter-start { opacity: 0; transform: translateY(15px) scale(0.98); }
        .tab-enter-end { opacity: 1; transform: translateY(0) scale(1); }

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

        .info-glass {
            background: rgba(0,212,255,0.08);
            border: 1px solid rgba(0,212,255,0.2);
            color: #e0f7fa;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .danger-glass {
            background: rgba(255,71,87,0.08);
            border: 1px solid rgba(255,71,87,0.2);
            color: #ff6b81;
            border-radius: 12px;
            padding: 16px 20px;
        }

        .max-w-lg { max-width: 32rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
    </style>
    @endpush
</div>
