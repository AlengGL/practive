<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // login
    public function login()
    {
        $credentials = request(['username', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['access_token' => $token], 200);
    }

    // logout
    public function logout()
    {
        if(auth()->check()) {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out'], 200);
        }

        return response()->json(['error' => 'Fail logged out'], 401);
    }

    // Register
    public function register(Request $request) {
        try{
            $check = User::find($request->input('username'));

            if($check){
                return response()->json(['error'=> 'User with this username already exists.'], 403);
            } else {
                $user = [
                    "username" => $request->input('username'),
                    "password" => Hash::make($request->input('password')),
                    "birthday" => $request->input('birthday'),
                    "last_login" => null,
                ];
                User::create($user);

                return response()->json(["message" => "User Register successfully"], 200);
            }
        } catch (QueryException $e) {
            return response()->json(["error" => "Database error: " . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(["error" => "Server error: " . $e->getMessage()], 500);
        }
    }

    public function userProfile() {
        try{
            $user = auth()->user();
            if (!$user) {
                return response()->json(["error" => "you not login"], 403);
            }
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(["error" => "Server error: " . $e->getMessage()], 500);
        }
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
