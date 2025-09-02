<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MyTeamController extends Controller
{
    public function index()
    {
        $search = request('search');
        $startDate =  request('startDate');
        $endDate =  request('endDate');

        $myteams = User::when(auth()->check(), function ($query) use ($search, $startDate, $endDate) {
            $query->where('referal', auth()->user()->username);

            if ($startDate && $endDate) {
                // Parse the start and end date using Carbon
                $startDate = Carbon::parse($startDate)->startOfDay();
                $endDate = Carbon::parse($endDate)->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10)->onEachSide(0);
        $filteredCount = $myteams->count();
        return view('myteams.index', compact('myteams', 'filteredCount'));
    }
}
