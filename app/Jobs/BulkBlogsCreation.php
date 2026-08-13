<?php

namespace App\Jobs;

use App\Models\Blog;
use App\Models\BlogsImportLogs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BulkBlogsCreation implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $importId,
        public string $originalFilename,
        public string $filePath,
        public int $uploadedBy,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $importStatus = '1';
        BlogsImportLogs::where('id', $this->importId)->update(['status' => $importStatus]);

        // $file = $this->file;
        logger()->info('Processing File: ' . $this->originalFilename . ' Path: ' . $this->filePath);

        if (!Storage::disk('public')->exists($this->filePath)) {
            logger()->error('File does not exist' . public_path('storage/' . $this->filePath));
            return;
        }

        $fullPath = Storage::disk('public')->path($this->filePath);
        $file = new \SplFileObject($fullPath);

        $file->setFlags(
            \SplFileObject::READ_CSV |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::DROP_NEW_LINE
        );

        $header = $file->fgetcsv();

        $chunkSize = 100;
        $chunk = [];
        $totalRecords = -1; // -1 count for ignoring header count.
        $totalSuccess = 0;
        $totalFailed = 0;

        foreach ($file as $row) {

            // Skip empty/invalid rows
            if ($row === [null] || $row === false) {
                continue;
            }

            $totalRecords++;

            $chunk[] = $row;

            if (count($chunk) >= $chunkSize) {

                // Process this chunk
                $return = $this->processChunk($chunk, $header);
                $totalSuccess += $return['totalSuccess'];
                $totalFailed += $return['totalFailed'];

                $chunk = [];
            }
        }

        // Process remaining records
        if (!empty($chunk)) {
            $return = $this->processChunk($chunk, $header);
            $totalSuccess += $return['totalSuccess'];
            $totalFailed += $return['totalFailed'];
        }



        if ($totalSuccess > 0 && $totalFailed > 0) {
            $importStatus = '2';
        } elseif ($totalSuccess === $totalRecords) {
            $importStatus = '3';
        } elseif ($totalFailed === $totalRecords) {
            $importStatus = '4';
        }

        logger()->info('CSV processing completed', [
            'total_records' => $totalRecords,
            'total_processed' => $totalSuccess,
            'total_failed' => $totalFailed,
            'status' => $importStatus,
        ]);

        BlogsImportLogs::where('id', $this->importId)->update([
            'total_records' => $totalRecords,
            'total_processed' => $totalSuccess,
            'total_failed' => $totalFailed,
            'status' => $importStatus,
        ]);

        return;
    }

    private function processChunk(array $chunk, $header): array
    {
        $return = ['totalSuccess' => 0, 'totalFailed' => 0];

        logger()->info('processChunk called');
        logger()->info('header: ' . Json::encode($header));

        foreach ($chunk as $row) {
            if ($row === [null] || $row === false || $row === []) {
                continue;
            }

            if ($header !== null && $row === $header) {
                continue;
            }
            logger()->info('Record:' . Json::encode($row));
            // $this->processRow($row);

            try {
                $blog = new Blog();
                $blog->slug = $row[0] ?? null;
                $blog->title = $row[1] ?? null;
                $blog->content = $row[2] ?? null;
                $blog->author_id = $this->uploadedBy;

                if ($blog->save()) {
                    $return['totalSuccess']++;
                } else {
                    $return['totalFailed']++;
                    logger()->error('Failed to save blog row', ['row' => $row]);
                }
            } catch (\Throwable $e) {
                $return['totalFailed']++;
                logger()->error('Error saving blog row', [
                    'row' => $row,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $return;
    }
}
