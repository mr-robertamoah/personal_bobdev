<?php

namespace App\Actions\Skills;

use App\Actions\Action;
use App\Exceptions\SkillException;
use App\Models\Skill;

class CheckIfValidSkillsAction extends Action
{
    public function execute(array $ids)
    {
        foreach ($ids as $key => $value) {
            $this->checkValidity($value);
        }
    }

    private function checkValidity($id)
    {
        if (Skill::find($id)) {
            return;
        }

        throw new SkillException("Sorry, no skill with id {$id} exists.");
    }
}