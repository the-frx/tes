<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index()
    {

        $startDate =  request('startDate');
        $endDate =  request('endDate');

        $transactionsQuery = Transaction::query();

        if (auth()->check()) {
            $transactionsQuery->where('user_id', auth()->user()->id);
        }

        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $transactionsQuery->orderBy('created_at', 'desc')
            ->with('user', 'network')
            ->paginate(10)->onEachSide(0);
        $filteredCount = $transactions->count();
        return view('transactions.index', compact('transactions', 'filteredCount'));
    }
    public function getTransactionDetails($id)
    {
        $transaction = Transaction::with('user', 'network')->find($id);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        $transaction->is_read = true;
        $transaction->save();

        return response()->json($transaction);
    }
}
