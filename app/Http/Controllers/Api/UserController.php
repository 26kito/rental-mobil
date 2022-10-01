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
  /**
   * @OA\Post(
   *     path="/api/v1/user/register",
   *     tags={"user"},
   *     summary="Register user",
   *     operationId="register",
   *     @OA\RequestBody(
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema(
   *                 @OA\Property(
   *                      type="object",
   *                      @OA\Property(
   *                          property="name",
   *                          type="string"
   *                      ),
   *                      @OA\Property(
   *                          property="email",
   *                          type="email"
   *                      ),
   *                      @OA\Property(
   *                          property="address",
   *                          type="string"
   *                      ),
   *                      @OA\Property(
   *                          property="mobile_phone",
   *                          type="integer"
   *                      ),
   *                      @OA\Property(
   *                          property="role_id",
   *                          type="integer"
   *                      ),
   *                      @OA\Property(
   *                          property="password",
   *                          type="string"
   *                      ),
   *                      @OA\Property(
   *                          property="password_confirmation",
   *                          type="string"
   *                      )
   *                 ),
   *                 example={
   *                     "name":"John Doe",
   *                     "email":"johndoe@example.com",
   *                     "address":"Valid address",
   *                     "mobile_phone":"0818512938251",
   *                     "role_id":"1",
   *                     "password":"pass",
   *                     "password_confirmation":"pass"
   *                }
   *             )
   *         )
   *      ),
   *      @OA\Response(
   *          response=201,
   *          description="data created"
   *      ),
   *      @OA\Response(
   *          response=400,
   *          description="error"
   *      ),
   * )
   */
  public function register(Request $request)
  {
    $validated = Validator::make($request->only('name', 'email', 'address', 'mobile_phone', 'role_id', 'password', 'password_confirmation'), [
      'name' => 'required|min:4|max:20|regex:/^[\pL\s\-]+$/u',
      'email' => 'required|email|min:8|max:25|unique:users',
      'address' => 'required',
      'mobile_phone' => 'required|min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users',
      'role_id' => 'required|exists:roles,id',
      'password' => 'required|min:8|confirmed'
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

  /**
   * @OA\Post(
   *     path="/api/v1/user/login",
   *     tags={"user"},
   *     summary="Login user",
   *     operationId="login",
   *     @OA\RequestBody(
   *         @OA\MediaType(
   *             mediaType="application/json",
   *             @OA\Schema(
   *                 @OA\Property(
   *                      type="object",
   *                      @OA\Property(
   *                          property="email",
   *                          type="email"
   *                      ),
   *                      @OA\Property(
   *                          property="password",
   *                          type="string"
   *                      )
   *                 ),
   *                 example={
   *                     "email":"johndoe@example.com",
   *                     "password":"12345678"
   *                }
   *             )
   *         )
   *      ),
   *      @OA\Response(
   *          response=200,
   *          description="success"
   *      ),
   *      @OA\Response(
   *          response=400,
   *          description="error"
   *      ),
   * )
   */
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
          'message' => 'Success',
          'data' => $user,
          'access_token' => $accessToken
        ], 200);
        // User email and password is no match
      } else {
        return response()->json([
          'message' => "Email & password does not match"
        ], 400);
      }
      // When the validation is error
    } else {
      return response()->json([
        'message' => $validated->errors()
      ], 400);
    }
  }

  /**
   * @OA\Get(
   *     path="/api/v1/user/profile/{user_id}",
   *     tags={"user"},
   *     summary="Get user by id",
   *     operationId="profile",
   *     @OA\Parameter(
   *         @OA\MediaType(
   *             mediaType="application/json",
   *         ),
   *         in="path",
   *         name="user_id",
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
   *         description="Failed"
   *     ),
   *     security={ {"passport": {}} }
   * )
   */
  public function profile()
  {
    $data = User::where('id', Auth::id())->find(Auth::id());
    if ($data) {
      return response()->json([
        'message' => 'Success',
        'data' => $data
      ], 200);
    } else {
      return response()->json(['message' => 'There\'s no data!'], 404);
    }
  }

  /**
   * @OA\Put(
   *     path="/api/v1/user/edit/{user_id}",
   *     tags={"user"},
   *     summary="Edit user",
   *     operationId="updateUser",
   *     @OA\Parameter(
   *         in="path",
   *         name="user_id",
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
   *                          property="name",
   *                          type="string"
   *                      ),
   *                      @OA\Property(
   *                          property="email",
   *                          type="email"
   *                      ),
   *                      @OA\Property(
   *                          property="address",
   *                          type="string"
   *                      ),
   *                      @OA\Property(
   *                          property="mobile_phone",
   *                          type="integer"
   *                      ),
   *                      @OA\Property(
   *                          property="role_id",
   *                          type="integer"
   *                      )
   *                 ),
   *                 example={
   *                     "name":"John Doe",
   *                     "email":"johndoe@example.com",
   *                     "address":"USA",
   *                     "mobile_phone":"0818512938251",
   *                     "role_id":"1"
   *                }
   *             )
   *         )
   *      ),
   *      @OA\Response(
   *          response=200,
   *          description="success"
   *      ),
   *      @OA\Response(
   *          response=400,
   *          description="error"
   *      ),
   *     security={ {"passport": {}} }
   * )
   */
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
            'message' => 'User updated successfully!',
            'data' => $user
          ], 200);
        } else {
          return response()->json(['message' => $validated->errors()], 400);
        }
      } catch (Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
      }
      // If user is not same, can't update
    } else {
      return response()->json(['message' => 'There\'s no data!'], 404);
    }
  }

  /**
   * @OA\Post(
   *     path="/api/v1/user/logout",
   *     tags={"user"},
   *     summary="Logout user",
   *     operationId="logout",
   *      @OA\Response(
   *          response=200,
   *          description="success",
   *          @OA\MediaType(mediaType="application/json")
   *      ),
   *      @OA\Response(
   *          response=400,
   *          description="error"
   *      ),
   *     security={ {"passport": {}} }
   * )
   */
  public function logout()
  {
    Auth::user()->token()->revoke();
    Auth::user()->token()->delete();
    return response()->json(['message' => 'User successfully logged out'], 200);
  }
}
