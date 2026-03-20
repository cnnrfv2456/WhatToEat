<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'place_id'           => 'required|string',
            'name'               => 'required|string|max:255',
            'address'            => 'nullable|string|max:500',
            'phone'              => 'nullable|string|max:50',
            'price_level'        => 'nullable|integer|min:0|max:4',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'user_ratings_total' => 'nullable|integer|min:0',
            'photo_url'          => 'nullable|string|max:2048',
            'lat'                => 'required|numeric',
            'lng'                => 'required|numeric',
        ]);

        $favorite = $request->user()->favorites()->firstOrCreate(
            ['place_id' => $validated['place_id']],
            $validated
        );

        return response()->json($favorite, 201);
    }

    public function destroy(Request $request, $id)
    {
        $favorite = $request->user()->favorites()->findOrFail($id);
        $favorite->delete();

        return response()->json(null, 204);
    }
}
