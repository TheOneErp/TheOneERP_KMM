<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Group;
use App\Models\GroupUser;
class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('groups')){
            Schema::create('groups', function (Blueprint $table) {
                $table->increments('group_id');
                $table->string('group_name');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['group_id','group_name']);
            });
        }

        if(!Schema::hasTable('group_user')){
            Schema::create('group_user', function (Blueprint $table) {
                $table->increments('group_user_id');
                $table->integer('group_id');
                $table->integer('user_id');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['group_id','user_id'],'groupuser_index');
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
            Schema::dropIfExists('groups');
            Schema::dropIfExists('group_user');
        }else{
            Group::where('created_by',-1)->delete();
            GroupUser::where('created_by',-1)->delete();
        }
    }
}
