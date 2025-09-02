<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $banks = Bank::where('user_id', Auth::id())->get();
        return view('users.profile', compact('banks'));
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.index')->with('success', 'Password updated successfully.');
    }
    public function bank(Request $request)
    {
        $data = [
            'bank_name' => $request->input('bank_name'),
            'bank_acount' => $request->input('bank_acount'),
            'bank_number' => $request->input('bank_number'),
            'user_id' => Auth::id(),
        ];

        $bank = Bank::find($request->input('id'));


        $bank->update($data); // Menggunakan metode 'update' untuk pembaruan
        return redirect()->route('profile.index')->with('success', 'Bank updated successfully.');
    }
}
