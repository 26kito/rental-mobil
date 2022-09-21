<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

  public function profile($user_id)
  {
    $data = User::where('id', $user_id)->first();
    if ($data) {
      return response()->json([
        'message' => 'Success',
        'data' => $data
      ], 200);
    } else {
      return response()->json(['message' => 'There\'s no data!']);
    }
  }

  public function register(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'name' => 'required|min:4|max:20|regex:/^[\pL\s\-]+$/u',
      'email' => 'required|email|min:8|max:20|unique:users',
      'address' => 'required',
      'mobile_phone' => 'required|min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users',
      'role_id' => 'required|exists:roles,id',
      'password' => 'required|min:8|confirmed'
    ]);

    try {
      // If validation success, then create data
      if ($validated->passes()) {
        User::create([
          'name' => $request->name,
          'email' => $request->email,
          'mobile_phone' => $request->mobile_phone,
          'address' => $request->address,
          'role_id' => $request->role_id,
          'password' => Hash::make($request->password)
        ]);

        return response()->json([
          'message' => 'Successfully created data',
        ], 201);
        // If validation error, throw message
      } else {
        return response()->json(['message' => $validated->errors()]);
      }
    } catch (Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function login(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'email' => 'required|email|exists:users,email',
      'password' => 'required|min:8'
    ]);

    try {
      // If validation success, then create data
      if ($validated->passes()) {
        $user = User::where('email', $request->email)->first();
        // If user email and password is match
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password]) && Hash::check($request->password, $user->password)) {
          $accessToken = $user->createToken('authToken')->accessToken;
          return response()->json([
            'message' => 'Success',
            'data' => $user,
            'access_token' => $accessToken
          ], 200);
          // User email and password is no match
        } else {
          return response()->json([
            'message' => "Email & password does not match"
          ], 200);
        }
        // When the validation is error
      } else {
        return response()->json([
          'message' => $validated->errors()
        ], 200);
      }
    } catch (Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ]);
    }
  }

  public function update(Request $request, $id)
  {
    // Check if user id is same from the param and user id = id
    $user = User::where('id', $id)->where('id', Auth::id())->first();
    if ($user) {
      try {
        // Validate for the input
        $validated = Validator::make($request->all(), [
          'name' => 'max:20',
          'email' => 'email|min:10',
          Rule::unique('users')->ignore($user->email),
          'mobile_phone' => 'min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
          Rule::unique('users')->ignore($user->mobile_phone),
        ]);

        // If validation passes, then update data
        if ($validated->passes()) {
          $user->update($request->all());
          return response()->json([
            'message' => 'User updated successfully!',
            'data' => $user
          ], 200);
        } else {
          return response()->json(['message' => $validated->errors()]);
        }
      } catch (Exception $e) {
        return response()->json(['message' => $e->getMessage()]);
      }
      // If user is not same, can't update
    } else {
      return response()->json(['message' => 'There\'s no data!']);
    }
  }
}
