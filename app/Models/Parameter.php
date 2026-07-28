<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table = 'parameters';
    protected $primaryKey = 'parameter_id';

    protected $fillable = [
        'parameter_code',
        'parameter_value',
        'parameter_remarks',
        'parameter_deletable',
    ];
}
