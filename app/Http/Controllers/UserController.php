<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
    function login(Request $request)
    {
    	$user = User::where(['email'=>$request->email])->first();
    	if(empty($user) || !Hash::check($request->password, $user->password))
    	{
    		return "Invalid username or password";
    	}

    	$request->session()->put('user',$user);
    	return redirect('/');
    }

    function logout(Request $request)
    {
        $request->session()->forget('user');
        return redirect('/');
    }

    function register(Request $request)
    {
        //return $request;

        $user = new User;
        $user->name = $request->user_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        return redirect('/login');
    }
}
