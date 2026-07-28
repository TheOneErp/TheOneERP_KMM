<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $primaryKey = "log_id";

	protected $fillable = [
		'page_id',
		'form_id',
		'id',
		'parent_id',
		'action',
		'data',
		'created_at',
		'created_by'
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
