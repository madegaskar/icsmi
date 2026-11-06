<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'name', 'position', 'affiliation', 'country', 'sort_order'])]
class BoardMember extends Model
{
    public const GROUP_EDITORIAL = 'editorial';

    public const GROUP_SPEAKER = 'speaker';
}
