<?php

namespace App\Http\Controllers\Auth;

use App\Models\Bank;
use App\Models\User;
use App\Models\Network;
use App\Models\NetworkBallance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^\S*$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'referal' => ['nullable', 'string', 'exists:users,username'], // Validasi untuk referal
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'referal' => $data['referal'] ?? 'system',
            'password' => Hash::make($data['password']),
        ]);

        // Membuat catatan bank kosong untuk pengguna baru
        $bank = new Bank;
        $bank->user_id = $user->id;
        $bank->save();
        $networks = Network::all();
        foreach ($networks as $network) {
            $networkBalance = new NetworkBallance;
            $networkBalance->user_id = $user->id;
            $networkBalance->network_id = $network->id;
            $networkBalance->balance = 0;  // atau nilai awal lainnya
            $networkBalance->save();
        }
        return $user;
    }
}
