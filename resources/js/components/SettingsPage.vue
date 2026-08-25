<template>
  <div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 48px; height: 48px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #8b5cf6;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
      <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #f8fafc; margin: 0;">System Settings</h1>
        <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 0;">Manage your profile, security, and client access.</p>
      </div>
    </div>

    <div class="grid-sidebar-layout" style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start;">
      
      <!-- Premium Sidebar Tabs -->
      <div style="position: sticky; top: 24px;">
        <div class="card" style="padding: 12px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
          <nav style="display: flex; flex-direction: column; gap: 8px;">
            <button @click="activeTab = 'profile'" class="settings-tab" :class="{ active: activeTab === 'profile' }">
              <div class="tab-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              </div>
              <div class="tab-content">
                <div class="tab-title">My Profile</div>
                <div class="tab-sub">Personal information</div>
              </div>
            </button>

            <button @click="activeTab = 'security'" class="settings-tab" :class="{ active: activeTab === 'security' }">
              <div class="tab-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              </div>
              <div class="tab-content">
                <div class="tab-title">Security</div>
                <div class="tab-sub">Password & authentication</div>
              </div>
            </button>

            <button @click="activeTab = 'client'" class="settings-tab" :class="{ active: activeTab === 'client' }">
              <div class="tab-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
              </div>
              <div class="tab-content">
                <div class="tab-title">Client Access</div>
                <div class="tab-sub">Client portal credentials</div>
              </div>
            </button>
          </nav>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="settings-content-area" style="position: relative;">
        
        <!-- Profile Tab -->
        <div v-show="activeTab === 'profile'" class="tab-pane">
          <div class="card premium-card">
            <div class="card-header premium-header">
              <div class="card-title">Profile Information</div>
            </div>
            <form @submit.prevent="saveProfile">
              <div class="card-body">
                <div class="grid grid-2 gap-6" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                  <div class="form-group">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Full Name</label>
                    <input type="text" v-model="profileForm.name" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Email Address</label>
                    <input type="email" v-model="profileForm.email" class="form-control" required />
                  </div>
                </div>
              </div>
              <div class="card-footer" style="display:flex; justify-content: flex-end; align-items: center; gap: 16px;">
                <button type="submit" class="btn btn-primary" :disabled="savingProfile">
                  {{ savingProfile ? 'Saving...' : 'Save Profile' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Security Tab -->
        <div v-show="activeTab === 'security'" class="tab-pane">
          <div class="card premium-card">
            <div class="card-header premium-header">
              <div class="card-title">Update Password</div>
            </div>
            <form @submit.prevent="changePassword">
              <div class="card-body">
                <div class="mb-6 max-w-lg" style="margin-bottom: 1.5rem;">
                  <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Current Password</label>
                  <input type="password" v-model="passwordForm.currentPassword" class="form-control" required />
                  <div v-if="passwordErrors.currentPassword" class="text-danger mt-2" style="font-size: 0.875rem; color: #ef4444; margin-top: 4px;">{{ passwordErrors.currentPassword[0] }}</div>
                </div>
                <div class="grid grid-2 gap-6" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                  <div class="form-group">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem;">New Password</label>
                    <input type="password" v-model="passwordForm.newPassword" class="form-control" required minlength="8" />
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Confirm New Password</label>
                    <input type="password" v-model="passwordForm.newPasswordConfirm" class="form-control" required minlength="8" />
                  </div>
                </div>
                <div v-if="passwordErrors.newPassword" class="text-danger mt-2" style="font-size: 0.875rem; color: #ef4444; margin-top: 4px;">{{ passwordErrors.newPassword[0] }}</div>
              </div>
              <div class="card-footer" style="display:flex; justify-content: flex-end; align-items: center; gap: 16px;">
                <button type="submit" class="btn btn-primary" :disabled="savingPassword">
                  {{ savingPassword ? 'Updating...' : 'Update Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Client Account Tab -->
        <div v-show="activeTab === 'client'" class="tab-pane">
          <div class="card premium-card">
            <div class="card-header premium-header">
              <div class="card-title">Client Portal Access</div>
            </div>
            <div class="card-body">
              <template v-if="clientUser">
                <div class="mb-6 max-w-lg" style="margin-bottom: 1.5rem;">
                  <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Client Email / Username</label>
                  <input type="text" class="form-control" :value="clientUser.email" readonly style="background: rgba(0,0,0,0.2); cursor: not-allowed; color: var(--color-muted);" />
                </div>
                
                <div class="max-w-lg">
                  <label class="form-label" style="display: block; margin-bottom: 0.5rem;">Set New Client Password</label>
                  <div style="display:flex; gap:12px; align-items: stretch;">
                    <input type="password" v-model="clientPasswordForm.newClientPassword" class="form-control" placeholder="New password (min 8 chars)" style="flex:1" />
                    <button @click="updateClientPassword" class="btn btn-primary" :disabled="savingClientPassword || !clientPasswordForm.newClientPassword">
                      {{ savingClientPassword ? 'Saving...' : 'Update Key' }}
                    </button>
                  </div>
                  <div v-if="clientPasswordErrors.newClientPassword" class="text-danger mt-2" style="font-size: 0.875rem; color: #ef4444; margin-top: 4px;">{{ clientPasswordErrors.newClientPassword[0] }}</div>
                </div>
              </template>
              <template v-else>
                <div class="alert danger-glass">
                  No client account found in the system.
                </div>
              </template>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  initialUser: { type: Object, required: true },
  initialClientUser: { type: Object, default: null },
  csrf: { type: String, required: true },
  endpoints: { type: Object, required: true }
});

const activeTab = ref('profile');

const profileForm = ref({
  name: props.initialUser.name || '',
  email: props.initialUser.email || ''
});
const savingProfile = ref(false);

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  newPasswordConfirm: ''
});
const passwordErrors = ref({});
const savingPassword = ref(false);

const clientUser = ref(props.initialClientUser);
const clientPasswordForm = ref({
  newClientPassword: ''
});
const clientPasswordErrors = ref({});
const savingClientPassword = ref(false);

const saveProfile = async () => {
  savingProfile.value = true;
  try {
    const response = await fetch(props.endpoints.saveProfile, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(profileForm.value)
    });
    const data = await response.json();
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Saved', message: data.message } }));
      }
    } else if (response.status === 422) {
      alert("Validation failed. Please check your inputs.");
    }
  } catch (e) {
    console.error(e);
  } finally {
    savingProfile.value = false;
  }
};

const changePassword = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.newPasswordConfirm) {
    passwordErrors.value = { newPassword: ['Passwords do not match.'] };
    return;
  }
  
  savingPassword.value = true;
  passwordErrors.value = {};
  
  try {
    const response = await fetch(props.endpoints.changePassword, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(passwordForm.value)
    });
    
    const data = await response.json();
    
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Changed', message: data.message } }));
      }
      passwordForm.value = { currentPassword: '', newPassword: '', newPasswordConfirm: '' };
    } else if (response.status === 422) {
      passwordErrors.value = data.errors || {};
      if (!data.errors) {
         passwordErrors.value = { currentPassword: [data.message] };
      }
    }
  } catch (e) {
    console.error(e);
  } finally {
    savingPassword.value = false;
  }
};

const updateClientPassword = async () => {
  savingClientPassword.value = true;
  clientPasswordErrors.value = {};
  
  try {
    const response = await fetch(props.endpoints.updateClientPassword, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(clientPasswordForm.value)
    });
    
    const data = await response.json();
    
    if (data.success) {
      if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: 'Updated', message: data.message } }));
      }
      clientPasswordForm.value.newClientPassword = '';
    } else if (response.status === 422) {
      clientPasswordErrors.value = data.errors || {};
    }
  } catch (e) {
    console.error(e);
  } finally {
    savingClientPassword.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles are handled by inline CSS and index.blade.php injected CSS,
   but we can add a few specific adjustments here if needed */
</style>
