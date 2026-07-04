<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get list of all categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return $this->successResponse(CategoryResource::collection($categories));
    }
}
