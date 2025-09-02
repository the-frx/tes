<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $paymentsQuery = Payment::query();

        if (auth()->check() && !auth()->user()->is_admin) {
            $paymentsQuery->where('user_id', auth()->user()->id);
        }

        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $paymentsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $payments = $paymentsQuery->orderBy('created_at', 'desc')
            ->with('user', 'network')
            ->paginate(10)->onEachSide(0);
        $filteredCount = $payments->count();
        return view('payments.index', compact('payments', 'filteredCount'));
    }
}
