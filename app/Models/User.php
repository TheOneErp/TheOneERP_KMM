<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Group;

use App\Utils\DatabaseUtil;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'username',
        'password',
        'name',
        'user_disabled',
        'user_remarks',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        "password"
    ];

    protected $casts = [
        'user_disabled' => 'boolean',
    ];

    public function userAgent(){
        return $this->hasOne('App\Models\UserAgent', 'user_id', 'user_id');
    }

    public function permissions(){
        return $this->hasMany('App\Models\Permission', 'permission_target_id', 'user_id')->where('permission_type', '=', 'user');
    }

    public function permissionColumn(){
        return $this->hasManyThrough('App\Models\PermissionColumn', 'App\Models\Permission','permission_target_id','permission_id');
    }

    public function groupUsers(){
        return $this->hasMany('App\Models\GroupUser', 'user_id', 'user_id');
    }

    public function groups(){
        return Group::whereIn('group_id',$this->groupUsers()->get()->map(function($groupUser){return($groupUser->group_id);})->toArray())->get();
    }

    public function notifications(){
        return $this->hasMany('App\Models\Notification', 'user_id', 'user_id');
    }

    public function notificationUser(){
        return $this->hasOne('App\Models\NotificationUser', 'user_id', 'user_id');
    }

    public function notificationTarget(){
        return $this->hasMany('App\Models\NotificationTarget', 'notification_target', 'user_id');
    }

    public function verifyLevel(){
        return $this->hasMany('App\Models\VerifyLevel', 'verify_target_id', 'user_id')->where('verify_target_type', '=', 'user');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($user) {
            DatabaseUtil::deleteWithRelationship($user, "userAgent");
            DatabaseUtil::deleteWithRelationship($user, "groupUsers");
            DatabaseUtil::deleteWithRelationship($user, "permissions");
            DatabaseUtil::deleteWithRelationship($user, "verifyLevel");
            DatabaseUtil::deleteWithRelationship($user, "notifications");
            DatabaseUtil::deleteWithRelationship($user, "notificationUser");
            DatabaseUtil::deleteWithRelationship($user, "notificationTarget");
        });
    }
}

class UserAgent extends Model
{
    protected $table = 'user_agent';
    protected $primaryKey = 'user_agent_id';

    protected $fillable = [
        'user_id',
        'user_agent_enabled',
        'user_agent_enabled_at',
        'user_agent_disabled_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'user_agent_enabled' => 'boolean',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function userAgentPage(){
        return $this->hasMany('App\Models\UserAgentPage', 'user_agent_id', 'user_agent_id');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($userAgent) {
            DatabaseUtil::deleteWithRelationship($userAgent, "userAgentPage");
        });
    }
}

class UserAgentPage extends Model
{
    protected $table = 'user_agent_page';
    protected $primaryKey = 'user_agent_page_id';

    protected $fillable = [
        'user_agent_id',
        'page_id',
        'user_agent_target_type',
        'user_agent_target_id',
    ];

    protected $casts = [
        'user_agent_id' => 'integer',
        'page_id' => 'integer',
        'user_agent_target_id' => 'integer',
    ];

    public function userAgent(){
        return $this->belongsTo('App\Models\UserAgent');
    }
}
