<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    public function index()
    {
        $search = request('search');
        $domains = Domain::when($search, function ($query, $search) {
            return $query->where('domain', 'like', "%{$search}%");
        })->with('user')
            ->orderBy('created_at', 'desc')  // Urutkan berdasarkan kolom created_at secara descending
            ->paginate(10)->onEachSide(0);

        return view('domains.index', compact('domains'));
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
            'domain' => 'required|string|max:255|unique:domains,domain|regex:/^(?:(?=[\p{L}\p{N}-]{1,63}\.)(xn--)?((?!-)[a-zA-Z0-9-]{0,62}[a-zA-Z0-9]\.)*(?!-)[a-zA-Z0-9-]{1,62}[a-zA-Z0-9]$)/i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Domain::create([
            'domain' => $request->input('domain'),
            'user_id' => $user->id,
        ]);


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
    public function edit(Domain $domain)
    {
        return response()->json($domain);
    }

    // Mengupdate pengguna yang ada dalam database
    public function update(Request $request, Domain $domain)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'domain' => 'required|string|max:255|unique:domains,domain|regex:/^(?:(?=[\p{L}\p{N}-]{1,63}\.)(xn--)?((?!-)[a-zA-Z0-9-]{0,62}[a-zA-Z0-9]\.)*(?!-)[a-zA-Z0-9-]{1,62}[a-zA-Z0-9]$)/i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update data pengguna
        $domain->update([
            'domain' => $request->domain,
        ]);


        return response()->json(['message' => 'Pengguna berhasil diperbarui.']);
    }

    // Menghapus pengguna dari database
    public function destroy(Domain $domain)
    {
        $domain->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
