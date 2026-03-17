<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function showLogin()
    {
        return view('login');
    }

    // Proses login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {

            $user = Auth::user();

            if ($user->role == 'superadmin') {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/user/dashboard');
            }
        }

        return back()->with('error', 'Email atau password salah');
    }

    // Tampilkan form register
    public function showRegister()
    {
        return view('register');
    }

    // Proses register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'status' => 'required',
            'password' => 'required|min:6'
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
            'role' => 'internal',
            'password' => bcrypt($request->password)
        ]);

        return redirect('/login')->with('success', 'Akun berhasil dibuat, silakan login');
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}