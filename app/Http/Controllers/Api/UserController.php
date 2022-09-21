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
use Laravel\Passport\HasApiTokens;

class UserController extends Controller
{
  use HasApiTokens;

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $user = User::get();
    try {
      if ($user->isNotEmpty()) {

        return response()->json([
          'message' => 'Success',
          'data' => $user
        ], 200);
      }

      return response()->json([
        'message' => 'There\'s no data'
      ], 200);
    } catch (Exception $e) {

      return response()->json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function profile($name)
  {
    $user = User::with('role')->where('name', $name)->get();

    return response()->json([
      'message' => 'Profile user',
      'data' => $user
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function register(Request $request)
  {
    $validation = Validator::make($request->all(), [
      'email' => 'required|unique:users|email',
      'mobile_phone' => 'required|unique:users|min:11|max:15'
    ]);

    if ($validation->fails()) {
      return response()->json([
        'message' => $validation->messages()
      ], 403);
    }

    try {
      User::create([
        'name' => $request->name,
        'email' => $request->email,
        'mobile_phone' => $request->mobile_phone,
        'address' => $request->address,
        'role_id' => $request->role_id,
        'password' => Hash::make($request->password)
      ]);

      return response()->json([
        'message' => 'User berhasil dibuat, kamu bisa Login sekarang',
      ], 201);
    } catch (Exception $e) {

      return response()->json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  /**
   * Function for user login
   */
  public function login(Request $request)
  {
    try {
      $user = User::where('email', $request->email)->first();

      if (Auth::attempt($request) && Hash::check($request->password, $user->password)) {

        $accessToken = $user->createToken('authToken')->accessToken;
        return response()->json([
          'access_token' => $accessToken
        ], 200);
      }

      return response()->json([
        'message' => 'Email dan password tidak sesuai'
      ], 422);
    } catch (Exception $e) {

      return response()->json([
        'message' => $e->getMessage()
      ]);
    }
  }
  /**
   * Function for update specified user.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, User $user)
  {
    try {
      if ($user) {
        $validated = $request->validate([
          'name' => 'max:20',
          'email' => 'email|min:10',
          Rule::unique('users')->ignore($user->email, 'email'),
          'mobile_phone' => 'min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
          Rule::unique('users')->ignore($user->mobile_phone),
        ]);

        $data = $request->all();

        if ($validated) {
          $user->update($data);
          return response()->json([
            'message' => 'User updated successfully!',
            'data' => $user
          ], 200);
        }
      }
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Function for delete specified user.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy(User $user)
  {
    try {
      $user->delete();
      return response()->json([
        'message' => 'User deleted successfully'
      ], 200);
    } catch (Exception $e) {
      throw $e;
    }
  }
}
