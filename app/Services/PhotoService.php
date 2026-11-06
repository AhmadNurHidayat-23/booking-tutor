<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhotoService
{
    public function store(UploadedFile $file): string
    {
        $source = @imagecreatefromstring($file->get());
        if (! $source) {
            throw ValidationException::withMessages(['photo' => 'Gambar tidak dapat dibaca.']);
        }$width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, 1600 / max($width, $height));
        $target = imagecreatetruecolor(max(1, (int) ($width * $scale)), max(1, (int) ($height * $scale)));
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white);
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);
        ob_start();
        imagejpeg($target, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($target);
        imagedestroy($source);
        $path = 'tutors/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }
}
