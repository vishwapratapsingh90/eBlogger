<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class BlogsImportLogs extends Model
{
    //
    use HasApiTokens, HasFactory;

    protected $fillable = [
        "original_file_name",
        "processed_file_name",
        "file_path",
        "total_records",
        "total_processed",
        "total_failed",
        "status", // 0 - Not Started, 1 - Inprogress, 2 - Partial Success with errors, 3 - Completed, 4 - Failed
        "uploaded_by",
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "uploaded_by", "id");
    }
}
