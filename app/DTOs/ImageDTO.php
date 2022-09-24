<?php

namespace App\DTOs;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use MrRobertAmoah\DTO\BaseDTO;

class ImageDTO extends BaseDTO
{
    public string $name;
    public string $storageName;
    public string $mime;
    public int $size;
    public ?UploadedFile $file;

    protected array $dtoFileKeys = [
        'file'
    ];

    protected array $dtoSpecifiedDataKeys = [
        'storage_name' => 'storageName'
    ];
    
    protected function fromRequestExtension(Request $request) : self
    {
        return $this->setOtherPropertiesUsingFile();
    }
    
    protected function fromArrayExtension(array $data = []) : self
    {
        return $this->setOtherPropertiesUsingFile();
    }

    private function setOtherPropertiesUsingFile() : self
    {
        if (is_null($this->file)) {
            return $this;
        }

        list($name, $mime, $size,) = ImageService::getDataFromUploadedFile(
            uploadedFile: $this->file
        );

        $this->name = $name;
        $this->mime = $mime;
        $this->size = $size;

        return $this;
    }
}