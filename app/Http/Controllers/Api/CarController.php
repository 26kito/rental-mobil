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
    /**
     * @OA\Get(
     *     path="/api/v1/car",
     *     tags={"car"},
     *     summary="Get all data",
     *     operationId="index",
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
     * )
     */
    public function index()
    {
        $data = DB::table('cars')
            ->join('car_descriptions AS cd', 'cars.id', 'cd.car_id')
            ->join('users', 'cars.owner_id', 'users.id')
            ->join('car_statuses AS cs', 'cars.status_id', 'cs.id')
            ->select('cars.id', 'cars.brand_car', 'users.name AS owner_name', 'cs.status', 'cd.capacity')
            ->paginate(10);
        try {
            if ($data->isNotEmpty()) {
                return response()->json([
                    'message' => 'Success',
                    'data' => $data
                ], 200);
            } else {
                return response()->json(['message' => 'There\'s no data'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/car/create",
     *     tags={"car"},
     *     summary="Insert new car",
     *     operationId="store",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="brand_car",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="car_model_year",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="color",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="capacity",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="no_plate",
     *                          type="integer"
     *                      )
     *                 ),
     *                 example={
     *                     "brand_car":"Toyota",
     *                     "car_model_year":"2016",
     *                     "color":"blue",
     *                     "capacity":"4",
     *                     "no_plate":"AA1F28G"
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
    public function store(Request $request)
    {
        // Check if user is an car_owner
        if (Auth::user()->role_id === 2) {
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
                        return response()->json(['message' => 'Gagal'], 400);
                    }
                } catch (Exception $e) {
                    DB::rollback();
                    return response()->json(['message' => $e->getMessage()], 400);
                }
                // If validation is failed, then throw error
            } else {
                return response()->json(['message' => $firstValidated->errors()], 400);
            }
            // If user is not an car owner, 
        } else {
            return response()->json(['message' => 'Not authorized'], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/car/{car_id}",
     *     tags={"car"},
     *     summary="Get car by id",
     *     operationId="show",
     *     @OA\Parameter(
     *         in="path",
     *         name="car_id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(mediaType="application/json")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed",
     *         @OA\MediaType(mediaType="application/json")
     *     )
     * )
     */
    public function show($carId)
    {
        $data = DB::table('cars')
            ->join('car_descriptions AS cd', 'cars.id', 'cd.car_id')
            ->join('users', 'cars.owner_id', 'users.id')
            ->where('cars.id', $carId)
            ->select('cars.brand_car', 'cd.car_model_year', 'cd.color', 'cd.capacity', 'cd.no_plate', 'users.name AS car_owner', 'users.mobile_phone', 'users.email', 'users.address')
            ->first();
        if ($data != null) {
            return response()->json([
                'message' => 'Success',
                'data' => $data
            ], 200);
        } else {
            return response()->json(['message' => 'No data found!'], 404);
        }
    }

    public function carOwner()
    {
        if (Auth::user()->role_id === 2) {
            // $data = Car::with('user:id,name,id')->where('owner_id', Auth::id())->get();
            $data = DB::table('cars')
                ->join('car_statuses AS cs', 'cars.status_id', 'cs.id')
                ->select('cars.id', 'cars.brand_car', 'cs.status')
                ->where('cars.owner_id', Auth::id())
                ->get();
            if ($data->isNotEmpty()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $data
                ], 200);
            } else {
                return response()->json(['message' => 'There\'s no data found!'], 404);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/car/edit/{car_id}",
     *     tags={"car"},
     *     summary="Edit car",
     *     operationId="updateCar",
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
     *                          property="brand_car",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="car_model_year",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="color",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="capacity",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="no_plate",
     *                          type="integer"
     *                      )
     *                 ),
     *                 example={
     *                     "brand_car":"Toyota",
     *                     "car_model_year":"2016",
     *                     "color":"blue",
     *                     "capacity":"4",
     *                     "no_plate":"AA1F28G"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\MediaType(mediaType="application/json")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="error",
     *          @OA\MediaType(mediaType="application/json")
     *      ),
     *     security={ {"passport": {}} }
     * )
     */
    public function updateCar(Request $request, $carId)
    {
        if (Auth::user()->role_id === 2) {
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

    /**
     * @OA\Delete(
     *     path="/api/v1/car/delete/{car_id}",
     *     tags={"car"},
     *     summary="Delete car",
     *     operationId="destroy",
     *     @OA\Parameter(
     *         in="path",
     *         name="car_id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\MediaType(mediaType="application/json")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="error",
     *         @OA\MediaType(mediaType="application/json")
     *     ),
     *     security={ {"passport": {}} }
     * )
     */
    public function destroy($carId)
    {
        if (Auth::user()->role_id === 2) {
            $data = Car::with('carDescription')->where('id', $carId)->where('owner_id', Auth::id())->find($carId);
            if ($data) {
                $data->delete();
                return response()->json(['message' => 'Data deleted successfully!'], 200);
            } else {
                return response()->json(['message' => 'No data found!'], 404);
            }
        } else {
            return response()->json(['message' => 'Not authorized!'], 401);
        }
    }
}
