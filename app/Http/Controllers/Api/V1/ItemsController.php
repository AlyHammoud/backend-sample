<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\V1\ItemResource;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class ItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ItemResource::collection(Item::with('itemImages')->paginate(20));
    }

    public function allAvailable()
    {
        return ItemResource::collection(Item::where('is_available', '1')->with('itemImages')->paginate(20));
    }

    /**
     * translations['name', 'description'] fillable['price', 'is_available', 'category_id'] 
     * images [item_id, image_url]
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreItemRequest $request)
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

        //first store to db, then upload images if success
        $item = Item::create([
            'is_available' => $request->is_available,
            ...$name_desc_translation,
            'category_id' => $request->category_id,
            'price' => $request->price
        ]);

        // upload images with createMany([])
        //[[image => ab.jpeg], [image => vs.jpeg]]
        $image_path = [];
        foreach ($data['images'] as $key => $image) {
            $image_path = [
                ...$image_path,
                ['image_url' => rand() . '_item_' . time() . '.' . $image->extension()]
            ];

            $image->move(public_path('images'), $image_path[$key]['image_url']);
        };

        $item->itemImages()
            ->createMany(
                $image_path
            );


        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        return new ItemResource($item);
    }

    public function showOneForClients(Item $item)
    {
        if ($item->is_available) {
            $item->visit()->withIp();
            return new ItemResource($item);
        }

        abort(404, 'Item not available');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateItemRequest  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateItemRequest $request, Item $item)
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

        $item->update([
            ...$data,
            ...$name_desc_translation,
        ]);


        $image_path = [];
        //store new comming images
        foreach ($images as $key => $image) {
            $image_path = [
                ...$image_path,
                ['image_url' => rand() . '_item_' . time() . '.' . $image->extension()]
            ];

            $image->move(public_path('images'), $image_path[$key]['image_url']);
        }

        // store before deleting 
        $item->itemImages()->createMany($image_path);

        if (count($deleted_images)) {

            $check_if_images_for_this_item = $item->itemImages()->whereIn('image_url', $deleted_images)->get();

            foreach ($check_if_images_for_this_item as $image) {
                if (File::exists('images/' . $image->image_url)) {
                    File::delete('images/' . $image->image_url);
                }
            }
            $item->itemImages()->whereIn('image_url', $deleted_images)->delete();
        }

        return response(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        Gate::allows('manageItem', $item);

        $itemWithPorducts = $item->with('products.productImages')->first();

        $item->delete();

        foreach ($itemWithPorducts->itemImages as $itemImage) {
            if (File::exists('images/' . $itemImage->image_url)) {
                File::delete('images/' . $itemImage->image_url);
            }

            foreach ($itemWithPorducts->products as $product) {
                foreach ($product->productImages as $productImage) {
                    if (File::exists('images/' . $productImage->image_url)) {
                        File::delete('images/' . $productImage->image_url);
                    }
                }
            }
        }

        return response(['success' => true]);
    }

    public function deleteAllItems()
    {
        Gate::authorize('fullAccessAdmin', Auth::user());

        $categoriesNestedRelations = Category::with('items', 'items.itemImages')->get();

        foreach ($categoriesNestedRelations as $category) {
            if (File::exists('images/' . $category->image_url)) {
                File::delete('images/' . $category->image_url);
            }
            foreach ($category->items as $item) {
                foreach ($item->itemImages as $image) {
                    if (File::exists('images/' . $image->image_url)) {
                        File::delete('images/' . $image->image_url);
                    }
                    ItemImage::where('id', $image->id)->delete();
                }
                Item::where('id', $item->id)->delete();
            }
            Category::where('id', $category->id)->delete();
        }

        return response(['success' => true]);
    }
}
