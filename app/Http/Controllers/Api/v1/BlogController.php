<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BlogResource::collection(Blog::with('author')->paginate($request->input('per_page', 15)));
        
        //
        // return response()->json([
        //     'message' => 'List of blogs',
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlogRequest $request): BlogResource
    {
        //
        /*
        $data = $request->validate([
            'slug' => 'required|string|unique:blogs,slug',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author_id' => 'required|integer|exists:users,id',
        ]);
        */
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
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
    public function update(UpdateBlogRequest $request, Blog $blog): BlogResource
    {
        //
        abort_if($blog->author_id !== Auth::id(), 403, 'You are not authorized to update this blog.');
        /*
        $data = $request->validate([
            'slug' => 'sometimes|required|string|unique:blogs,slug,' . $blog->id,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'author_id' => 'sometimes|required|integer|exists:users,id',
        ]);
        */
        $data = $request->validated();
        $blog->update($data);
        return new BlogResource($blog::with('author')->first());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog): JsonResponse
    {
        //
        abort_if($blog->author_id !== Auth::id(), 403, 'You are not authorized to delete this blog.');
        // use destroy with id to avoid argument mismatch if delete is overridden
        Blog::destroy($blog->id);
        return response()->json([
            'message' => 'Blog deleted successfully',
        ]);
    }

    public function myBlogs(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $user = $request->user();
        return BlogResource::collection($user->blogs()->paginate($request->input('per_page', 15)));
    }
}
