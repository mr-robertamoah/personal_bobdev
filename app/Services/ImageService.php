<?php

namespace App\Services;

use App\DTOs\ImageDTO;
use App\Models\Image;
use Illuminate\Http\UploadedFile;

class ImageService extends Service
{
    public static $path = 'images';

    public static function getDataFromUploadedFile(UploadedFile $uploadedFile, bool $store = false) : array
    {
        $mime = $uploadedFile->getClientMimeType();
        $size = $uploadedFile->getSize();
        $name = $uploadedFile->getClientOriginalName();

        $storageName = null;

        if ($store) {
            $storageName = static::storeImage($uploadedFile, $name);
        }

        return [
            'mime' => $mime,
            'size' => $size,
            'name' => $name,
            'storageName' => $storageName,
        ];
    }

    public static function storeImage(UploadedFile $uploadedFile, ?string $name = null)
    {
        $path = 'storage/' . ImageService::$path;
        $storageName = static::getFileStorageName(
            $name ?: $uploadedFile->getClientOriginalName(), 
            $uploadedFile->getClientOriginalExtension()
        );

        $uploadedFile->storeAs($path, $storageName);

        return $storageName;
    }

    public function createImage(ImageDTO $imageDTO)
    {
        $imageDTO->storageName = static::storeImage($imageDTO->file);

        $image = Image::create($imageDTO->getData(all: true));

        return $image;
    }
}