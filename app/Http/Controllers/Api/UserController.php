<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
  public function register(Request $request)
  {
    $validated = Validator::make($request->only("name", "email", "address", "mobile_phone", 'role_id', 'password', 'password_confirmation'), [
      "name" => "required|min:4|max:20|regex:/^[\pL\s\-]+$/u",
      "email" => "required|email|min:8|max:25|unique:users",
      "address" => "required",
      "mobile_phone" => "required|min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users",
      "role_id" => "required|exists:roles,id",
      "password" => "required|min:8|regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{8,}$/|confirmed"
    ]);

    try {
      // If validation success, then create data
      if ($validated->passes()) {
        User::create([
          "name" => ucwords($request->name),
          "email" => strtolower($request->email),
          "mobile_phone" => $request->mobile_phone,
          "address" => $request->address,
          "role_id" => $request->role_id,
          "password" => Hash::make($request->password)
        ]);

        return $this->successResponse(201, "Successfully register!");
        // If validation error, throw message
      } else {
        return $this->failedResponse(400, $validated->errors());
      }
    } catch (Exception $e) {
      return $this->failedResponse(400, $e->getMessage());
    }
  }

  public function login(Request $request)
  {
    $validated = Validator::make($request->only("email", "password"), [
      "email" => "required|email|exists:users,email",
      "password" => "required|min:8"
    ]);

    // If validation success, then create data
    if ($validated->passes()) {
      $user = User::where("email", $request->email)->first();
      // If user email and password is match
      if (Auth::attempt(["email" => $request->email, "password" => $request->password]) && Hash::check($request->password, $user->password)) {
        $accessToken = $user->createToken("authToken")->accessToken;
        $user->accessToken = $accessToken;
        return $this->successResponse(301, "Successfully login! Redirecting to Home Page", $user);
        // User email and password is no match
      } else {
        return $this->failedResponse(400, "Email & password does not match");
      }
      // When the validation is failed then throw error
    } else {
      return $this->failedResponse(400, $validated->errors());
    }
  }

  public function profile()
  {
    if (Auth::check()) return $this->successResponse(200, "Success", Auth::user());
  }

  public function updateUser(Request $request, $id)
  {
    // Check if user id is same from the param and user id = id
    $user = User::where("id", $id)->where("id", Auth::id())->find($id);
    if ($user) {
      try {
        // Validate for the input
        $validated = Validator::make($request->only("name", "email", "mobile_phone"), [
          "name" => "max:20",
          "email" => "email|min:10",
          Rule::unique("users")->ignore($user->email),
          "mobile_phone" => "min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/",
          Rule::unique("users")->ignore($user->mobile_phone),
        ]);

        // If validation passes, then update data
        if ($validated->passes()) {
          $user->update($request->only("name", "email", "mobile_phone"));
          return $this->successResponse(200, "User updated successfully!", $user);
        } else {
          return $this->failedResponse(400, $validated->errors());
        }
      } catch (Exception $e) {
        return $this->failedResponse(400, $e->getMessage());
      }
      // If user is not same, can't update
    } else {
      return $this->failedResponse(404, "There's no data found!");
    }
  }

  public function logout()
  {
    Auth::user()->token()->revoke();
    Auth::user()->token()->delete();
    return $this->successResponse(200, "User successfully logged out");
  }
}
