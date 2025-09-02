<?php

namespace App\Http\Controllers;

use App\Models\Convertion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConvertionController extends Controller
{
    public function index()
    {

        $startDate =  request('startDate');
        $endDate =  request('endDate');

        $convertionsQuery = Convertion::query();

        if (auth()->check() && !auth()->user()->is_admin) {
            $convertionsQuery->where('user_id', auth()->user()->id);
        }

        if ($startDate && $endDate) {
            // Parse the start and end date using Carbon
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $convertionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $convertions = $convertionsQuery->orderBy('created_at', 'desc')
            ->with('user', 'network')
            ->paginate(10)->onEachSide(0);
        $filteredCount = $convertions->count();
        return view('convertions.index', compact('convertions', 'filteredCount'));
    }
}
