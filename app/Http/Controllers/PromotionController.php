<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function index()
    {
        $search = request('search');
        $promotions = Promotion::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->with('user')
            ->orderBy('created_at', 'desc')  // Urutkan berdasarkan kolom created_at secara descending
            ->paginate(10)->onEachSide(0);

        return view('promotions.index', compact('promotions'));
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
            'name' => ['required', 'string', 'max:255'],
            'link' => 'required|url|max:255|unique:promotions,link',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Promotion::create([
            'name' => $request->input('name'),
            'link' => $request->input('link'),
            'user_id' => $user->id,
        ]);


        return response()->json(['message' => 'Promotion baru berhasil ditambahkan'], 200);
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
    public function edit(Promotion $promotion)
    {
        return response()->json($promotion);
    }

    // Mengupdate pengguna yang ada dalam database
    public function update(Request $request, Promotion $promotion)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'link' => 'required|url|max:255|unique:promotions,link',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update data pengguna
        $promotion->update([
            'name' => $request->name,
            'link' => $request->link,
        ]);


        return response()->json(['message' => 'Promotion berhasil diperbarui.']);
    }

    // Menghapus pengguna dari database
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->json(['message' => 'Promotion berhasil dihapus.']);
    }
}
