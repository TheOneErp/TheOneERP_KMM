<?php

namespace App\Models;

use App\Utils\DatabaseUtil;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'permission_id';

    protected $fillable = [
        'page_id',
        'permission_target_id',
        'permission_type',
        'permission_read',
        'permission_insert',
        'permission_update',
        'permission_delete',
        'permission_allow_rw_all',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'permission_target_id' => 'integer',
        'permission_read' => 'boolean',
        'permission_insert' => 'boolean',
        'permission_update' => 'boolean',
        'permission_delete' => 'boolean',
        'permission_allow_rw_all' => 'boolean',
    ];

    public function group(){
        return $this->where('permission_target_type', '=', 'group')->belongsTo('App\Models\Group', 'group_id', 'permission_target_id');
    }

    public function users(){
        return $this->group()->users();
    }

    public function user(){
        return $this->where('permission_target_type', '=', 'user')->belongsTo('App\Models\User', 'user_id', 'permission_target_id');
    }

    public function page(){
        return $this->belongsTo('App\Models\Page', 'page_id');
    }

	public function permissionColumn(){
        return $this->hasMany('App\Models\PermissionColumn', 'permission_id', 'permission_id');
    }

    public static function boot(){
        parent::boot();

        self::deleting(function($permission) {
            DatabaseUtil::deleteWithRelationship($permission, "permissionColumn");
        });
    }
}

class PermissionColumn  extends Model
{
	protected $table = 'permission_column';
	protected $primaryKey = 'permission_column_id';

	protected $fillable = [
        'permission_id',
		'field_id',
		'permission_column_attribute',
		'permission_column_logic',
		'permission_column_content',
		'permission_column_relative',
		'permission_column_remarks',
        'created_by',
        'updated_by'
    ];

	public function page()
    {
        return $this->belongsTo('App\Models\Permission', 'permission_id');
    }
}
