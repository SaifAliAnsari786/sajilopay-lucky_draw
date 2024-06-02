<?php

namespace App\Http\Controllers;

use App\Models\prize;
use App\Models\PrizeWinner;
use App\Models\SpinLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Http\Request;

use function Laravel\Prompts\spin;

class SpinWheelController extends Controller
{

    public function getSpinWheel()
    {
        try {
            $result = Prize::orderBy('id', 'DESC')->get();
            return response()->json(['data' => $result], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }

    public function storeSpinWheel(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'probability' => 'required|numeric|min:0|max:100',
            ]);
    
            // Check if the validation fails
            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422); // Unprocessable Entity status code
            }
    
            // Get the validated data
            $validatedData = $validatedData->validated();
    
            // Create a new Prize
            $prize = new Prize();
            $prize->name = $validatedData['name'];
            $prize->probability = $validatedData['probability'];
            $prize->save();

            $prizeWinner = new PrizeWinner();
            $prizeWinner->user_id = '1';
            $prizeWinner->prize_id = $prize->id; // Associate with the newly created Prize
            $prizeWinner->save();

            if (!$prize) {
                return response()->json(['message' => 'Could not save prize.'], 404);
            }
    
            // Return a success response
            return response()->json([
                'message' => 'Successfully saved the Prize.'
            ], 200);
    
        } catch (Exception $e) {
    
            return response()->json([
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function updateSpinWheel(Request $request)
    {
        try{
            $prize = Prize::find(request('id'));
            $prize->name = $request['name'];
            $prize->probability = $request['probability'];
            $prize->save();
            if (!$prize) {
                return response()->json(['message' => 'Record not found.'], 404);
            }
    
            // Return a success response
            return response()->json([
                'message' => 'Successfully saved the Prize.'
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function deleteSpinWheel()
    {
        try {
            $prize = Prize::find(request('id'));
            
            if (!$prize) {
                return response()->json([
                    'message' => 'Record not found'
                ], 404);
            }

            $prize->delete();

            return response()->json([
                'message' => 'Successfully deleted the Prize.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function getWinnerId()
    {
        try {
            $result = PrizeWinner::orderBy('id', 'DESC')->get();
            return response()->json(['data' => $result], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }

    public function changeStatusWinner(Request $request)
    {
        try {
            $prizeWinner = PrizeWinner::find(request('id'));
            
            if (!$prizeWinner) {
                return response()->json(['message' => 'Record not found.'], 404);
            }
    
            $prizeWinner->status = $request['status'];
            $prizeWinner->save();
    
            return response()->json([
                'message' => 'Successfully Update the prizeWinner.'
            ], 200);
    
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    public function spinLog()
    {
        try{
            $spinLog = new SpinLog();
            $spinLog->user_id = '1';
            $spinLog->save();
            
            if(!$spinLog) {
                return response()->json(['message' => 'Could not save spin log.'], 404);
            }
            return response()->json([
                'message' => 'Successfully saved the spin log.'
            ], 200);
            

        }catch(Exception $e){
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function checkTodaySpin()
    {
        try {
            $today = Carbon::now()->format('Y-m-d');
            $userCount = SpinLog::where('created_at', 'LIKE', $today.'%')->where('user_id',1)->count();
            if ($userCount >= 3) {
                $status = 'not available';
            } else {
                $status = 'available';
            }
    
            return response()->json([
                'message' => 'Successfully spin status fetched.',
                'status' => $status
            ], 200);
    
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal server error.'
            ], 500);
        }
    }
    

}
