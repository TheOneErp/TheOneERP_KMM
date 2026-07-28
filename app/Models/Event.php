<?php

namespace App\Models;

use App\Utils\DatabaseUtil;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
	public $timestamps = false;

	protected $fillable = [
		'title', 'start', 'end','desc','color',
	];
}

?>