<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BlogResource::collection(Blog::with('author')->get());
        
        //
        // return response()->json([
        //     'message' => 'List of blogs',
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): BlogResource
    {
        //
        $data = $request->validate([
            'slug' => 'required|string|unique:blogs,slug',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author_id' => 'required|integer|exists:users,id',
        ]);
        $blog = Blog::create($data);

        return new BlogResource($blog);

        // return response()->json([
        //     'message' => 'Blog created successfully',
        //     'data' => new BlogResource($blog),
        // ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog): BlogResource
    {
        return new BlogResource($blog::with('author')->first());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog): BlogResource
    {
        //
        $data = $request->validate([
            'slug' => 'sometimes|required|string|unique:blogs,slug,' . $blog->id,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'author_id' => 'sometimes|required|integer|exists:users,id',
        ]);
        $blog->update($data);
        return new BlogResource($blog::with('author')->first());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog): JsonResponse
    {
        //
        $blog->delete();
        return response()->json([
            'message' => 'Blog deleted successfully',
        ]);
    }
}
