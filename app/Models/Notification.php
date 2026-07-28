<?php

namespace App\Models;

use App\Utils\DatabaseUtil;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = "notification_id";
	protected $fillable = [
		'notification_id',
		'notification_setting_id',
		'user_id',
		'notification_text',
		'notification_link',
		'notification_read',
		'created_by',
		'updated_by'
    ];
}

class NotificationUser extends Model
{
	protected $fillable = [
		'user_id',
		'notification_user_phone',
		'notification_user_email',
		'created_by',
		'updated_by'
    ];
    protected $casts = [
        'user_id' => 'integer',
    ];
    protected $table = 'notification_user';
    protected $primaryKey = "notification_user";

	public function user(){
        return $this->belongsTo('App\Models\User');
    }
}

class NotificationSetting extends Model
{
    protected $table = 'notification_setting';
    protected $primaryKey = "notification_setting_id";

	protected $fillable = [
		'page_id',
		'notification_setting_trigger_type',
		'notification_setting_content',
		'notification_setting_mail',
		'notification_setting_phone',
		'created_by',
		'updated_by'
    ];

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification', 'notification_setting_id');
    }

	public function notificationTarget()
    {
        return $this->hasMany('App\Models\NotificationTarget', 'notification_setting_id','notification_setting_id');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($notification_setting) {
            DatabaseUtil::deleteWithRelationship($notification_setting, "notificationTarget");
        });
    }
}

class NotificationTarget extends Model
{
    protected $table = 'notification_target';
    protected $primaryKey = "notification_target_id";
	protected $fillable = [
		'notification_setting_id',
		'notification_target',
		'notification_target_type',
		'created_by',
		'updated_by'
    ];

	 public function setting()
    {
        return $this->belongsTo('App\Models\NotificationSetting', 'notification_setting_id', 'notification_setting_id');
    }
}
