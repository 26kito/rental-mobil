<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Car;
use App\Models\Rent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/rent/{car_id}",
     *     tags={"rent"},
     *     summary="Rent a car for user",
     *     operationId="rentCar",
     *     @OA\Parameter(
     *         in="path",
     *         name="car_id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="rent_date",
     *                          type="date"
     *                      ),
     *                      @OA\Property(
     *                          property="return_date",
     *                          type="date"
     *                      )
     *                 ),
     *                 example={
     *                     "rent_date":"DD-MM-YYYY",
     *                     "return_date":"DD-MM-YYYY"
     *                }
     *             )
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(mediaType="application/json")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed",
     *         @OA\MediaType(mediaType="application/json")
     *     ),
     *     security={ {"passport": {}} }
     * )
     */
    public function rentCar(Request $request, $carId)
    {
        $car = Car::where('id', $carId)->first();
        if (Auth::user()->role_id === 1 && Auth::user()->token()->user_id === Auth::id()) {
            try {
                // If there is car and car status is available then move to next process
                if ($car !== null && $car->status_id === 1) {
                    $validated = Validator::make($request->all(), [
                        'customer_id' => 'exists:users,id',
                        'car_id' => 'exists:cars,id',
                        'rent_date' => 'required|date_format:d-m-Y|after_or_equal:today',
                        'return_date' => 'required|date_format:d-m-Y|after_or_equal:rent_date'
                    ]);
                    // If data validated, then move to next process
                    if ($validated->passes()) {
                        $data = Rent::create([
                            'customer_id' => Auth::id(),
                            'car_id' => $carId,
                            'rent_date' => Carbon::createFromFormat('d-m-Y', $request->rent_date),
                            'return_date' => Carbon::createFromFormat('d-m-Y', $request->return_date)
                        ]);
                        Car::where('id', $carId)->update(['status_id' => 3]);
                        return response()->json([
                            'message' => 'Success',
                            'data' => $data
                        ], 201);
                        // If data is not validated, then throw error
                    } else {
                        return response()->json(['message' => $validated->errors()], 200);
                    }
                } else {
                    return response()->json(['message' => 'There\'s no data!'], 404);
                }
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()]);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }
}
