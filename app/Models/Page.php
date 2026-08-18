<?php

namespace App\Models;

use App\Utils\DatabaseUtil;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'page_id';

    protected $fillable = [
        'page_code',
        'page_module',
        'page_controllcastser',
        'page_list_template',
        'page_form_template',
        'page_visible',
        'page_stay',
        'page_order',
        'page_readonly',
        'page_options',
        'page_remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'page_module' => 'integer',
        'page_visible' => 'boolean',
        'page_stay' => 'boolean',
        'page_order' => 'integer',
        'page_readonly' => 'boolean',
        'page_options' => 'array',
    ];

    public function scopeVisible($query){
        $query->where('page_visible', 1);
    }

    public function scopeFindByCode($query, $code){
        $query->where('page_code', $code);
    }

    public function module(){
        return $this->belongsTo('App\Models\Page', 'page_module','page_id');
    }

    public function subPages(){
        $this->hasMany('App\Models\Page', 'page_id', 'page_module');
    }

    public function forms(){
        return $this->hasMany('App\Models\Form', 'page_id', 'page_id');
    }

    public function permissions(){
        return $this->hasMany('App\Models\Permission', 'page_id', 'page_id');
    }

    public function notificationSetting(){
        return $this->hasMany('App\Models\NotificationSetting', 'page_id', 'page_id');
    }

    public function translation(){
        return $this->hasMany('App\Models\Translation', 'translation_code', 'page_code');
    }

    public function verifies(){
        return $this->hasOne('App\Models\Verify', 'page_id', 'page_id');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($page) {
            DatabaseUtil::deleteWithRelationship($page, "forms");
            DatabaseUtil::deleteWithRelationship($page, "verifies");
            DatabaseUtil::deleteWithRelationship($page, "translation");
            DatabaseUtil::deleteWithRelationship($page, "permissions");
            DatabaseUtil::deleteWithRelationship($page, "notificationSetting");
        });
    }
}

class Form extends Model
{
    protected $table = 'forms';
    protected $primaryKey = 'form_id';

    protected $fillable = [
        'page_id',
        'form_order',
        'form_type',
        'form_parent',
        'ref_page_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [];

    public function fields()
    {
        return $this->hasMany('App\Models\Field', 'form_id', 'form_id');
    }

    public function page()
    {
        return $this->belongsTo('App\Models\Page', 'page_id');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('form_type', $type);
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($form) {
            DatabaseUtil::deleteWithRelationship($form, "fields");
        });
    }
}

class Field extends Model
{
    protected $table = 'fields';
    protected $primaryKey = 'field_id';

    protected $fillable = [
        'form_id',
        'field_code',
        'field_type',
        'field_rule',
        'field_order',
        'field_default_value',
        'field_required',
        'field_readonly',
        'field_show_on_form',
        'field_show_on_list',
        'field_options',
        'field_remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'field_required' => 'boolean',
        'field_readonly' => 'boolean',
        'field_show_on_form' => 'boolean',
        'field_show_on_list' => 'boolean',
        'field_rule' => 'array',
        'field_options' => 'array'
    ];

    public function scopeGetShowForm($query, $form_id)
    {
        return $query->where('field_show_on_form', 1)->where('form_id', $form_id)->orderBy('field_order');
    }

    public function scopeGetShowList($query, $form_id)
    {
        return $query->where('field_show_on_list', 1)->where('form_id', $form_id)->orderBy('field_order');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('field_type', $type);
    }

    public function translation(){
        return $this->hasMany('App\Models\Translation', 'translation_code', 'field_code');
    }

    public function form()
    {
        return $this->belongsTo('App\Models\Form', 'form_id');
    }

    public function page()
    {
        return $this->belongsTo('App\Models\Form', 'form_id')->page();
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($field) {
            // $field->translation()->delete();
        });
    }
}
