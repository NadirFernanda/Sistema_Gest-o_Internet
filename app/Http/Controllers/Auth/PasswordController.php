<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.passwords.change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Senha atual incorreta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('password.change')->with('status','Senha atualizada com sucesso.');
    }
}
