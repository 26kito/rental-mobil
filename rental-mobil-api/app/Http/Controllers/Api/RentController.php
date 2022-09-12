<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Car;
use App\Models\Rent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request, $carId)
    {
        $car = Car::where('id', $carId)->first();

        if ( Auth::user()->role_id === 1 && $car !== null ) {
            try {
                // return $request->rent_date;
                $validated = $request->validate([
                    'customer_id' => 'exists:users,id',
                    'car_id' => 'exists:cars,id',
                    'rent_date' => 'required|date_format:d-m-Y|after_or_equal:today',
                    'return_date' => 'required|date_format:d-m-Y|after_or_equal:rent_date'
                ]);
    
                if ( $validated ) {
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
                } else {
                    return response()->json([
                        'message' => 'Failed'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'message' => 'failed'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
