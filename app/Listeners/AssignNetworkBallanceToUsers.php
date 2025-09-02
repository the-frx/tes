<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\NetworkCreated;
use App\Models\NetworkBallance;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignNetworkBallanceToUsers
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NetworkCreated $event): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $networkBalance = new NetworkBallance;
            $networkBalance->user_id = $user->id;
            $networkBalance->network_id = $event->network->id;
            $networkBalance->balance = 0; // atau nilai default lainnya
            $networkBalance->save();
        }
    }
}
