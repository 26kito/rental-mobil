<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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
      } else {
        return response()->json([
          'message' => 'There\'s no data'
        ], 200);
      }
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
    $validated = $request->validate([
      'name' => 'required|min:4|max:20|alpha',
      'email' => 'required|email|unique:users',
      'mobile_phone' => 'required|min:11|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users',
      'address' => 'required',
      'role_id' => 'required|exists:roles,id',
      'password' => 'required|min:8|confirmed'
    ]);

    try {
      if ($validated) {
        $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'mobile_phone' => $request->mobile_phone,
          'address' => $request->address,
          'role_id' => $request->role_id,
          'password' => Hash::make($request->password)
        ]);
      }

      $accessToken = $user->createToken('authToken')->accessToken;
      return response()->json([
        'message' => 'User Created Successfully!',
        'user' => $user,
        'access_token' => $accessToken
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
    $data = $request->validate([
      'email' => 'required|email|exists:users,email',
      'password' => 'required'
    ]);

    try {
      $user = User::where('email', $request->email)->first();
      if (Auth::attempt($data) && Hash::check($request->password, $user->password)) {
        $accessToken = $user->createToken('authToken')->accessToken;
        return response()->json([
          'message' => 'Success',
          'data' => $user,
          'access_token' => $accessToken
        ], 200);
      } else {
        return response()->json([
          'message' => 'Wrong Password!'
        ], 422);
      }
    } catch (Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ]);
    }
  }

  /**
   * Show specified user by id.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */

  // public function show($id)
  // {
  //   $user = User::find($id);
  //   try {
  //     if ($user) {
  //       return response()->json([
  //         'message' => 'Success',
  //         'data' => $user
  //       ], 200);
  //     } else {
  //       return response()->json([
  //         'message' => 'User not found'
  //       ], 200);
  //     }
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'message' => $e->getMessage()
  //     ], 400);
  //   }
  // }

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
