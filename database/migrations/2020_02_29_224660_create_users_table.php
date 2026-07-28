<?php

use App\Models\Page;
use App\Models\Form;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use Staudenmeir\LaravelMigrationViews\Facades\Schema as View;

use App\Database\Migrations\Migration;

use App\Models\User;
use App\Models\UserAgent;
use App\Models\UserAgentPage;
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('users')){
            Schema::create('users', function (Blueprint $table) {
                $table->increments('user_id');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('name');
                $table->boolean('user_disabled')->default(false);
                $table->string('user_remarks')->nullable()->default(null);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->rememberToken();
                $table->timestamps();

                $table->index(['user_id','username', 'name'],'users_index');
            });
        }

        if(!Schema::hasTable('user_agent')){
            Schema::create('user_agent', function (Blueprint $table) {
                $table->increments('user_agent_id');
                $table->integer('user_id');
                $table->boolean('user_agent_enabled')->default(false);
                $table->dateTime('user_agent_enabled_at')->nullable();
                $table->dateTime('user_agent_disabled_at')->nullable();
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['user_agent_id','user_id'],'useragents_index');
            });
        }

        if(!Schema::hasTable('user_agent_page')){
            Schema::create('user_agent_page', function (Blueprint $table) {
                $table->bigIncrements('user_agent_page_id');
                $table->integer('user_agent_id');
                $table->integer('page_id');
                $table->string('user_agent_target_type',10)->nullable();
                $table->integer('user_agent_target_id')->nullable();
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['user_agent_page_id','user_agent_id', 'page_id'],'useragentpages_index');
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
            Schema::dropIfExists('users');
            Schema::dropIfExists('user_agent');
            Schema::dropIfExists('user_agent_page');
        }else{
            User::where('created_by',-1)->delete();
            /* UserAgent::where('created_by',-1)->delete();
            UserAgentPage::where('created_by',-1)->delete(); */
        }
        $userAgentPageId = Page::where("page_code","SY_USER_AGENT_PAGE")->get()->pluck('page_id')->first();
        $userAgentFormId = Form::where("page_id",$userAgentPageId)->get()->pluck('form_id')->first();
        View::dropViewIfExists("SY_USER_AGENT_PAGE_{$userAgentFormId}_user_agent_target_id");
    }
}
