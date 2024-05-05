<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data['orders'] = DB::table('orders')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->select(["orders.*", DB::raw("customers.first_name || ' ' || customers.last_name as customer_name ")])
                ->get();

            return $this->sendResponse("List fetched successfully", $data, 200);
        }catch(Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|numeric|min:1',
                'products.*.discount' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return $this->sendError("Please enter valid input data", $validator->errors(), 404);
            }

            $postData = $validator->validated();

            DB::beginTransaction();

            // Create Order
            $data['order'] = Order::create([
                'customer_id' => $postData['customer_id'],
                'order_number' => "POS".uniqid(),
            ]);

            // Calculate Price
            $data['order']->price = 0;

            foreach($postData['products'] as $product) {
                $productData = Product::find($product['product_id']);

                if (empty($productData)) {
                    return $this->sendError("Product not found", ["errors" => [
                        "generic" => "Product not found"
                    ]], 404);
                }

                // Check if stock is available
                if ($productData->stock < $product['quantity']) {
                    return $this->sendError($productData->name . " is out of stock",  ["errors" => [
                        "generic" => "Out of stock"
                    ]], 400);
                }

                // Create the Item
                ProductOrder::create([
                    'order_id' => $data['order']->id,
                    'product_id' => $productData->id,
                    'product_name' => $productData->name,
                    'product_price' => $productData->price,
                    'product_quantity' => $product['quantity'],
                    'product_discount' => $product['discount'],
                ]);

                // Update the Product's stock
                $productData->stock = ($productData->stock - $product['quantity']);
                $productData->save();

                // Calculate the price and update the Order's price
                $calculatePrice = $productData->price - ($productData->price * $product['discount'] / 100);
                $data['order']->price += floatval($calculatePrice * $product['quantity']);

                // Update the Order's quantity
                $data['order']->quantity += intval($product['quantity']);
            }

            $data['order']->save();
            DB::commit();

            return $this->sendResponse("Order created successfully", $data, 201);
        }catch(Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data['order'] = Order::with(['items', 'customer'])->find($id);
            return $this->sendResponse("Order fetched successfully", $data, 200);
        }catch(Exception $e) {
            return $this->handleException($e);
        }
    }
}
