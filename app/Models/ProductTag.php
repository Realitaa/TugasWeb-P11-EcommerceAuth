<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['product_id', 'tag_id'])]
class ProductTag extends Pivot
{
    //
}
