<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Car;
use App\Models\Rent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (Auth::user()->role_id === 1 && Auth::user()->token()->user_id === Auth::id()) {
            try {
                $car = Car::where('id', $carId)->first();
                // If there is car and car status is available then move to next process
                if ($car !== null && $car->status_id === 1) {
                    $validated = Validator::make($request->only('rent_date', 'return_date'), [
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
                        return response()->json([
                            'message' => 'Success',
                            'data' => $data
                        ], 201);
                        // If data is not validated, then throw error
                    } else {
                        return response()->json(['message' => $validated->errors()], 400);
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

    public function owner()
    {
        if (Auth::user()->role_id === 2 && Auth::user()->token()->user_id === Auth::id()) {
            $data = DB::table('rents')
                ->join('users', 'customer_id', 'users.id')
                ->join('cars', 'car_id', 'cars.id')
                ->select('users.id', 'users.name', 'cars.brand_car', 'rents.rent_date', 'rents.return_date')
                ->where('cars.owner_id', Auth::id())
                ->where('rent_status', 1)
                ->get();
            if ($data->isNotEmpty()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'message' => 'there\'s no data!'
                ], 404);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }

    public function approval(Request $request, $customer_id)
    {
        if (Auth::user()->role_id === 2 && Auth::user()->token()->user_id === Auth::id()) {
            $validated = Validator::make($request->only('status_id'), [
                'status_id' => 'integer|between:1,2'
            ]);
            if ($validated->passes()) {
                $rent = DB::table('rents')
                    ->join('users', 'customer_id', 'users.id')
                    ->join('cars', 'car_id', 'cars.id')
                    ->select('users.id', 'users.name', 'cars.brand_car', 'rents.rent_date', 'rents.return_date')
                    ->where('rents.customer_id', $customer_id)
                    ->where('cars.owner_id', Auth::id())
                    ->where('rent_status', 1)
                    ->first();
                if ($rent->rent_status === 1) {
                    if ($request->status_id == 1) {
                        $acceptRent = Rent::where('customer_id', $customer_id)->update(['rent_status' => 2]);
                        if ($acceptRent) {
                            DB::table('cars')
                                ->join('rents', 'car_id', 'cars.id')
                                ->where('cars.owner_id', Auth::id())
                                ->where('rents.customer_id', $customer_id)
                                ->update(['cars.status_id' => 2]);
                            return response()->json(['message' => 'success'], 200);
                        }
                    } else {
                        Rent::where('customer_id', $customer_id)->update(['rent_status' => 3]);
                        return response()->json(['message' => 'success'], 200);
                    }
                } else {
                    return response()->json(['message' => 'not found'], 404);
                }
            } else {
                return response()->json(['message' => $validated->errors()], 400);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }
}
