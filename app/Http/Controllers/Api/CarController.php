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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1);
        $data = Cache::remember('get_all_car_page=' . $page, 60, function () {
            return DB::table('cars')
                ->join('car_descriptions AS cd', 'cars.id', 'cd.car_id')
                ->join('users', 'cars.owner_id', 'users.id')
                ->join('car_statuses AS cs', 'cars.status_id', 'cs.id')
                ->select('cars.id', 'cars.brand_car', 'users.name AS owner_name', 'cs.status', 'cd.capacity')
                ->where('cars.status_id', 1)
                ->simplePaginate(10);
        });
        try {
            if ($data->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Success',
                    'data' => $data
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        // Check if user is an car_owner
        $firstValidated = Validator::make($request->only('brand_car'), [
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
                    $secondValidated = Validator::make($request->only('car_model_year', 'color', 'capacity', 'no_plate'), [
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
                            'success' => true,
                            'message' => 'Data created successfully!',
                            'data' => $data
                        ], 201);
                        // If validation is failed, then throw error
                    } else {
                        return response()->json(['success' => false, 'message' => $secondValidated->errors()]);
                    }
                    // If car is failed to create then throw error
                } else {
                    DB::rollback();
                    return response()->json(['success' => false, 'message' => 'Failed to create'], 400);
                }
            } catch (Exception $e) {
                DB::rollback();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            // If validation is failed, then throw error
        } else {
            return response()->json(['success' => false, 'message' => $firstValidated->errors()], 400);
        }
    }

    public function show($carId)
    {
        $data = Cache::remember('show_car=' . $carId, 60, function () use ($carId) {
            return DB::table('cars')
                ->join('car_descriptions AS cd', 'cars.id', 'cd.car_id')
                ->join('users', 'cars.owner_id', 'users.id')
                ->where('cars.id', $carId)
                ->select('cars.brand_car', 'cd.car_model_year', 'cd.color', 'cd.capacity', 'cd.no_plate', 'users.name AS car_owner', 'users.mobile_phone', 'users.email', 'users.address')
                ->first();
        });
        if ($data != null) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $data
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
        }
    }

    public function search($keyword)
    {
        // Check if input is greater than 3 char
        if (strlen($keyword) >= 3) {
            $data = DB::table('cars')
                ->join('car_descriptions AS cd', 'cars.id', 'cd.car_id')
                ->join('users', 'cars.owner_id', 'users.id')
                ->join('car_statuses AS cs', 'cars.status_id', 'cs.id')
                ->select('cars.id', 'cars.brand_car', 'users.name AS owner_name', 'cs.status', 'cd.capacity')
                ->where('cars.brand_car', 'LIKE', '%' . $keyword . '%')
                ->get();

            try {
                if ($data->isNotEmpty()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Success',
                        'data' => $data
                    ], 200);
                } else {
                    return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
                }
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Please input 3 or more characters'], 400);
        }
    }

    public function carOwner()
    {
        $data = Cache::remember('car_owner_user=' . Auth::id(), 60, function () {
            return DB::table('cars')
                ->join('car_statuses AS cs', 'cars.status_id', 'cs.id')
                ->select('cars.id', 'cars.brand_car', 'cs.status')
                ->where('cars.owner_id', Auth::id())
                ->get();
        });

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $data
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
        }
    }

    public function updateCar(Request $request, $carId)
    {
        $car = Car::with('carDescription')->where('id', $carId)->where('owner_id', Auth::id())->find($carId);
        // Check if there is data
        if ($car) {
            DB::beginTransaction();
            $insert = $car->update(['brand_car' => $request->brand_car]);
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
                        'success' => true,
                        'message' => 'Data updated successfully!',
                        'data' => $data
                    ], 201);
                }
            } else {
                DB::rollback();
                return response()->json(['success' => false, 'message' => 'Failed to update'], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
        }
    }

    public function destroy($carId)
    {
        $data = Car::with('carDescription')
            ->where('id', $carId)
            ->where('owner_id', Auth::id())
            ->find($carId);

        if ($data) {
            $data->delete();
            return response()->json(['success' => false, 'message' => 'Data deleted successfully!'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
        }
    }
}
