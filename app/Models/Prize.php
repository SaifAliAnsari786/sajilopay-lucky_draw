<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use HasFactory;

    public function prizeWinners()
    {
        return $this->hasMany(PrizeWinner::class);
    }
}
