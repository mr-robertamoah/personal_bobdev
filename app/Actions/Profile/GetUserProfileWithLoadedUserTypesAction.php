<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GetUserProfileWithLoadedUserTypesAction extends Action
{
    public function execute(User $user): Profile|null
    {
        return Profile::query()
            ->where('profileable_type', $user::class)
            ->where('profileable_id', $user->id)
            ->with("profileable", function(MorphTo $morphTo) {
                return $morphTo->morphWith([
                    User::class => [
                        'userTypes'
                    ]
                ]);
            })
            ->first();
    }
}