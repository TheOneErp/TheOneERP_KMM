<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Permission;
use App\Models\PermissionColumn;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('permissions')){
            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('permission_id');
                $table->integer('page_id');
                $table->integer('permission_target_id');
                $table->string('permission_type');
                $table->boolean('permission_read')->default(false);
                $table->boolean('permission_insert')->default(false);
                $table->boolean('permission_update')->default(false);
                $table->boolean('permission_delete')->default(false);
                $table->boolean('permission_allow_rw_all')->default(false);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['page_id','permission_target_id','permission_type'],'permissions_index');
            });
        }

        if(!Schema::hasTable('permission_column')){
            Schema::create('permission_column', function (Blueprint $table) {
                $table->bigIncrements('permission_column_id');
                $table->integer('permission_id');
                $table->integer('field_id');
                $table->string('permission_column_attribute',10);
                $table->string('permission_column_logic',10);
                $table->string('permission_column_content',255);
                $table->string('permission_column_relative',5);
                $table->string('permission_column_remarks',255);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['permission_id','permission_column_id','field_id'],'permissioncolumns_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if($this->ALL_ROLLBACK){
            Schema::dropIfExists('permissions');
            Schema::dropIfExists('permission_column');
        }else{
            Permission::where('created_by',-1)->delete();
            // PermissionColumn::where('created_by',-1)->delete();
        }
    }
}
