<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'internal')
                     ->latest()
                     ->get();

        return view('admin.users.index', compact('users'));
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $user = \App\Models\User::findOrFail($id);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        ActivityLog::create([
            'admin_id'       => auth()->id(),
            'target_user_id' => $user->id,
            'action'         => 'change_password',
            'description'    => 'Mengubah password akun "' . $user->name . '".'
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    //impersonate
    public function impersonate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|min:5'
        ]);

        $adminId = auth()->id();

        $user = User::findOrFail($id);

        if ($user->role !== 'internal') {
            return back()->with('error', 'User tidak dapat diimpersonate.');
        }

        session([
            'impersonator'       => $adminId,
            'impersonate_reason' => $request->reason,
        ]);

        ActivityLog::create([
            'admin_id'       => $adminId,
            'target_user_id' => $user->id,
            'action'         => 'impersonate',
            'description'    => 'Melakukan impersonate ke akun "' .
                                $user->name .
                                '". Alasan: ' .
                                $request->reason
        ]);

        auth()->login($user);

        return redirect('/user/dashboard');
}
    
}