<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';
    protected $primaryKey = 'language_id';

    protected $fillable = [
        'language_id',
        'language_code',
        'language_name',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        "language_id" => "integer",
        'created_by' => "integer",
        'updated_by' => "integer"
    ];

    public function translation()
    {
        return $this->hasMany('App\Models\Translation', 'language_id', 'language_id');
    }
}

class Translation extends Model
{
    protected $table = 'translation';
    protected $primaryKey = 'translation_id';

    protected $fillable = [
        'language_id',
        'translation_type',
        'translation_code',
        'form_id',
        'translation',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'language_id' => "integer",
        'form_id' => "integer",
        'created_by' => "integer",
        'updated_by' => "integer"
    ];

    public function language()
    {
        return $this->belongsTo('App\Models\Language', 'language_id', 'language_id');
    }

    public function form()
    {
        return $this->belongsTo('App\Models\Form', 'form_id', 'form_id');
    }

    public function page()
    {
        return $this->form()->page();
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('translation_type', $type);
    }
}
