<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

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

    // List users (admin)
    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('name');
        if ($search = $request->query('q')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(25)->withQueryString();
        return view('admin.users.index', compact('users'));
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

    // Show edit form
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name', 'name');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('admin.users.index')->with('status', 'Usuário atualizado com sucesso');
    }

    // Destroy user
    public function destroy(Request $request, User $user)
    {
        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Você não pode excluir o seu próprio usuário');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'Usuário removido');
    }
}
