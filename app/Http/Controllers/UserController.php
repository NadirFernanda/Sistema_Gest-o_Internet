<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show form to create a new user
    public function create()
    {
        // only users with permission can see the form via route middleware
        $roles = Role::orderBy('name')->pluck('name', 'name');
        return view('admin.users.create', compact('roles'));
    }

    // Store new user
    public function store(Request $request)
    {
        // route is protected by 'permission:users.create' middleware

        // normalize email: if user provided only local-part, append default domain
        $defaultDomain = 'sgmrtexas.angolawifi.ao';
        $emailInput = (string) $request->input('email', '');
        if (strpos($emailInput, '@') === false) {
            $emailNormalized = $emailInput . '@' . $defaultDomain;
        } else {
            $emailNormalized = $emailInput;
        }

        $dataToValidate = [
            'name' => $request->input('name'),
            'email' => $emailNormalized,
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'role' => $request->input('role'),
        ];

        $validator = Validator::make($dataToValidate, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $data = $validator->validate();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return redirect()->route('dashboard')->with('status', 'Usuário criado com sucesso');
    }
}
