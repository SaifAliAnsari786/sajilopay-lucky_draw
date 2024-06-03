<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class SpinLog extends Model
{
    use HasFactory;

    public static function checkAvailability()
    {
        try {
            $today = Carbon::now()->format('Y-m-d');
            $userCount = SpinLog::where('created_at', 'LIKE', $today.'%')->where('user_id', request()->user_id)->count();
            $status = 'Y';
            if ($userCount >= env('SPIN_COUNT')) {
                $status = 'N';
            }
            return $status;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
