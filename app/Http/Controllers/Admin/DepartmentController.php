<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource with optional search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $departments = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Departments fetched successfully',
            'data' => DepartmentResource::collection($departments->items()),
            'meta' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = Department::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => new DepartmentResource($department),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new DepartmentResource($department),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        $department->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => new DepartmentResource($department),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {

        if ($department->doctors()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department. Please reassign or delete its active doctors first.',
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully',
        ]);
    }
}
