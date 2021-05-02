<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use Session;
use Illuminate\Support\Facades\DB;
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

	function removeCart(Request $request)
	{
		if(!$request->session()->has('user'))
		{
			return redirect('login');
		}
		if(Cart::destroy($request->id))
			return redirect('/cartlist');
		return "Error in deleting product from cart";
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

	function cartList()
	{
		$user_id = Session::has('user') ? Session::get('user')['id'] : 0;
		$product = DB::table('cart')
				   ->join('products','cart.product_id','=','products.id')
				   ->where('cart.user_id',$user_id)
				   ->select('products.*','cart.id as cart_id')
				   ->get();

		return view('cartlist',['products'=>$product]);
	}

	function orderNow()
	{
		$user_id = Session::has('user') ? Session::get('user')['id'] : 0;
		$sum = DB::table('cart')
				   ->join('products','cart.product_id','=','products.id')
				   ->where('cart.user_id',$user_id)
				   ->select('products.*','cart.id as cart_id')
				   ->sum('products.price');

		return view('ordernow',['total'=>$sum]);
	}

	function orderPlace(Request $request)
	{
		//return $request->input();
		$user_id = Session::has('user') ? Session::get('user')['id'] : 0;
		if(!$user_id){
			return redirect('login');			
		}
		
		$all_cart = Cart::where('user_id',$user_id)->get();
		foreach ($all_cart as $cart) 
		{
			$order = new Order();
			$order->product_id=$cart['product_id'];
			$order->user_id=$cart['user_id'];
			$order->status="pending";
			$order->payment_method=$request->payment;
			$order->payment_status="pending";
			$order->address=$request->address;
			$order->created_at=date('Y-m-d H:i:s');
			$order->updated_at=date('Y-m-d H:i:s');
			$order->save();

			Cart::where('user_id',$user_id)->delete();
		}

		$request->input();
        return redirect('/');

		/*$sum = DB::table('cart')
				   ->join('products','cart.product_id','=','products.id')
				   ->where('cart.user_id',$user_id)
				   ->select('products.*','cart.id as cart_id')
				   ->sum('products.price');

		return view('ordernow',['total'=>$sum]);*/
	}

	function myOrders()
	{
		$user_id = Session::has('user') ? Session::get('user')['id'] : 0;
		if(!$user_id){
			return redirect('login');			
		}
        $orders= DB::table('order')
         ->join('products','order.product_id','=','products.id')
         ->where('order.user_id',$user_id)
         ->get();
         return view('myorders',['orders'=>$orders]);
	}
}
