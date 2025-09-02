<?php

namespace App\Models;

use App\Models\User;
use App\Models\Network;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function network()
    {
        return $this->belongsTo(Network::class);
    }
}
