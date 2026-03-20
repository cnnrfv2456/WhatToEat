<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = $request->user()
            ->histories()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($histories);
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
            'distance'           => 'required|numeric|min:0',
            'photo_url'          => 'nullable|string|max:2048',
            'lat'                => 'required|numeric',
            'lng'                => 'required|numeric',
        ]);

        $history = $request->user()->histories()->create($validated);

        return response()->json($history, 201);
    }

    public function destroy(Request $request, $id)
    {
        $history = $request->user()->histories()->findOrFail($id);
        $history->delete();

        return response()->json(null, 204);
    }
}
