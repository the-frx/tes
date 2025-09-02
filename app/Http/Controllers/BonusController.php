<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BonusController extends Controller
{
    public function index(Request $request)
    {

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $bonusesQuery = Bonus::query();

        if (auth()->check() && !auth()->user()->is_admin) {
            $bonusesQuery->where('user_id', auth()->user()->id);
        }

        if ($startDate && $endDate) {
            // Parse the start and end date using Carbon
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $bonusesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $bonuses = $bonusesQuery->orderBy('created_at', 'desc')
            ->with('user', 'network')
            ->paginate(10)->onEachSide(0);
        $filteredCount = $bonuses->count();
        return view('bonuses.index', compact('bonuses', 'filteredCount'));
    }
}
