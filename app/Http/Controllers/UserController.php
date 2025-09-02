<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Models\Network;
use Illuminate\Http\Request;
use App\Models\NetworkBallance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');
        $users = User::when($search, function ($query, $search) {
            return $query->where('username', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')  // Urutkan berdasarkan kolom created_at secara descending
            ->paginate(10)->onEachSide(0);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^\S*$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'id_card' => ['nullable', 'string'],
            'referal' => ['nullable', 'string', 'exists:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'], // Validation for is_active
            'custom_fee' => ['required', 'boolean'], // Validation for custom_fee
            'fee' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'id_card' => $request->input('id_card'),
            'referal' => $request->input('referal') ?? 'system',
            'password' => Hash::make($request->input('password')),
            'is_active' => $request->input('is_active') ?? false,
            'custom_fee' => $request->input('custom_fee') ?? false,
            'fee' => $request->input('custom_fee') ? max(0, intval($request->input('fee', 0))) : 0,
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

        return response()->json(['message' => 'Pengguna baru berhasil ditambahkan'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return response()->json($user);
    }

    // Mengupdate pengguna yang ada dalam database
    public function update(Request $request, User $user)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'id_card' => ['nullable', 'string'],
            'is_active' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'custom_fee' => 'required|boolean',
            'fee' => 'integer|min:0', // Add 'min:0' validation rule
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update data pengguna
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'id_card' => $request->id_card,
            'is_active' => $request->is_active,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'custom_fee' => $request->custom_fee,
            'fee' => $request->custom_fee ? max(0, $request->fee) : 0,
        ]);


        return response()->json(['message' => 'Pengguna berhasil diperbarui.']);
    }

    // Menghapus pengguna dari database
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
