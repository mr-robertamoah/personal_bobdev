<?php

namespace App\Actions\Skills;

use App\Actions\Action;
use App\Exceptions\SkillException;
use App\Models\Skill;

class CheckIfValidSkillsAction extends Action
{
    public function execute(array $ids)
    {
        $validSkillIds = Skill::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
        
        if (count($ids) == count($validSkillIds)) {
            return;
        }

        $ids = array_reduce(
            array_diff($ids, $validSkillIds), 
            function($carry, $item) {
                if ($carry)
                {
                    return str($carry) . ", " . str($item);
                }

                return str($item);
            });
        throw new SkillException("Sorry, ({$ids}) ids do not point to valid skills.");
    }
}