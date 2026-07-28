<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\NotificationTarget;
use App\Models\NotificationSetting;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('notifications')){
            Schema::create('notifications', function (Blueprint $table) {
                $table->bigIncrements('notification_id');
                $table->integer('notification_setting_id');
                $table->integer('user_id');
                $table->string('notification_text');
                $table->string('notification_link');
                $table->boolean('notification_read');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['user_id'],'notifications_index');
            });
        }

        if(!Schema::hasTable('notification_user')){
            Schema::create('notification_user', function (Blueprint $table) {
                $table->increments('notification_user');
                $table->integer('user_id');
                $table->string('notification_user_phone')->nullable();
                $table->string('notification_user_email')->nullable();
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['user_id'],'notificationuesrs_index');
            });
        }

        if(!Schema::hasTable('notification_setting')){
            Schema::create('notification_setting', function (Blueprint $table) {
                $table->increments('notification_setting_id');
                $table->string('notification_setting_content',255);
                $table->integer('page_id');
                $table->string('notification_setting_trigger_type');
                $table->longText('notification_setting_options')->nullable();
                $table->boolean('notification_setting_mail');
                $table->boolean('notification_setting_phone');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                // $table->index(['notification_setting_target','notification_setting_target_type','page_id']);
                // $table->index(['page_id']);
            });
        }

        if(!Schema::hasTable('notification_target')){
            Schema::create('notification_target', function (Blueprint $table) {
                $table->increments('notification_target_id');
                $table->integer('notification_setting_id');
                $table->integer('notification_target');
                $table->string('notification_target_type');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                // $table->index(['notification_target','notification_targe_type']);
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
            Schema::dropIfExists('notifications');
            Schema::dropIfExists('notification_user');
            Schema::dropIfExists('notification_target');
            Schema::dropIfExists('notification_setting');
        }else{
            Notification::where('created_by',-1)->delete();
            // NotificationUser::where('created_by',-1)->delete();
            // NotificationTarget::where('created_by',-1)->delete();
            NotificationSetting::where('created_by',-1)->delete();
        }
    }
}
