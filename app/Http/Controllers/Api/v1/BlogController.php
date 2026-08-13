<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Resources\BlogResource;
use App\Jobs\BulkBlogsCreation;
use App\Models\Blog;
use App\Models\BlogsImportLogs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
     * 
     * @param StoreBlogRequest $request
     * @return BlogResource
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
     * 
     * @param Blog $blog
     * @return BlogResource
     */
    public function show(Blog $blog): BlogResource
    {
        // ensure the bound model has its relations loaded
        $blog->load('author');
        return new BlogResource($blog);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param UpdateBlogRequest $request
     * @param Blog $blog
     * @return BlogResource
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
        // Eager load relationships for the returned resource
        $blog->load('author');
        return new BlogResource($blog);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param Blog $blog
     * @return JsonResponse
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

    /**
     * Get the blogs of the authenticated user.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function myBlogs(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $user = $request->user();
        return BlogResource::collection($user->blogs()->paginate($request->input('per_page', 15)));
    }

    public function uploadBlogsCsvFile(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'blog_file' => [
                'required',
                'file',
                File::types(['csv'])->max('10mb'),
            ],
        ]);


        $file = $request->file('blog_file');

        $originalFilename = $file->getClientOriginalName();
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9\s.-_]/', '', pathinfo($originalFilename, PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $fileName = $sanitizedFilename . '_' . Str::random(32) . '.' . $extension;

        $filePath = $file->storeAs('uploads/imports/blogs', $fileName, 'public');

        $import = BlogsImportLogs::create([
            'original_file_name' => $originalFilename,
            'processed_file_name' => $fileName,
            'file_path' => $filePath,
            'total_records' => 0,
            'total_processed' => 0,
            'total_failed' => 0,
            'status' => '0',
            'uploaded_by' => $userId,
        ]);

        $importId = $import->id;

        try {
            BulkBlogsCreation::dispatch($importId, $originalFilename, $filePath, $userId)
                ->onQueue('blogs_import');

            return response()->json([
                'message' => 'Job dispatched successfully',
                'queue' => 'blogs_import',
                'status' => 'queued',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to dispatch job',
                'queue' => 'blogs_import',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
