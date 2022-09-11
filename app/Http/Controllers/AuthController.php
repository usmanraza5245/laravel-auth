<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(Request $request) {
        $validation = Validator::make($request->all(),[
            'name' => 'required|string|min:5|max:20|alpha',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|max:25'
        ]);
        if($validation->fails()){
            return $validation->errors()->toJson();
        }
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        // dd($request->all());
    }

    public function login(Request $request) {
        $validation = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|max:25'
        ]);
        if($validation->fails()){
            return $validation->errors()->toJson();
        }
        if(!Auth::attempt($request->only('email', 'password'))){
            return response([
                'message' => 'Invalid email or password'
            ], 401);
        }
        $user = Auth::user();

        $token = $user->createToken('token')->plainTextToken;

        $cookie = cookie('token', $token, 60*24);

        return response(['user' => $user])->withCookie($cookie);
    }

    public function user() {
        return Auth::user();
    }

    public function logout() {
        $cookie = Cookie::forget('token');

        return response([
            'message'=>'success'
        ])->withCookie($cookie);
    }
}
