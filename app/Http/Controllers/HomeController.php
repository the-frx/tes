<?php

namespace App\Http\Controllers;

use App\Models\link;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Traffic;
use App\Models\Convertion;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $linkall = link::count();
        $linkuser = link::where('user_id', Auth::id())->count();
        $convertionall = Convertion::sum('ballance');
        $convertionuser = Convertion::where('user_id', Auth::id())->sum('ballance');
        $bonusall = Bonus::sum('ballance');
        $bonususer = Bonus::where('user_id', Auth::id())->sum('ballance');
        $ballanceall = User::sum('ballance');
        $revenue = User::where('id', Auth::id())
            ->whereHas('networkBallances', function ($query) {
                $query->where('balance', '>', 0);
            })
            ->with(['networkBallances.network'])
            ->orderBy('created_at', 'desc')
            ->get();
        $user = User::where('id', Auth::id())->with('bank')->first();
        $alltraffic = Traffic::where('user_id', Auth::id())->count();
        $allconvertion = Convertion::where('user_id', Auth::id())->count();
        $transactions = Transaction::where('user_id', Auth::id())->orderByDesc('created_at')
            ->with('user', 'network')
            ->paginate(10)->onEachSide(0);

        return view('home', compact(
            'transactions',
            'alltraffic',
            'allconvertion',
            'user',
            'bonusall',
            'bonususer',
            'ballanceall',
            'revenue',
            'linkall',
            'linkuser',
            'convertionuser',
            'convertionall'
        ));
    }
}
