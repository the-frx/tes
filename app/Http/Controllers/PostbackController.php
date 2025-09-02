<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bonus;
use App\Models\Network;
use App\Models\Convertion;
use App\Models\Transaction;
use App\Helpers\SettingsHelper;
use App\Models\NetworkBallance;
use App\Models\Traffic;
use Stevebauman\Location\Facades\Location;

class PostbackController extends Controller
{
    public function index()
    {
        $settings = SettingsHelper::getAllSettings();
        $key = request('key');
        $settingsKey = $settings['Postback_Key'];
        if ($key == $settingsKey) {
            $network = request('network');
            $alias = Network::where('alias', $network)->first();
            if ($alias) {
                $id = request('id');
                if ($alias->cid) {
                    $cid = Traffic::where('cid', $id)->first();
                    if ($cid) {
                        $userId = $cid->user_id;
                        $user = User::find($userId);
                        $country = $cid->country;
                    }
                } else {
                    $country = request('country');
                    $user = User::where('username', $id)->first();
                }
                if ($user) {
                    if (strlen($country) != 2 || !ctype_upper($country)) {
                        $location = Location::get($country);
                        $getcountrycode = $location->countryCode;
                        $country = $getcountrycode;
                    }
                    $ballance = request('ballance');
                    if ($user->custom_fee) {
                        $defaultFee = $user->fee;
                    } else {
                        $defaultFee = $settings['Default_Fee'];
                    }
                    $amountToDeduct = ($defaultFee / 100) * $ballance;
                    $ballance -= $amountToDeduct;
                    if ($user->referal != 'system') {
                        $referal = User::where('username', $user->referal)->first();
                        if ($referal) {
                            $percentageToReferal = 0.1;
                            $amountToReferal = $ballance * $percentageToReferal;
                            Bonus::create([
                                'from'  => $user->username,
                                'country' =>  $country,
                                'ballance' =>  $amountToReferal,
                                'network_id' =>  $alias->id,
                                'user_id' => $referal->id
                            ]);
                            NetworkBallance::where('user_id', $referal->id)
                                ->where('network_id', $alias->id)
                                ->increment('balance', $amountToReferal);
                            User::where('id', $referal->id)->increment('ballance', $amountToReferal);
                            $transaction = Transaction::where('user_id', $referal->id)->orderBy('created_at', 'desc')->first();
                            $newAmount = ($transaction ? $transaction->amount : 0) + $ballance;
                            Transaction::create([
                                'network_id' => $alias->id,
                                'type' => 'Bonus',
                                'ballance' => $amountToReferal,
                                'amount' => $newAmount,
                                'user_id' => $referal->id,
                                'is_read' => false,
                            ]);
                            $ballance -= $amountToReferal;
                        }
                    }
                    $transaction = Transaction::where('user_id',  $user->id)->orderBy('created_at', 'desc')->first();
                    $newAmount = ($transaction ? $transaction->amount : 0) + $ballance;
                    Transaction::create([
                        'network_id' => $alias->id,
                        'type' => 'Convertion',
                        'ballance' => $ballance,
                        'amount' => $newAmount,
                        'user_id' => $user->id,
                        'is_read' => false,
                    ]);
                    NetworkBallance::where('user_id', $user->id)
                        ->where('network_id', $alias->id)
                        ->increment('balance', $ballance);
                    User::where('id', $user->id)->increment('ballance', $ballance);
                    Convertion::create([
                        'country' =>  $country,
                        'ballance' =>  $ballance,
                        'network_id' =>  $alias->id,
                        'user_id' => $user->id
                    ]);
                }
                return '';
            }
            return '';
        }
        return '';
    }
}
