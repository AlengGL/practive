<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // search all
    public function getAll() {
        try {
            return response()->json(["data" => User::all()], 200);
        } catch (QueryException $e) {
            return response()->json(["error" => "Database error: " . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(["error" => "Server error: " . $e->getMessage()], 500);
        }
    }

    // search by person
    public function getByUsername(Request $request) {
        try {
            $username = $request->route("username");

            if($check = User::find($username)) {
                return  response()->json(["data" => User::find($username)], 200);
            }

            return response()->json(['message' => 'not found user'], 401);
        } catch (QueryException $e) {
            return response()->json(["error" => "Database error: " . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(["error" => "Server error: " . $e->getMessage()], 500);
        }
    }

    // update
    public function update(Request $request) {
        try {
            $user = User::where("username", $request->route("username"))->first();

            if($user) {
                $user->password = Hash::make($request->input('password'));
                $user->birthday = $request->input('birthday');
                $user->save();
                return response()->json(["message" => "User update successfully"], 200);
            }

            return response()->json(["error" => "User not found"], 404);
        } catch (QueryException $e) {
            return response()->json(["error" => "Database error: " . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(["error" => "Server error: " . $e->getMessage()], 500);
        }
    }

    // delete
    public function delete(Request $request) {
        try {
            $username = $request->route("username");
            $user = User::where("username", $username)->first();

            if ($user) {
                $user->delete();
                return response()->json(["message" => "User deleted successfully"], 200);
            }

            return response()->json(["error" => "User not found"], 404);
        } catch (QueryException $e) {
            return response()->json(["error" => "Database error: " . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(["error" => "Server error: " . $e->getMessage()], 500);
        }
    }
}
