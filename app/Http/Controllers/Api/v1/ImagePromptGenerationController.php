<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePromptRequest;
use App\Http\Resources\ImagePromptGenerationResource;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImagePromptGenerationController extends Controller
{
    public function __construct(
        private OpenAiService $openAiService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $allowedSortColumns = ['id', 'generated_prompt', 'original_filename', 'file_size', 'mime_type', 'created_at', 'updated_at'];
        $search = $request->input('search');
        $sortBy = $request->input('sort_by');
        $sortOrder = strtolower((string) $request->input('sort_order', 'asc'));

        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }

        $imageGenerations = $user->imageGenerations()
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where('generated_prompt', 'like', "%{$search}%");
            })
            ->when($request->filled('sort_by') && in_array($sortBy, $allowedSortColumns, true), function ($query) use ($sortBy, $sortOrder) {
                $query->orderBy($sortBy, $sortOrder);
            })
            ->paginate($request->input('per_page', 15));

        return ImagePromptGenerationResource::collection($imageGenerations);
    }

    public function store(GeneratePromptRequest $request)
    {
        $user = $request->user();
        $image = $request->file('image');

        $originalFilename = $image->getClientOriginalName();
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9\s.-_]/', '', pathinfo($originalFilename, PATHINFO_FILENAME));
        $extension = $image->getClientOriginalExtension();
        $safeFileName = $sanitizedFilename.'_'.Str::random(32).'.'.$extension;

        $imagePath = $image->storeAs('uploads/images', $safeFileName, 'public');

        $generatedPrompt = $this->openAiService->generatePromptFromImage($image);

        $imageGeneration = $user->imageGenerations()->create([
            'image_path' => $imagePath,
            'generated_prompt' => $generatedPrompt,
            'original_filename' => $originalFilename,
            'file_size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ]);

        return new ImagePromptGenerationResource($imageGeneration);
    }
}
