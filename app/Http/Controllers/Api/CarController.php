<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Car;
use Illuminate\Http\Request;
use App\Models\CarDescription;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    public function index()
    {
        $data = Car::with('carDescription')->get();
        try {
            if ($data->isNotEmpty()) {
                return response()->json([
                    'message' => 'Success',
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'message' => 'There\'s no data found'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function store(Request $request)
    {
        // Check if user is an car_owner
        if (Auth::user()->role_id === 2) {
            $firstValidated = Validator::make($request->all(), [
                'brand_car' => 'required'
            ]);
            // If validation is passes, then proceed to next process
            if ($firstValidated->passes()) {
                DB::beginTransaction();
                try {
                    $insert = Car::create([
                        'brand_car' => $request->brand_car,
                        'owner_id' => Auth::id()
                    ]);
                    // If car created, then check the car description
                    if ($insert) {
                        $secondValidated = Validator::make($request->all(), [
                            'car_id' => 'unique:car_descriptions',
                            'car_model_year' => 'required|integer',
                            'color' => 'alpha',
                            'capacity' => 'integer|between:2,10',
                            'no_plate' => 'unique:car_descriptions|min:5|max:12'
                        ]);
                        // If validation is passes, then proceed to next process
                        if ($secondValidated->passes()) {
                            CarDescription::create([
                                'car_id' => $insert->id,
                                'car_model_year' => $request->car_model_year,
                                'color' => $request->color,
                                'capacity' => $request->capacity,
                                'no_plate' => strtoupper($request->no_plate),
                            ]);

                            DB::commit();
                            $data = Car::with('carDescription')->whereRelation('carDescription', 'car_id', $insert->id)->get();
                            return response()->json([
                                'message' => 'Data created successfully!',
                                'data' => $data
                            ], 201);
                            // If validation is failed, then throw error
                        } else {
                            return response()->json(['message' => $secondValidated->errors()]);
                        }
                        // If car is failed to create then throw error
                    } else {
                        DB::rollback();
                        return response()->json(['message' => 'Gagal'], 200);
                    }
                } catch (Exception $e) {
                    DB::rollback();
                    return response()->json(['message' => $e->getMessage()], 400);
                }
                // If validation is failed, then throw error
            } else {
                return response()->json(['message' => $firstValidated->errors()]);
            }
            // If user is not an car owner, 
        } else {
            return response()->json(['message' => 'Not authorized'], 403);
        }
    }

    public function show($carId)
    {
        $data = Car::with('carDescription')->find($carId);
        if ($data != null) {
            return response()->json([
                'message' => 'Success',
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'message' => 'No data found!'
            ], 200);
        }
    }

    public function update(Request $request, $carId)
    {
        if (Auth::user()->role_id === 2) {
            $car = Car::with('carDescription')->where('id', $carId)->where('owner_id', Auth::id())->find($carId);
            // Check if there is data
            if ($car) {
                DB::beginTransaction();
                $insert = $car->update([
                    'brand_car' => $request->brand_car
                ]);
                if ($insert) {
                    $validated = $request->validate([
                        'car_model_year' => 'integer',
                        'color' => 'alpha',
                        'capacity' => 'integer|between:2,10',
                        'no_plate' => 'min:5|max:12',
                        Rule::unique('car_descriptions')->ignore($request->no_plate)
                    ]);
                    if ($validated) {
                        CarDescription::where('car_id', $carId)->update([
                            'car_model_year' => $request->car_model_year,
                            'color' => $request->color,
                            'capacity' => $request->capacity,
                            'no_plate' => strtoupper($request->no_plate),
                        ]);

                        DB::commit();
                        $data = Car::with('carDescription')->where('id', $carId)->where('owner_id', Auth::id())->first();
                        return response()->json([
                            'message' => 'Data updated successfully!',
                            'data' => $data
                        ], 201);
                    }
                } else {
                    DB::rollback();
                    return response()->json(['message' => 'Gagal'], 200);
                }
            } else {
                return response()->json(['message' => 'There\'s no data found!'], 404);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }

    public function destroy($carId)
    {
        if (Auth::user()->role_id === 2) {
            $data = Car::with('carDescription')->where('id', $carId)->where('owner_id', Auth::id())->find($carId);
            if ($data) {
                $data->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No data found!'
                ], 404);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }
}
