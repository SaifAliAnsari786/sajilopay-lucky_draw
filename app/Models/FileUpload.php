<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUpload extends Model
{
    use HasFactory;

    public static function uploadFile($file, $location)
    {
        try {
            // Generate a unique name for the file
            $fileName = Str::random(20). time() . '.' . $file->getClientOriginalExtension();

            // Store the file in the 'public' disk (you can configure other disks as needed)
            Storage::disk('public')->putFileAs($location, $file, $fileName);

            // Return the file path for future use (e.g., storing in the database)
            return $fileName;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function deleteFiles($fileArray): bool
    {
        Storage::disk('public')->delete($fileArray);
        return true;
    }
}
