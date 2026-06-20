<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        return back()->with('success', 'Password berhasil diubah.');
    }

    //impersonate
    public function impersonate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|min:5'
        ]);

        $user = User::findOrFail($id);

        // pengamanan
        if ($user->role !== 'internal') {
            return back()->with('error', 'User tidak dapat diimpersonate.');
        }

        session([
            'impersonator'       => auth()->id(),
            'impersonate_reason' => $request->reason,
        ]);

        auth()->login($user);

        return redirect('/user/dashboard');
    }
}