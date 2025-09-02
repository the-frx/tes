<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bonus;
use App\Models\Network;
use App\Models\Payment;
use App\Models\Convertion;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\NetworkBallance;
use Illuminate\Support\Facades\Validator;


class TransferController extends Controller
{
    public function index()
    {
        $search = request('search');
        $users = User::when($search, function ($query, $search) {
            return $query->where('username', 'like', "%{$search}%");
        })
            ->whereHas('networkBallances', function ($query) {
                $query->where('balance', '>', 0);
            })
            ->with(['networkBallances.network'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)->onEachSide(0);

        $network = Network::all();
        return view('transfers.index', compact('users', 'network'));
    }
    public function now(User $user)
    {
        $user->load(['networkBallances.network', 'bank']);
        return view('transfers.now', compact('user'));
    }
    public function transfer(Request $request)
    {
        $inputData = $request->all();
        $inputData['startDate'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('startDate'))->format('Y-m-d');
        $inputData['endDate'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('endDate'))->format('Y-m-d');

        // Validate the request data
        $validator = Validator::make($inputData, [
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d'],
            'network_id' => ['required', 'integer'],
            'ballance' => ['required', 'numeric'],
            'imgurl' => 'required|url|max:255',
            'rate' => ['required', 'numeric'],
            'amount' => ['required', 'numeric'],
            'method' => ['required', 'string', 'max:255'],
            'user_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new Payment instance and store it in the database
        Payment::create($validator->validated());
        $transaction = Transaction::where('user_id',  $inputData['user_id'])->orderBy('created_at', 'desc')->first();
        $negatif = $inputData['ballance'] = -1 * abs($inputData['ballance']);
        $newAmount = ($transaction ? $transaction->amount : 0) + $negatif;
        Transaction::create([
            'network_id' => $request->input('network_id'),
            'type' => 'Payout',
            'ballance' => $negatif,
            'amount' => $newAmount,
            'user_id' => $inputData['user_id'],
            'is_read' => false,
        ]);
        NetworkBallance::where('user_id', $inputData['user_id'])
            ->where('network_id', $inputData['network_id'])
            ->increment('balance', $negatif);
        User::where('id', $inputData['user_id'])->increment('ballance', $negatif);

        // Return a success response
        return redirect(route('transfers'))->with(['success' => 'Transfer successfully'], 201);
    }
    public function getBalance(Request $request, User $user)
    {
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $request->startDate);
            $endDate = Carbon::createFromFormat('d/m/Y', $request->endDate)->endOfDay(); // Menambahkan akhir hari agar mendapatkan data dari seluruh hari tersebut.
            $networkId = $request->network_id;

            $totalBonus = Bonus::where('user_id', $user->id)
                ->where('network_id', $networkId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('ballance');

            $totalConvertion = Convertion::where('user_id', $user->id)
                ->where('network_id', $networkId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('ballance');

            $totalBalance = $totalBonus + $totalConvertion;

            return response()->json(['totalBalance' => $totalBalance]);
        } catch (\Exception $e) {
            // Jika ada kesalahan, kirim respons dengan pesan kesalahan.
            return response()->json(['error' => 'Something went wrong. ' . $e->getMessage()], 500);
        }
    }
}
