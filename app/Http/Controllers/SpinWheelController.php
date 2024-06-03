<?php

namespace App\Http\Controllers;

use App\Models\{FileUpload, Prize, PrizeWinner, SpinLog};
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpinWheelController extends Controller
{
    // List all prizes
    /**
     *    @OA\Info(
     *    title="Lucky Spin API",
     *    description="List of all api",
     *    version="1.0.0",
     * )
     * @OA\Get(
     *      path="/api/spin-prize",
     *     tags={"Prizes"},
     *      summary="List All Prizes",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items (
     *                      type="object",
     *                      @OA\Property(property="id", type="numeric", format="numeric", example="1"),
     *                      @OA\Property(property="image", type="string", format="string", example="null"),
     *                      @OA\Property(property="name", type="string", format="string", example="Rs 100 Top-Up"),
     *                      @OA\Property(property="probability", type="string", format="string", example="0.00000005")
     *                  )
     *             )
     *         )
     *     )
     *  )
     */
    public function list()
    {
        try {
            $result = Prize::orderBy('created_at', 'DESC')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image' => !empty($item->image) ? asset('storage/prizes/' . $item->image) : null,
                    'name' => $item->name,
                    'probability' => $item->probability
                ];
            });
            return response()->json(['data' => $result], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }

    // Edit prize
    /**
     * @OA\Get(
     *      path="/api/spin-prize/{id}",
     *      tags={"Prizes"},
     *      summary="Edit prize detail",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="numeric", format="numeric", example="1"),
     *             @OA\Property(property="image", type="string", format="string", example="null"),
     *             @OA\Property(property="name", type="string", format="string", example="Rs 100 Top-Up"),
     *             @OA\Property(property="probability", type="string", format="string", example="0.00000005")
     *         )
     *     )
     *  )
     */
    public function edit($id)
    {
        try {
            $prize = Prize::findOrFail($id);
            $dataArray = [
                'id' => $prize->id,
                'image' => !empty($prize->image) ? asset('storage/prizes/' . $prize->image) : null,
                'name' => $prize->name,
                'probability' => $prize->probability
            ];
            return response()->json($dataArray, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Prize not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }

    // Store prize detail
    /**
     * @OA\Post(
     *      path="/api/spin-prize",
     *      tags={"Prizes"},
     *      summary="Store prize detail",
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *            required={"name","probability","image"},
     *            @OA\Property(property="name", type="string", format="string", example="Rs 1000"),
     *            @OA\Property(property="probability", type="string", format="string", example="0.0000045"),
     *            @OA\Property(property="image", type="string", format="string", example="http://localhost:8087/storage/about/rWoq4SHrej5qiIw0BMUb1711468907.jpg"),
     *         ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully prize detail saved."),
     *         )
     *     )
     *  )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:prizes,name,' . $request->id,
                'probability' => 'required|numeric|min:0|max:100',
                'image' => "nullable|file|mimes:jpg,jpeg,png,svg|max:2048"
            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422);
            }

            // Create a new Prize
            $prize = new Prize();
            $prize->name = $request->name;
            $prize->probability = $request->probability;

            // Image
            if ($request->hasFile('image')) {
                $prize->image = FileUpload::uploadFile(\request()->file('image'), 'prizes');
            }
            if (!$prize->save()) {
                return response()->json(['message' => 'Could not save prize detail.'], 400);
            }
            return response()->json([
                'message' => 'Successfully prize detail saved.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error.'
            ], 500);
        }
    }

    // Update prize detail
    /**
     * @OA\Put(
     *      path="/api/spin-prize/{id}",
     *      tags={"Prizes"},
     *      summary="Update prize detail",
     *      @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           description="ID",
     *           @OA\Schema(
     *               type="integer",
     *               format="int64",
     *               example="2"
     *           )
     *      ),
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *            required={"name","probability","image"},
     *            @OA\Property(property="name", type="string", format="string", example="Rs 1000"),
     *            @OA\Property(property="probability", type="string", format="string", example="0.0000045"),
     *            @OA\Property(property="image", type="string", format="string", example="http://localhost:8087/storage/about/rWoq4SHrej5qiIw0BMUb1711468907.jpg"),
     *         ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully prize detail updated."),
     *         )
     *     )
     *  )
     */
    public function update(Request $request, $id)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:prizes,name,' . $id,
                'probability' => 'required|numeric|min:0|max:100',
                'image' => "nullable|file|mimes:jpg,jpeg,png,svg|max:2048"
            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422);
            }
            $prize = Prize::findOrFail($id);
            $prize->name = $request->name;
            $prize->probability = $request->probability;

            // Image
            if ($request->hasFile('image')) {
                $fileArray = [];
                $fileArray[] = 'prizes/'.$prize->image;
                $prize->image = FileUpload::uploadFile(\request()->file('image'), 'prizes');
                FileUpload::deleteFiles($fileArray);
            }
            if (!$prize->save()) {
                return response()->json(['message' => 'Could not update prize detail.'], 400);
            }

            return response()->json([
                'message' => 'Successfully prize detail updated.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Prize not found.'
            ], 404);
        } catch (Exception $e){
            return response()->json([
                'message' => 'Internal Server Error.'
            ], 500);
        }
    }

    // Delete Prize
    /**
     * @OA\Delete(
     *      path="/api/spin-prize/{id}",
     *      tags={"Prizes"},
     *      summary="Delete prize detail",
     *      @OA\Parameter(
     *          name="id",
     *          description="Id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              example="2"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Successfully prize deleted."),
     *          )
     *      )
     * )
     */
    public function delete($id)
    {
        try {
            $prize = Prize::findOrFail($id);
            if (!$prize->delete()) {
                $fileArray = [];
                $fileArray[] = 'prizes/'.$prize->image;
                FileUpload::deleteFiles($fileArray);

                return response()->json([
                    'message' => 'Could not delete prize detail.'
                ], 400);
            }
            return response()->json([
                'message' => 'Successfully prize deleted.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Prize not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    }

    // Store winner detail
    /**
     * @OA\Post(
     *      path="/api/winner/store-winner",
     *      tags={"Prize Winner"},
     *      summary="Store winner detail",
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *            required={"user_id","prize_id"},
     *            @OA\Property(property="user_id", type="numeric", format="numeric", example="1"),
     *            @OA\Property(property="prize_id", type="numeric", format="numeric", example="3")
     *         ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully winner detail saved."),
     *         )
     *     )
     *  )
     */
    public function storeWinner()
    {
        try {
            $validatedData = Validator::make(\request()->all(), [
                'user_id' => 'required|numeric',
                'prize_id' => 'required|numeric|exists:prizes,id'
            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422);
            }

            $winner = new PrizeWinner();
            $winner->user_id = \request()->user_id;
            $winner->prize_id = \request()->prize_id;
            if (!$winner->save()) {
                return response()->json(['message' => 'Could not save winner detail.'], 404);
            }
            return response()->json([
                'message' => 'Successfully winner detail saved.'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }

    // Store winner detail
    /**
     * @OA\Put(
     *      path="/api/winner/change-status/{id}",
     *      tags={"Prize Winner"},
     *      summary="Update winner detail",
     *      @OA\Parameter(
     *           name="id",
     *           description="Id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(
     *               type="integer",
     *               format="int64",
     *               example="2"
     *           )
     *       ),
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *            required={"status"},
     *            @OA\Property(property="status", type="string", format="string", example="completed")
     *         ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully winner status changed."),
     *         )
     *     )
     *  )
     */
    public function changeStatusWinner(Request $request, $id)
    {
        try {
            $validatedData = Validator::make(\request()->all(), [
                'status' => 'required|string|in:pending,completed'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422);
            }
            $prizeWinner = PrizeWinner::findOrFail($id);
            $prizeWinner->status = $request->status;
            if (!$prizeWinner->save()) {
                return response()->json([
                    'message' => 'Could not change winner status.'
                ], 400);
            }
            return response()->json([
                'message' => 'Successfully winner status changed.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Winner not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error.'
            ], 500);
        }
    }

    // Store spin log
    /**
     * @OA\Post(
     *      path="/api/store-spin-log",
     *      tags={"Spin Log"},
     *      summary="Store spin log",
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *            required={"user_id"},
     *            @OA\Property(property="user_id", type="numeric", format="numeric", example="1")
     *         ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully spin log saved."),
     *         )
     *     )
     *  )
     */
    public function storeSpinLog(Request $request)
    {
        try {
            if (empty(env('SPIN_COUNT'))) {
                return response()->json(['message' => 'Please set spin count.'], 400);
            }
            $validatedData = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422);
            }
            $status = SpinLog::checkAvailability();
            if ($status === 'N') {
                return response()->json([
                    'message' => "You don't have any spin left for today. Come back, tomorrow."
                ], 400);
            }
            $spinLog = new SpinLog();
            $spinLog->user_id = $request->user_id;
            if (!$spinLog->save()) {
                return response()->json(['message' => 'Could not save spin log.'], 404);
            }
            return response()->json([
                'message' => 'Successfully spin log saved.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error.'
            ], 500);
        }
    }

    // Check Spin Availability
    /**
     * @OA\Post(
     *      path="/api/check-spin",
     *      tags={"Spin Availability"},
     *      summary="Check available spin for today.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="numeric", format="numeric", example="1")
     *          ),
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully spin status fetched."),
     *             @OA\Property(property="is_spin_available", type="string", example="Y"),
     *         )
     *     )
     *  )
     */
    public function checkTodaySpin(Request $request)
    {
        try {
            if (empty(env('SPIN_COUNT'))) {
                return response()->json(['message' => 'Please set spin count.'], 400);
            }
            $validatedData = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $validatedData->errors(),
                ], 422);
            }
            $status = SpinLog::checkAvailability();
            return response()->json([
                'message' => 'Successfully spin status fetched.',
                'is_spin_available' => $status
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error.'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/spin-prize/get-list",
     *     tags={"Public APIs"},
     *      summary="List All Prizes (NOTE :- Decrypt using CryptoJS)",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items (
     *                      type="object",
     *                      @OA\Property(property="image", type="string", format="string", example="null"),
     *                      @OA\Property(property="name", type="string", format="string", example="Rs 100 Top-Up"),
     *                      @OA\Property(property="enc-key", type="string", format="string", example="4asXdDsf323dfd"),
     *                      @OA\Property(property="probability", type="string", format="string", example="cDZZPXyWPXjLs6adYy9i6w==")
     *                  )
     *             )
     *         )
     *     )
     *  )
     */
    public function getList()
    {
        try {
            $method = 'aes128';
            $pass = '4asXdDsf323dfd';
            $iv = '4f01bede9221586c';
            $result = Prize::orderBy('created_at', 'DESC')->get()->map(function ($item) use ($method, $pass, $iv) {
                return [
                    'image' => !empty($item->image) ? asset('storage/prizes/' . $item->image) : null,
                    'name' => $item->name,
                    'enc-key' => $pass,
                    'probability' => openssl_encrypt($item->probability, $method, $pass, null, $iv)
                ];
            });
            return response()->json(['data' => $result], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }
}
