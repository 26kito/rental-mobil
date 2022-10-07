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
    public function rentCar(Request $request, $carId)
    {
        // Check if customer rent is accepted
        $data = DB::table('rents')
            ->join('users', 'rents.customer_id', 'users.id')
            ->select('rents.rent_status')
            ->where('rents.customer_id', Auth::id())
            ->where('users.role_id', 1)
            ->whereNotIn('rents.rent_status', [1, 3])
            ->first();

        try {
            // If customer have already rent and accepted, then they can't rent another car
            if ($data) {
                return response()->json(['message' => 'Gabisa om'], 400);
            } else {
                if (Auth::user()->role_id === 1 && Auth::user()->token()->user_id === Auth::id()) {
                    $car = Car::where('id', $carId)->first();
                    // If there is a car and the car status is available then move to next process
                    if ($car !== null && $car->status_id === 1) {
                        $validated = Validator::make($request->only('rent_date', 'return_date'), [
                            'customer_id' => 'exists:users,id',
                            'car_id' => 'exists:cars,id',
                            'rent_date' => 'required|date_format:d-m-Y|after_or_equal:today',
                            'return_date' => 'required|date_format:d-m-Y|after_or_equal:rent_date'
                        ]);

                        // If data validated, then move to next process
                        if ($validated->passes()) {
                            $data = Rent::firstOrCreate(
                                [
                                    'customer_id' => Auth::id(),
                                    'car_id' => $carId,
                                ],
                                [
                                    'rent_date' => Carbon::createFromFormat('d-m-Y', $request->rent_date),
                                    'return_date' => Carbon::createFromFormat('d-m-Y', $request->return_date)
                                ]
                            );
                            return response()->json([
                                'message' => 'Success',
                                'data' => $data
                            ], 201);
                            // If data is not validated, then throw error
                        } else {
                            return response()->json(['message' => $validated->errors()], 400);
                        }
                    } else {
                        return response()->json(['message' => 'There\'s no data found!'], 404);
                    }
                } else {
                    return response()->json(['message' => 'Not authorized!'], 401);
                }
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function owner()
    {
        $data = DB::table('rents')
            ->join('users', 'customer_id', 'users.id')
            ->join('cars', 'car_id', 'cars.id')
            ->select('users.id', 'users.name', 'cars.brand_car', 'rents.rent_date', 'rents.return_date')
            ->where('cars.owner_id', Auth::id())
            ->where('rent_status', 1)
            ->get();
        if ($data->isNotEmpty()) {
            return response()->json([
                'message' => 'Success',
                'data' => $data
            ], 200);
        } else {
            return response()->json(['message' => 'There\'s no data found!'], 404);
        }
    }

    public function approval(Request $request, $customer_id)
    {
        $validated = Validator::make($request->only('approval_status'), [
            'approval_status' => 'required|boolean'
        ]);

        if ($validated->passes()) {
            $data = DB::table('rents')
                ->join('users', 'customer_id', 'users.id')
                ->join('cars', 'car_id', 'cars.id')
                ->select('users.id', 'users.name', 'cars.brand_car', 'rents.rent_date', 'rents.return_date', 'rents.rent_status')
                ->where('rents.customer_id', $customer_id)
                ->where('rents.rent_status', 1)
                ->where('cars.owner_id', Auth::id())
                ->first();
            if ($data !== null) {
                $base_query = DB::table('rents')
                    ->join('cars', 'car_id', 'cars.id')
                    ->where('cars.owner_id', Auth::id())
                    ->where('rents.customer_id', $customer_id);
                if ($request->approval_status == true) {
                    $acceptRent = $base_query->update(['rent_status' => 2]);
                    if ($acceptRent) {
                        DB::table('cars')
                            ->join('rents', 'car_id', 'cars.id')
                            ->where('cars.owner_id', Auth::id())
                            ->where('rents.customer_id', $customer_id)
                            ->update(['cars.status_id' => 2]);
                        return response()->json(['message' => 'Success'], 200);
                    }
                } else {
                    $base_query->update(['rent_status' => 3]);
                    return response()->json(['message' => 'Success'], 200);
                }
            } else {
                return response()->json(['message' => 'There\'s no data found!'], 404);
            }
        } else {
            return response()->json(['message' => $validated->errors()], 400);
        }
    }
}
