<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Session;
class ProductController extends Controller
{
	function index()
	{
		$products = Product::all();
		return view("product", ['products'=>$products]);
	}

	function detail($id)
	{
		$product = Product::find($id);
		return view("detail", ['product'=>$product]);
	}

	function addToCart(Request $request)
	{
		if(!$request->session()->has('user'))
		{
			return redirect('login');
		}
		$cart = new Cart();
		$cart->user_id = $request->session()->get('user')['id'];
		$cart->product_id = $request->product_id;
		if($cart->save())
			return redirect('/');
		return "Error in save product in cart";
	}

	function search(Request $request)
	{
		$data= Product::where('name', 'like', '%'.$request->input('query').'%')->get();
        return view('search',['products'=>$data]);
	}

	static function getCartItem()
	{
		if(!Session::has('user'))
		{
			return 0;
		}
		$user_id = Session::get('user')['id'];
		return Cart::where('user_id',$user_id)->count();
	}
}
