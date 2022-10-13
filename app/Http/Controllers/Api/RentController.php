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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class RentController extends Controller
{
    public function rentCar(Request $request, $carId)
    {
        // Check if customer rent is accepted
        $data = DB::table("rents")
            ->join("users", "rents.customer_id", "users.id")
            ->select("rents.rent_status")
            ->where("rents.customer_id", Auth::id())
            ->where("users.role_id", 1)
            ->whereNotIn("rents.rent_status", [1, 3])
            ->first();

        try {
            // If customer have already rent and accepted, then they can't rent another car
            if ($data) {
                return $this->failedResponse(400, "Can't rent another car!");
            } else {
                $car = Car::where("id", $carId)->first();
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
                        return $this->successResponse(201, "Success", $data);
                        // If data is not validated, then throw error
                    } else {
                        return $this->failedResponse(400, $validated->errors());
                    }
                } else {
                    return $this->failedResponse(404, "There's no data found!");
                }
            }
        } catch (Exception $e) {
            return $this->failedResponse(400, $e->getMessage());
        }
    }

    public function history()
    {
        $data = Cache::remember("rent_history_user=" . Auth::id(), 60, function () {
            return DB::table("rents")
                ->join("cars", "rents.car_id", "cars.id")
                ->join("users", "cars.owner_id", "users.id")
                ->join("rent_statuses", "rents.rent_status", "rent_statuses.id")
                ->select("rents.id", "cars.brand_car", "users.name", "rent_statuses.status")
                ->where("rents.customer_id", Auth::id())
                ->get();
        });

        if ($data->isNotEmpty()) {
            return $this->successResponse(200, "Success", $data);
        } else {
            return $this->failedResponse(404, "There's no data found!");
        }
    }

    public function owner()
    {
        $data = DB::table("rents")
            ->join("users", "customer_id", "users.id")
            ->join("cars", "car_id", "cars.id")
            ->select("users.id AS customer_id", "users.name", "cars.id AS car_id", "cars.brand_car", "rents.rent_date", "rents.return_date")
            ->where("cars.owner_id", Auth::id())
            ->where("rent_status", 1)
            ->get();

        if ($data->isNotEmpty()) {
            return $this->successResponse(200, "Success", $data);
        } else {
            return $this->failedResponse(404, "There's no data found!");
        }
    }

    public function approval(Request $request, $customer_id)
    {
        $validated = Validator::make($request->only("approval_status", "car_id"), [
            "approval_status" => "required|boolean",
            "car_id" => "required|integer|exists:rents,car_id"
        ]);

        if ($validated->passes()) {
            $data = DB::table("rents")
                ->join("users", "customer_id", "users.id")
                ->join("cars", "car_id", "cars.id")
                ->select("users.id", "users.name", "cars.brand_car", "rents.rent_date", "rents.return_date", "rents.rent_status")
                ->where("rents.customer_id", $customer_id)
                ->where("rents.car_id", $request->car_id)
                ->where("rents.rent_status", 1)
                ->where("cars.owner_id", Auth::id())
                ->where("cars.status_id", 1)
                ->first();

            if ($data !== null) {
                $base_query = DB::table("rents")
                    ->join("cars", "rents.car_id", "cars.id")
                    ->where("cars.owner_id", Auth::id())
                    ->where("rents.customer_id", $customer_id)
                    ->where("rents.car_id", $request->car_id);
                if ($request->approval_status) {
                    $acceptRent = $base_query->update(["rent_status" => 2]);
                    if ($acceptRent) {
                        DB::table("cars")
                            ->join("rents", "car_id", "cars.id")
                            ->where("rents.customer_id", $customer_id)
                            ->update(["cars.status_id" => 2]);
                        return $this->successResponse(200, "Success");
                    }
                } else {
                    $base_query->update(["rent_status" => 3]);
                    return $this->successResponse(200, "Success");
                }
            } else {
                return $this->successResponse(404, "There's no data found!");
            }
        } else {
            return $this->failedResponse(400, $validated->errors());
        }
    }
}
