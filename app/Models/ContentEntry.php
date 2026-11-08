<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentEntry extends Model
{
    protected $fillable = ['key', 'group', 'label', 'type', 'value'];
}
