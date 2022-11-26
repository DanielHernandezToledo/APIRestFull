<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Product;
use App\Seller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{

    public function index(Seller $seller)
    {
        $products = $seller->products;

        return $this->showAll($products);
    }

    public function store(Request $request, User $seller)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $this->validate($request,$rules);

        $data = $request->all();

        $data['status'] = Product::PRODUCTO_NO_DISPONIBLE;
        $data['image'] = $request->image->store(''); //ubicacion y sistema de archivos a usar
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in: ' .Product::PRODUCTO_NO_DISPONIBLE.','.Product::PRODUCTO_DISPONIBLE,
            'image' => 'image',
        ];

        $this->validate($request, $rules);

        
        $this->verificarVendedor($seller, $product);

        $product->fill($request->only([
            'name',
            'description',
            'quantity'
        ]));
        
        if($request->has('status')){
            $product->status = $request->status;

            if($product->estaDisponible() && $product->categories()->count() == 0){
                return $this->errorResponse('Un producto activo debe teer al menos una categoría', 409);
            }
        }


        if($request->hasFile('image')){
            Storage::delete($product->image);
            $product->image = $request->image->store("");
        }

        if($product->isClean()){
            return $this->errorResponse('Se debe indicar por lo menos un valor diferente para actulizar', 422);
        }

        $product->save();
        return $this->showOne($product);
    }

    public function destroy(Seller $seller, Product $product)
    {
        $this->verificarVendedor($seller, $product);

        Storage ::delete($product->image);

        $product->delete();
        return $this->showOne($product);
    }

    protected function verificarVendedor(Seller $seller, Product $product)
    {
        if($seller->id != $product->seller_id){
            throw new HttpException(422, 'El vendedor especificado no es el vendedor real del producto');
        }
    }


}
