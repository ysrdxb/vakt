<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SettingsPage extends Component
{
    public string $name = '';
    public string $email = '';
    
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirm = '';
    
    public string $newClientPassword = '';
    
    public string $activeTab = 'profile';

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function saveProfile()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . auth()->id()
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email
        ]);

        $this->dispatch('toast', ['type' => 'success', 'title' => 'Saved', 'message' => 'Profile updated.']);
    }

    public function changePassword()
    {
        $this->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|same:newPasswordConfirm'
        ]);

        if (!Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->newPassword)
        ]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirm']);
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Changed', 'message' => 'Password updated.']);
    }

    public function getClientUserProperty()
    {
        return User::where('role', 'client')->first();
    }

    public function updateClientPassword()
    {
        $this->validate(['newClientPassword' => 'required|min:8']);
        
        if ($this->clientUser) {
            $this->clientUser->update([
                'password' => Hash::make($this->newClientPassword)
            ]);
            $this->reset('newClientPassword');
            $this->dispatch('toast', ['type' => 'success', 'title' => 'Updated', 'message' => 'Client password updated.']);
        }
    }

    public function render()
    {
        return view('livewire.settings.settings-page', [
            'clientUser' => $this->clientUser
        ])->layout('layouts.app', ['title' => 'Settings']);
    }
}
