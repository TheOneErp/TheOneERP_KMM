<?php

namespace App\Models;

use App\Utils\DatabaseUtil;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'group_name',
        'created_by',
        'updated_by'
    ];

    protected $casts = [];

    public function users(){
        return $this->hasMany('App\Models\GroupUser', 'group_id', 'group_id');
    }

    public function permissions(){
        return $this->hasMany('App\Models\Permission', 'permission_target_id', 'group_id')->where('permission_type', '=', 'group');
    }

    public function verifyLevel(){
        return $this->hasMany('App\Models\VerifyLevel', 'verify_target_id', 'group_id')->where('verify_target_type', '=', 'group');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($group) {
            DatabaseUtil::deleteWithRelationship($group, "users");
            DatabaseUtil::deleteWithRelationship($group, "verifyLevel");
            DatabaseUtil::deleteWithRelationship($group, "permissions");
        });
    }
}

class GroupUser extends Model
{
    protected $table = 'group_user';
    protected $primaryKey = 'group_user_id';

    protected $fillable = [
        'group_id',
        'user_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [];

    public function group(){
        return $this->belongsTo('App\Models\Group', 'group_id', 'group_id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
