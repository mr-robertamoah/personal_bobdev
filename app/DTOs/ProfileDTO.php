<?php

namespace App\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class ProfileDTO extends BaseDTO
{
    public ?string $about = null;
    public Model $profileable = null;
    public $settings = null;

    protected array $dtoDataKeys = [
        'about',
        'settings'
    ];
    
    /**
     * assign data (filled or validated) to the dto properties as an 
     * addition to the fromRequest function.
     *
     * @param  Illuminate\Http\Request  $request
     * @return MrRobertAmoah\DTO\BaseDTO
     */
    protected function fromRequestExtension(Request $request) : self
    {
        return $this->jsonDecodeSettings();
    }

    /**
     * assign values of keys of an array to the corresponding dto properties 
     * as an additional function for the fromData function.
     *
     * @param  array  $data
     * @return MrRobertAmoah\DTO\BaseDTO
     */
    protected function fromArrayExtension(array $data = []) : self
    {
        return $this->jsonDecodeSettings();
    }

    private function jsonDecodeSettings()
    {
        $this->settings = ! $this->settings ? [] : (array) json_decode($this->settings);

        return $this;
    }
}