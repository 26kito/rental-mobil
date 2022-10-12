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
        $page = request()->get("page", 1);
        $data = Cache::remember("get_all_car_page=" . $page, 60, function () {
            return DB::table("cars")
                ->join("car_descriptions AS cd", "cars.id", "cd.car_id")
                ->join("users", "cars.owner_id", "users.id")
                ->join("car_statuses AS cs", "cars.status_id", "cs.id")
                ->select("cars.id", "cars.brand_car", "users.name AS owner_name", "cs.status", "cd.capacity")
                ->where("cars.status_id", 1)
                ->simplePaginate(10);
        });
        try {
            if ($data->isNotEmpty()) {
                return $this->successResponse(200, "Success", $data);
            } else {
                return $this->failedResponse(404, "There's no data found!");
            }
        } catch (Exception $e) {
            return $this->failedResponse(400, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Check if user is an car_owner
        $firstValidated = Validator::make($request->only("brand_car"), [
            "brand_car" => "required"
        ]);
        // If validation is passes, then proceed to next process
        if ($firstValidated->passes()) {
            DB::beginTransaction();
            $insert = Car::create([
                "brand_car" => $request->brand_car,
                "owner_id" => Auth::id()
            ]);
            // If car created, then check the car description
            if ($insert) {
                $secondValidated = Validator::make($request->only("car_model_year", "color", "capacity", "no_plate"), [
                    "car_id" => "unique:car_descriptions",
                    "car_model_year" => "required|integer",
                    "color" => "alpha",
                    "capacity" => "integer|between:2,10",
                    "no_plate" => "unique:car_descriptions|min:5|max:12"
                ]);
                // If validation is passes, then proceed to next process
                if ($secondValidated->passes()) {
                    CarDescription::create([
                        "car_id" => $insert->id,
                        "car_model_year" => $request->car_model_year,
                        "color" => $request->color,
                        "capacity" => $request->capacity,
                        "no_plate" => strtoupper($request->no_plate),
                    ]);

                    DB::commit();
                    $data = Car::with("carDescription")->where("owner_id", Auth::id())->whereRelation("carDescription", "car_id", $insert->id)->first();
                    return $this->successResponse(201, "Data created successfully!", $data);
                    // If validation is failed, then throw error
                } else {
                    return $this->failedResponse(400, $secondValidated->errors());
                }
                // If car is failed to create then throw error
            } else {
                DB::rollback();
                return $this->failedResponse(400, "Failed to create!");
            }
            // If validation is failed, then throw error
        } else {
            return $this->failedResponse(400, $firstValidated->errors());
        }
    }

    public function show($carId)
    {
        $data = Cache::remember("show_car=" . $carId, 60, function () use ($carId) {
            return DB::table("cars")
                ->join("car_descriptions AS cd", "cars.id", "cd.car_id")
                ->join("users", "cars.owner_id", "users.id")
                ->where("cars.id", $carId)
                ->select("cars.brand_car", "cd.car_model_year", "cd.color", "cd.capacity", "cd.no_plate", "users.name AS car_owner", "users.mobile_phone", "users.email", "users.address")
                ->first();
        });
        if ($data != null) {
            return $this->successResponse(200, "Success", $data);
        } else {
            return $this->failedResponse(404, "There's no data found!");
        }
    }

    public function search($keyword)
    {
        // Check if input is greater than 3 char
        if (strlen($keyword) >= 3) {
            $data = DB::table("cars")
                ->join("car_descriptions AS cd", "cars.id", "cd.car_id")
                ->join("users", "cars.owner_id", "users.id")
                ->join("car_statuses AS cs", "cars.status_id", "cs.id")
                ->select("cars.id", "cars.brand_car", "users.name AS owner_name", "cs.status", "cd.capacity")
                ->where("cars.brand_car", "LIKE", "%" . $keyword . "%")
                ->get();

            try {
                if ($data->isNotEmpty()) {
                    return $this->successResponse(200, "Success", $data);
                } else {
                    return $this->failedResponse(404, "There's no data found!");
                }
            } catch (Exception $e) {
                return $this->failedResponse(400, $e->getMessage());
            }
        } else {
            return $this->failedResponse(400, "Please input 3 or more characters");
        }
    }

    public function carOwner()
    {
        $data = Cache::remember("car_owner_user=" . Auth::id(), 60, function () {
            return DB::table("cars")
                ->join("car_statuses AS cs", "cars.status_id", "cs.id")
                ->select("cars.id", "cars.brand_car", "cs.status")
                ->where("cars.owner_id", Auth::id())
                ->get();
        });

        if ($data->isNotEmpty()) {
            return $this->successResponse(200, "Success", $data);
        } else {
            return $this->failedResponse(404, "There's no data found!");
        }
    }

    public function updateCar(Request $request, $carId)
    {
        $car = Car::with("carDescription")->where("id", $carId)->where("owner_id", Auth::id())->find($carId);
        // Check if there is data
        if ($car) {
            DB::beginTransaction();
            $insert = $car->update(["brand_car" => $request->brand_car]);
            if ($insert) {
                $validated = Validator::make($request->only("car_model_year", "color", "capacity", "no_plate"), [
                    "car_model_year" => "integer",
                    "color" => "alpha",
                    "capacity" => "integer|between:2,10",
                    "no_plate" => "min:5|max:12",
                    Rule::unique("car_descriptions")->ignore($request->no_plate)
                ]);
                if ($validated) {
                    CarDescription::where("car_id", $carId)->update([
                        "car_model_year" => $request->car_model_year,
                        "color" => $request->color,
                        "capacity" => $request->capacity,
                        "no_plate" => strtoupper($request->no_plate),
                    ]);

                    DB::commit();
                    $data = Car::with("carDescription")->where("id", $carId)->where("owner_id", Auth::id())->first();
                    return $this->successResponse(200, "Data updated successfully", $data);
                }
            } else {
                DB::rollback();
                return $this->failedResponse(400, "Failed to update!");
            }
        } else {
            return $this->failedResponse(404, "There's no data found!");
        }
    }

    public function destroy($carId)
    {
        $data = Car::with("carDescription")
            ->where("id", $carId)
            ->where("owner_id", Auth::id())
            ->find($carId);

        if ($data) {
            $data->delete();
            return $this->successResponse(200, "Data deleted successfully!");
        } else {
            return $this->failedResponse(404, "There's no data found!");
        }
    }
}
