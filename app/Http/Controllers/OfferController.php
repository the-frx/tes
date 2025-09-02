<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Helpers\CountryHelper;
use App\Models\Network;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {

        $search = request('search');
        $offers = Offer::when(
            $search,
            function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            }
        )
            ->with('user', 'network')
            ->orderBy('created_at', 'desc')
            ->paginate(10)->onEachSide(0);
        $network = Network::get();
        $countryOptions = CountryHelper::getCountryOptions();
        return view('offers.index', compact('offers', 'countryOptions', 'network'));
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
            'name' => 'required|max:255',
            'network_id' => 'required|max:255',
            'country' => ['required', 'string', 'max:255'],
            'url_mobile' => 'required|url|max:255',
            'url_desktop' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Offer::create([
            'name' => $request->input('name'),
            'network_id' => $request->input('network_id'),
            'country' => $request->input('country'),
            'url_mobile' => $request->input('url_mobile'),
            'url_desktop' => $request->input('url_desktop'),
            'user_id' => $user->id,
        ]);


        return response()->json(['message' => 'Offer baru berhasil ditambahkan'], 200);
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
    public function edit(Offer $offer)
    {
        return response()->json($offer);
    }

    // Mengupdate pengguna yang ada dalam database
    public function update(Request $request, Offer $offer)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'network_id' => 'required|max:255',
            'country' => ['required', 'string', 'max:255'],
            'url_mobile' => 'required|url|max:255',
            'url_desktop' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update data pengguna
        $offer->update([
            'name' => $request->name,
            'network_id' => $request->network_id,
            'country' => $request->country,
            'url_mobile' => $request->url_mobile,
            'url_desktop' => $request->url_desktop,
        ]);


        return response()->json(['message' => 'Pengguna berhasil diperbarui.']);
    }

    // Menghapus pengguna dari database
    public function destroy(Offer $offer)
    {
        $offer->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
