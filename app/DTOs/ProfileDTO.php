<?php

namespace App\DTOs;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class ProfileDTO extends BaseDTO
{
    public ?string $about = null;
    public ?string $action = null;
    public ?string $profileableId = null;
    public ?string $profileableType = null;
    public ?Model $profileable = null;
    public ?Profile $profile = null;
    public array $settings = [];

    protected array $dtoDataKeys = [
        'about',
        'settings'
    ];
}