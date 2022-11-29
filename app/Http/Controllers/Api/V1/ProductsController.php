<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductResource::collection(Product::with('productImages')->paginate(20));
    }

    public function allAvailable()
    {
        return ProductResource::collection(Product::where('is_available', '1')->with('productImages')->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $name_desc_translation = [];
        if ($request->name_translation || $request->description_translation) {
            //
            // foreach $key is en / ar / fr
            // (array)$request->name  if $request->name is null, then it converts it to emty array
            // to avaoid error in loopin through null
            // or instead use if() statement
            foreach ((array)$request->name_translation as $key => $name) {

                if ($name != "") {
                    $name_desc_translation =
                        [
                            ...$name_desc_translation, $key => ["name" => $name]
                        ];
                }
            }

            foreach ((array)$request->description_translation as $key => $description) {
                if ($description != '') {
                    //sometimes, transaltion doesnt have en, or ar for names, so it will give error
                    //since of no existence of a key, so create it instead of pushing into it
                    if (array_key_exists($key, $name_desc_translation)) {
                        $name_desc_translation[$key] = [
                            ...$name_desc_translation[$key], "description" => $description
                        ];
                    } else {
                        $name_desc_translation =
                            [
                                ...$name_desc_translation, $key => ["description" => $description]
                            ];
                    }
                }
            }
        }

        $products = Product::create([
            'is_available' => $request->is_available,
            ...$name_desc_translation,
            'item_id' => $request->item_id,
            'price' => $request->price
        ]);

        // ->createMany([])
        $image_path = [];
        foreach ($data['images'] as $key => $image) {
            $image_path = [
                ...$image_path,
                ['image_url' => rand() . '_product_' . time() . '.' . $image->extension()]
            ];

            $image->move(public_path('images'), $image_path[$key]['image_url']);
        }


        $products->productImages()->createMany(
            $image_path
        );


        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function showOneForClients(Product $product)
    {
        if ($product->is_available) {
            $product->visit()->withIp();
            return new ProductResource($product);
        }

        abort(404, 'Product not available');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $name_desc_translation = [];
        if (isset($data['name_translation'])) {
            foreach ($data['name_translation'] as $key => $name) {
                $name_desc_translation = [
                    ...$name_desc_translation, $key => ['name' => $name]
                ];
            }
            unset($data['name_translation']);
        }

        if (isset($data['description_translation'])) {
            foreach ($data['description_translation'] as $key => $description) {
                if (array_key_exists($key, $name_desc_translation)) {
                    $name_desc_translation[$key] = [
                        ...$name_desc_translation[$key], "description" => $description
                    ];
                } else {
                    $name_desc_translation =
                        [
                            ...$name_desc_translation, $key => ["description" => $description]
                        ];
                }
            }
            unset($data['description_translation']);
        }
        $images = isset($data['images']) ? $data['images'] : [];
        unset($data['images']);

        $deleted_images = isset($data['deleted_images']) ? $data['deleted_images'] : [];
        unset($data['deleted_images']);

        //
        //first update data in products table and translations then images down
        //
        $product->update([
            ...$data,
            ...$name_desc_translation,
        ]);

        //first delete images if there is deleted images
        //then add the new ones
        //then insert to productImages table
        if (count($deleted_images)) {
            $check_if_images_for_this_product = $product->productImages()->whereIn('image_url', $deleted_images)->get();

            foreach ($check_if_images_for_this_product as $image) {
                if (File::exists('images/' . $image->image_url)) {
                    File::delete('images/' . $image->image_url);
                }
            }
            $product->productImages()->whereIn('image_url', $deleted_images)->delete();
        }

        $image_path = [];
        //store new comming images
        foreach ($images as $key => $image) {
            $image_path = [
                ...$image_path,
                ['image_url' => rand() . '_item_' . time() . '.' . $image->extension()]
            ];

            $image->move(public_path('images'), $image_path[$key]['image_url']);
        }

        $product->productImages()->createMany($image_path);

        return response(['success' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {

        Gate::allows('manageProduct', $product);
        $images_paths = $product->productImages;
        $product->delete();

        foreach ($images_paths as $product) {
            if (File::exists('images/' . $product->image_url)) {
                File::delete('images/' . $product->image_url);
            }
        }

        return response(['success' => true]);
    }
}
