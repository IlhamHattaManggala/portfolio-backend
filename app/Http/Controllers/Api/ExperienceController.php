<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $experiences = Experience::where('is_active', true)
            ->orderBy('order')
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ExperienceResource::collection($experiences)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $experience = Experience::create($request->all());

        return response()->json([
            'success' => true,
            'data' => new ExperienceResource($experience)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $experience = Experience::find($id);

        if (!$experience) {
            return response()->json([
                'success' => false,
                'message' => 'Experience not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ExperienceResource($experience)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $experience = Experience::find($id);

        if (!$experience) {
            return response()->json([
                'success' => false,
                'message' => 'Experience not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'company' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $experience->update($request->all());

        return response()->json([
            'success' => true,
            'data' => new ExperienceResource($experience->fresh())
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $experience = Experience::find($id);

        if (!$experience) {
            return response()->json([
                'success' => false,
                'message' => 'Experience not found'
            ], 404);
        }

        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully'
        ]);
    }
}
