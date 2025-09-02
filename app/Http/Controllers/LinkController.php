<?php

namespace App\Http\Controllers;

use App\Models\link;
use App\Models\Domain;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class LinkController extends Controller
{
    private function getFemaleName()
    {
        $faker = FakerFactory::create();
        $femaleName = $faker->firstNameFemale;

        return $femaleName;
    }
    protected function generateUniqueSub()
    {
        do {
            $name = $this->getFemaleName();
            $nameLower = strtolower($name); // Mengubah nama menjadi huruf kecil
            $randomNumber = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT); // Menghasilkan angka acak 6 digit
            $sub = $nameLower . '-' . $randomNumber;
        } while (Link::where('sub', $sub)->exists());
        return $sub;
    }
    private function generatedomain()
    {
        $domains = Domain::inRandomOrder()->first();
        return $domains->domain;
    }
    protected function generateCustomHash($input, $length)
    {
        $input = $input . uniqid();
        $hash = substr(hash('sha256', $input), 0, $length);
        return $hash;
    }
    protected function generateUniqueAlias()
    {
        do {
            $aliasLength = 30;
            $alias = $this->generateCustomHash(date('YmdHis') . rand(5, 10), $aliasLength);
        } while (Link::where('alias', $alias)->exists());

        return $alias;
    }
    public function gemerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'integer|min:1|max:10',
            'network' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Error: ' . implode(', ', $validator->errors()->all())], 400);
        }

        $numberOfResults = min($request->input('jumlah', 1), 10);
        $results = [];
        DB::beginTransaction();
        try {
            for ($i = 0; $i < $numberOfResults; $i++) {
                $domain = $this->generatedomain();
                if (empty($domain)) {
                    DB::rollBack();
                    return response()->json(['error' => 'Domain Nothing'], 400);
                }
                $sub = $this->generateUniqueSub();
                $alias = $this->generateUniqueAlias();
                $host = $sub . '.' . $domain;
                $network = $request->input('network');
                $target = route('redirect') . '?network=' . $network . '&id=' . Auth::user()->username;
                $data = [
                    'user_id' => Auth::id(),
                    'sub' => $sub,
                    'domain' => $domain,
                    'alias' => $alias,
                    'host' => $host,
                    'target' => $target,
                ];
                Link::create($data);
                $resultHost = 'https://' . $sub . '.' . $domain;
                $resultHostAlias = 'https://' . $sub . '.' . $domain . '/' . $alias;
                $results[] = [
                    'resultHost' => $resultHost,
                    'resultHostAlias' => $resultHostAlias,
                ];
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while saving data: ' . $e->getMessage()], 500);
        }

        return response()->json(['results' => $results]);
    }

    public function smartlinks()
    {

        $startDate =  request('startDate');
        $endDate =  request('endDate');

        $smartlinksQuery = link::query();

        if (auth()->check() && !auth()->user()->is_admin) {
            $smartlinksQuery->where('user_id', auth()->user()->id);
        }

        if ($startDate && $endDate) {
            // Parse the start and end date using Carbon
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $smartlinksQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $smartlinks = $smartlinksQuery->orderBy('created_at', 'desc')
            ->with('user')
            ->paginate(10)
            ->onEachSide(0);
        $network = Network::get();
        return view('links.smartlinks', compact('smartlinks', 'network'));
    }
    public function destroy(Link $link)
    {
        $link->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
