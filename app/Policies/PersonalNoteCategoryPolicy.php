<?php

namespace App\Policies;

use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\User;

class PersonalNoteCategoryPolicy
{
    public function __construct() {}

    public function own(User $user, PersonalNoteCategories $note): bool
    {
        return $user->id == $note->user_id;
    }

    public function create(): bool
    {
        return true;
    }
}
