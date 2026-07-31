<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use OpenAI\Factory;

class OpenAiService
{
    public function generatePromptFromImage(UploadedFile $image): string
    {
        $imageData = base64_encode(file_get_contents($image->getPathname()));
        $mimeType = $image->getClientMimeType();

        $client = (new Factory)->withApiKey(config('services.openai.api_key'))->make();
        $response = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Analyze this image and generate a detailed descriptive prompt that could be reused to recreate a similar image using AI image generation tools. The prompt should be comprehensive, describing the visual elements, composition, style, and any other relevant details. Make it detailed enough that someone could use it to generate a similar image. You must preserve the aspect ratio as the original image or close to it.',
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:'.$mimeType.';base64,'.$imageData,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $response->choices[0]->message->content;
    }
}
