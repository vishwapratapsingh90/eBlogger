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
        $imageGenerations = $user->imageGenerations()->latest()->paginate(10);
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
