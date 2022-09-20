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

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Car::with('carDescription')->get();
        try {
            if ( $data->isNotEmpty() ) {
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Check if user is an car_owner
            if ( Auth::user()->role_id === 2 ) {
                $firstValidated = $request->validate([
                    'brand_car' => 'required',
                ]);
                
                if ( $firstValidated ) {
                    DB::beginTransaction();
                    try {
                        $insert = Car::create([
                            'brand_car' => $request->brand_car,
                            'owner_id' => Auth::id()
                        ]);
                        if ( $insert ) {
                            $secondValidated = $request->validate([
                                'car_id' => 'unique:car_descriptions',
                                'car_model_year' => 'required|integer',
                                'color' => 'alpha',
                                'capacity' => 'integer',
                                'no_plate' => 'unique:car_descriptions|min:5'
                            ]);
                            if ( $secondValidated ) {
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
                            }
                        } else {
                            DB::rollback();
                            return response()->json(['message' => 'Gagal'], 200);
                        }
                    } catch (Exception $e) {
                        DB::rollback();
                        return response()->json(['message' => $e->getMessage()], 400);
                    }
                }
            // If user is not an car owner, 
            } else {
                return response()->json(['message' => 'Not authorized'], 403);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($carId)
    {
        $data = Car::with('carDescription')->find($carId);
        if ( $data != null ) {
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $carId)
    {
        $car = Car::with('carDescription')->find($carId);
        if ( $car ) {
            try {
                if ( Auth::user()->role_id == 2 ) {
                    DB::beginTransaction();

                    try {
                        $car->update([
                            'brand_car' => $request->brand_car
                        ]);
                        if ( $car ) {
                            $secondValidated = $request->validate([
                                'car_model_year' => 'integer',
                                'color' => 'alpha',
                                'capacity' => 'integer',
                                Rule::unique('car_descriptions')->ignore($request->no_plate)
                            ]);
                            if ( $secondValidated ) {
                                CarDescription::where('car_id', $carId)
                                                ->update([
                                    'car_model_year' => $request->car_model_year,
                                    'color' => $request->color,
                                    'capacity' => $request->capacity,
                                    'no_plate' => $request->no_plate,
                                ]);

                                DB::commit();
                                $data = Car::with('carDescription')->first();
                                return response()->json([
                                    'message' => 'Data updated successfully!',
                                    'data' => $data
                                ], 201);
                            }
                        } else {
                            DB::rollback();
                            return response()->json([
                                'message' => 'Gagal'
                            ], 200);
                        }
                    } catch (Exception $e) {
                        DB::rollback();
                        return response()->json([
                            'message' => $e->getMessage()
                        ], 200);
                    }
                } else {
                    return 'anak anj';
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            return 'gaada';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($carId)
    {
        if ( Auth::user()->role_id === 2 ) {
            try {
                $data = Car::with('carDescription')->where('owner_id', Auth::id())->find($carId);
                if ( $data ) {
                    $data->delete();
                    return response()->json([
                        'message' => 'Data deleted successfully!'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'No data found!'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
