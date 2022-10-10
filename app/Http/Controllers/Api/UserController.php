<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\RateLimiter;

class UserController extends Controller
{
  public function register(Request $request)
  {
    $validated = Validator::make($request->only('name', 'email', 'address', 'mobile_phone', 'role_id', 'password', 'password_confirmation'), [
      'name' => 'required|min:4|max:20|regex:/^[\pL\s\-]+$/u',
      'email' => 'required|email|min:8|max:25|unique:users',
      'address' => 'required',
      'mobile_phone' => 'required|min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users',
      'role_id' => 'required|exists:roles,id',
      'password' => 'required|min:8|regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{8,}$/|confirmed'
    ]);

    try {
      // If validation success, then create data
      if ($validated->passes()) {
        User::create([
          'name' => ucwords($request->name),
          'email' => strtolower($request->email),
          'mobile_phone' => $request->mobile_phone,
          'address' => $request->address,
          'role_id' => $request->role_id,
          'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Successfully register'], 201);
        // If validation error, throw message
      } else {
        return response()->json(['message' => $validated->errors()], 400);
      }
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  public function login(Request $request)
  {
    $validated = Validator::make($request->only('email', 'password'), [
      'email' => 'required|email|exists:users,email',
      'password' => 'required|min:8'
    ]);

    // If validation success, then create data
    if ($validated->passes()) {
      $user = User::where('email', $request->email)->first();
      // If user email and password is match
      if (Auth::attempt(['email' => $request->email, 'password' => $request->password]) && Hash::check($request->password, $user->password)) {
        $accessToken = $user->createToken('authToken')->accessToken;
        return response()->json([
          'success' => true,
          'message' => 'Successfully login! Redirecting to Home Page',
          'data' => $user,
          'access_token' => $accessToken
        ], 301);
        // User email and password is no match
      } else {
        return response()->json(['success' => false, 'message' => "Email & password does not match"], 400);
      }
      // When the validation is failed then throw error
    } else {
      return response()->json(['success' => false, 'message' => $validated->errors()], 400);
    }
  }

  public function profile()
  {
    if (Auth::check()) {
      return response()->json([
        'success' => true,
        'message' => 'Success',
        'data' => Auth::user()
      ], 200);
    }
  }

  public function updateUser(Request $request, $id)
  {
    // Check if user id is same from the param and user id = id
    $user = User::where('id', $id)->where('id', Auth::id())->find($id);
    if ($user) {
      try {
        // Validate for the input
        $validated = Validator::make($request->only('name', 'email', 'mobile_phone'), [
          'name' => 'max:20',
          'email' => 'email|min:10',
          Rule::unique('users')->ignore($user->email),
          'mobile_phone' => 'min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
          Rule::unique('users')->ignore($user->mobile_phone),
        ]);

        // If validation passes, then update data
        if ($validated->passes()) {
          $user->update($request->only('name', 'email', 'mobile_phone'));
          return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'data' => $user
          ], 200);
        } else {
          return response()->json(['success' => false, 'message' => $validated->errors()], 400);
        }
      } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
      }
      // If user is not same, can't update
    } else {
      return response()->json(['success' => false, 'message' => 'There\'s no data found!'], 404);
    }
  }

  public function logout()
  {
    Auth::user()->token()->revoke();
    Auth::user()->token()->delete();
    return response()->json(['success' => false, 'message' => 'User successfully logged out'], 200);
  }
}
