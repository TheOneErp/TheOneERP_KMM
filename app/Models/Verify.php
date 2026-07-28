<?php

namespace App\Models;

use App\Utils\DatabaseUtil;
use Illuminate\Database\Eloquent\Model;

class Verify extends Model
{
    protected $table = 'verifies';
    protected $primaryKey = 'verify_id';
    protected $fillable = [
        'page_id',
        'verify_remarks',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'page_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function page(){
        return $this->belongsTo('App\Models\Page', 'page_id', 'page_id');
    }

    public function verifyLevel(){
        return $this->hasMany('App\Models\VerifyLevel', 'verify_id', 'verify_id');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($verify) {
            DatabaseUtil::deleteWithRelationship($verify, "verifyLevel");
        });
    }
}

class VerifyLevel extends Model
{
    protected $table = 'verify_level';
    protected $primaryKey = 'verify_level_id';

    protected $fillable = [
        'verify_id',
        'verify_level',
        'verify_target_id',
        'verify_target_type',
        'verify_population',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'verify_id' => 'integer',
        'verify_level' => 'integer',
        'verify_target_id' => 'integer',
        'verify_population' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];


    public function verify(){
        return $this->belongsTo('App\Models\Verify', 'verify_id', 'verify_id');
    }

    public function verifyTarget(){
        $targetId = $this->verify_target_type."_id";
        $targetModel = ucfirst($this->verify_target_type);
        return $this->belongsTo("App\\Models\\$targetModel", 'verify_target_id', $targetId);
    }

    public function verifyCondition(){
        return $this->hasMany('App\Models\VerifyCondition', 'verify_level_id', 'verify_level_id');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($verifyLevel) {
            DatabaseUtil::deleteWithRelationship($verifyLevel, "verifyCondition");
        });
    }
}

class VerifyCondition extends Model
{
    protected $table = 'verify_condition';
    protected $primaryKey = 'verify_condition_id';

    protected $fillable = [
        'verify_level_id',
        'verify_condition_group',
        'verify_logical',
        'field_code',
        'verify_comparison',
        'verify_value',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'verify_level_id' => 'integer',
        'verify_condition_group' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function verifyLevel(){
        return $this->belongsTo('App\Models\VerifyLevel', 'verify_level_id', 'verify_level_id');
    }
}
