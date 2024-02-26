<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function show()
    {
        $products = Product::paginate(3);
        return view('show', compact('products'));
    }

    
    public function index()
    {

        $products = Product::paginate(3);
        return view('product', compact('products'));


        // return response()->json([
        //     'message' => 'รายการสินค้าทั้งหมด',
        //     'data' => $products
        // ], 200);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'stock'=>'required'
        ],
        
        [
            'product_name.required' => '**กรุณากรอกชื่อสินค้า',
            'description.required' => '**กรุณากรอกรายละเอียดสินค้า',
            'price.required' => '**กรุณากรอกราคาสินค้า',
            'stock.required' => '**กรุณากรอกจำนวนสินค้า'

        ]
    
        );

        $latestProduct = Product::orderBy('id', 'desc')->first();
        $id = $latestProduct ? $latestProduct->id + 1 : 1;

        Product::insert([
            'id' => $id,
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'created_at' => now()
        ]);

        
        return redirect()->route('product');


    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::where('id', $id)->first();
    
        return view('edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'product_name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'stock'=>'required'
        ],
        
        [
            'product_name.required' => '**กรุณากรอกชื่อสินค้า',
            'description.required' => '**กรุณากรอกรายละเอียดสินค้า',
            'price.required' => '**กรุณากรอกราคาสินค้า',
            'stock.required' => '**กรุณากรอกจำนวนสินค้า'
        ]
        );
    
        Product::where('id', $id)->update([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'updated_at' => now()
        ]);
    
        return redirect()->route('product');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::table('products')->where('product_id', $id)->delete();
    }


    //CartController 
    public function addcart($id)
    {
        $product = Product::findOrfail($id);
        $cart = session()->get('cart', []);
        if(isset($cart[$id])){
            $cart[$id]['stock']++;
        }else{
            $cart[$id] = [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'stock' => 1,
                'description' => $product->description,
                'price' => $product->price,
            ];
        }
        session()->put('cart',$cart);
        return redirect()->route('cart')->with('success','เพิ่มสินค้าลงในตะกร้าเรียบร้อย');
    }

    public function cart()
    {
        $cart = session()->get('cart');
        $cartQuantityMatches = $this->checkCartQuantity();
        return view('cart', compact('cart', 'cartQuantityMatches'));
    }

    public function deletecart($id)
    {
        $cart = session()->get('cart');
        if(isset($cart[$id])){
            unset($cart[$id]);
        }
        session()->put('cart',$cart);
        return redirect()->route('cart');
    }
    
    public function updatecart(Request $request)
    {
        $id = $request->id;
        $stock = $request->stock;
    
        $cart = session()->get('cart');
        if (isset($cart[$id])) {
            $cart[$id]['stock'] = $stock;
        }
        session()->put('cart', $cart);
    
        return redirect()->route('cart');
    }
    
    
    public function checkCartQuantity()
    {
        $cart = session()->get('cart');
        $cartQuantityMatches = true; // เพิ่มตัวแปรเพื่อตรวจสอบจำนวนสินค้าในตะกร้า
    
        foreach ($cart as $id => $details) {
            $product = Product::find($id);
            
            if (!$product || $details['stock'] > $product->stock) {
                $cartQuantityMatches = false; // มีจำนวนสินค้าในตะกร้ามากกว่าในฐานข้อมูลหรือสินค้าไม่มีอยู่ในฐานข้อมูล
                // เพิ่มการแสดงข้อความเตือน
                session()->flash('cartQuantityMatches', false);
                session()->flash('cartMessages.'.$id, [
                    'product_id' => $id,
                    'product_name' => $details['product_name'],
                    'remaining_stock' => $product ? $product->stock : 0 // จำนวนสินค้าที่เหลือในฐานข้อมูล (ถ้ามีสินค้า)
                ]);
            }
        }
    
        return $cartQuantityMatches;
    }
    


}
