<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $clientUser = User::where('role', 'client')->first();

        if ($request->wantsJson()) {
            return response()->json([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'clientUser' => $clientUser ? [
                    'email' => $clientUser->email
                ] : null
            ]);
        }

        return \Inertia\Inertia::render('SettingsPage', compact('user', 'clientUser'));
    }

    public function saveProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . auth()->id()
        ]);

        auth()->user()->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'user' => auth()->user()
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|same:newPasswordConfirm'
        ]);

        if (!Hash::check($request->input('currentPassword'), auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'errors' => ['currentPassword' => ['Current password is incorrect.']]
            ], 422);
        }

        auth()->user()->update([
            'password' => Hash::make($request->input('newPassword'))
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated.'
        ]);
    }

    public function updateClientPassword(Request $request)
    {
        $request->validate(['newClientPassword' => 'required|min:8']);
        
        $clientUser = User::where('role', 'client')->first();
        
        if ($clientUser) {
            $clientUser->update([
                'password' => Hash::make($request->input('newClientPassword'))
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Client password updated.'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Client user not found.'
        ], 404);
    }
}
