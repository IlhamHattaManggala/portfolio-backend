<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TechnologyResource;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TechnologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $technologies = Technology::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => TechnologyResource::collection($technologies)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('technologies', 'public');
            $data['icon'] = $iconPath;
        }

        $technology = Technology::create($data);

        return response()->json([
            'success' => true,
            'data' => new TechnologyResource($technology)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $technology = Technology::find($id);

        if (!$technology) {
            return response()->json([
                'success' => false,
                'message' => 'Technology not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TechnologyResource($technology)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $technology = Technology::find($id);

        if (!$technology) {
            return response()->json([
                'success' => false,
                'message' => 'Technology not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon
            if ($technology->icon) {
                Storage::disk('public')->delete($technology->icon);
            }
            $iconPath = $request->file('icon')->store('technologies', 'public');
            $data['icon'] = $iconPath;
        }

        $technology->update($data);

        return response()->json([
            'success' => true,
            'data' => new TechnologyResource($technology->fresh())
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $technology = Technology::find($id);

        if (!$technology) {
            return response()->json([
                'success' => false,
                'message' => 'Technology not found'
            ], 404);
        }

        // Delete icon if exists
        if ($technology->icon) {
            Storage::disk('public')->delete($technology->icon);
        }

        $technology->delete();

        return response()->json([
            'success' => true,
            'message' => 'Technology deleted successfully'
        ]);
    }
}
