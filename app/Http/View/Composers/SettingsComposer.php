<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Transaction;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\Auth;

class SettingsComposer
{
    protected $settings;
    protected $transaction;
    public function __construct()
    {
        $this->transaction = Transaction::where('user_id', Auth::id())->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->paginate(4);
        $this->settings = SettingsHelper::getAllSettings();
    }

    public function compose(View $view)
    {
        $notifcount = Transaction::where('user_id', Auth::id())->where('is_read', false)->count();
        $view->with('notifcount', $notifcount);
        $view->with('notif',  $this->transaction);
        $view->with('settings', $this->settings);
    }
}
