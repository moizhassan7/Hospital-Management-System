<?php

namespace App\Http\Controllers\Api; // Note the Api namespace

use App\Http\Controllers\Controller; // Base Controller
use App\Models\Location; // Your Location Model
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Location::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
        ]);

        $location = Location::create($request->all());
        return response()->json($location, 201); // 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        return response()->json($location);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
        ]);

        $location->update($request->all());
        return response()->json($location);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return response()->json(null, 204); // 204 No Content
    }
}