<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }


        $userid = Auth::user()->id;
        $allrecipes = Recipe::where('user_id', $userid)->get();

        return response()->json([
            'recipes' => RecipeResource::collection($allrecipes),
        ], 200, [], JSON_UNESCAPED_UNICODE);




        // My Note ::
        //  1. Bio, Username from user account 

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png',
            'category_id' => 'required',
            'time' => 'required|numeric',
            'time_unit' => 'required',
            'numberofpeople' => 'required|integer|min:1',
            'ingredients' => 'required', // array
            'instructions' => 'required',
            'user_id' => 'required'
        ]);


        $user = Auth::user();
        $user_id = $user->id;

        $validatedData['user_id'] = $user_id;



        $validatedData['ingredients'] = json_encode($validatedData['ingredients']);
        $validatedData['instructions'] = json_encode($validatedData['instructions']);

        if ($validatedData['image']) {
            $file = $request['image'];
            $imagenewname = uniqid($user_id) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('recipes/images', $imagenewname);

            $filepath = 'recipes/images/' . $imagenewname;
            $validatedData['image'] = $filepath;
        }


        $createrecipe = Recipe::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Recipe created successfully',
            'recipe' => new RecipeResource($createrecipe)
        ], 201);

    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $showrecipe = Recipe::findOrFail($id);

        return response()->json([
            'recipe' => new RecipeResource($showrecipe)
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }



    public function edit(string $id)
    {

        $recipe = Recipe::findOrFail($id);
        return response()->json([
            'recipe' => new RecipeResource($recipe)
        ], 200);

        // http://127.0.0.1:8000/api/recipe/edit/${recipeId}
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png',
            'category_id' => 'required',
            'time' => 'required|numeric',
            'time_unit' => 'required',
            'numberofpeople' => 'required|integer|min:1',
            'ingredients' => 'required', // array
            'instructions' => 'required',
            'user_id' => 'required'
        ]);



        $user = Auth::user();
        $user_id = $user->id;


        $validatedData['user_id'] = $user_id;

        $validatedData['ingredients'] = json_encode($validatedData['ingredients']);
        $validatedData['instructions'] = json_encode($validatedData['instructions']);

        $recipe = Recipe::findOrFail($id);

        // Remove Old Image
        if ($validatedData['image']) {
            $path = $recipe->image;

            if (File::exists($path)) {
                File::delete($path);
            }
        }

        if ($validatedData['image']) {
            $file = $request['image'];
            $imagenewname = uniqid($user_id) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('recipes/images', $imagenewname);

            $filepath = 'recipes/images/' . $imagenewname;
            $validatedData['image'] = $filepath;


        }

        $recipe->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Recipe updated successfully',
            'recipe' => new RecipeResource($recipe)
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();
        return response()->json([
            'status' => true,
            'message' => 'Recipe Deleted successfully'
        ], 204);
    }

}
