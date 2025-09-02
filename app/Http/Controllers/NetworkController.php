<?php

namespace App\Http\Controllers;

use App\Events\NetworkCreated;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');
        $networks = Network::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->with('user')
            ->orderBy('created_at', 'desc')  // Urutkan berdasarkan kolom created_at secara descending
            ->paginate(10)->onEachSide(0);

        return view('networks.index', compact('networks'));
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
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'alias' => 'required|string|max:255|unique:networks,alias',
            'smartlink' => 'required|url|max:255',
            'tracker' => 'nullable|string|max:255',
            'sub1' => 'nullable|string|max:255',
            'cid' => 'nullable|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $network = Network::create([
            'name' => $request->input('name'),
            'alias' => strtoupper($request->input('alias')),
            'smartlink' => $request->input('smartlink'),
            'tracker' => $request->input('tracker'),
            'sub1' => $request->input('sub1'),
            'cid' => $request->input('cid'),
            'user_id' => $user->id,
        ]);

        event(new NetworkCreated($network));
        return response()->json(['message' => 'Domain baru berhasil ditambahkan'], 200);
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
    public function edit(Network $network)
    {
        return response()->json($network);
    }

    // Mengupdate pengguna yang ada dalam database
    public function update(Request $request, Network $network)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'alias' => 'required|string|max:255',
            'smartlink' => 'required|url|max:255',
            'tracker' => 'nullable|string|max:255',
            'sub1' => 'nullable|string|max:255',
            'cid' => 'nullable|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $network->update([
            'name' => $request->name,
            'alias' => strtoupper($request->alias),
            'smartlink' => $request->smartlink,
            'tracker' => $request->tracker,
            'sub1' => $request->sub1,
            'cid' => $request->cid,

        ]);


        return response()->json(['message' => 'Pengguna berhasil diperbarui.']);
    }

    // Menghapus pengguna dari database
    public function destroy(Network $network)
    {
        $network->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
