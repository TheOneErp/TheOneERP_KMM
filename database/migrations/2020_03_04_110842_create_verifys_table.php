<?php

use App\Models\Page;
use App\Models\Form;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use Staudenmeir\LaravelMigrationViews\Facades\Schema as View;

use App\Database\Migrations\Migration;

use App\Models\Verify;

class CreateVerifysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('verifies')){
            Schema::create('verifies', function (Blueprint $table) {
                $table->increments('verify_id');
                $table->integer('page_id');
                $table->string('verify_remarks')->nullable();
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['verify_id','page_id'],'verifies_index');
            });
        }

        if(!Schema::hasTable('verify_level')){
            Schema::create('verify_level', function (Blueprint $table) {
                $table->increments('verify_level_id');
                $table->integer('verify_id');
                $table->integer('verify_level');
                $table->integer('verify_target_id');
                $table->string('verify_target_type', 10);
                $table->integer('verify_population');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['verify_level_id','verify_id','verify_target_id','verify_target_type'],'verifylevels_index');
            });
        }

        if(!Schema::hasTable('verify_condition')){
            Schema::create('verify_condition', function (Blueprint $table) {
                $table->increments('verify_condition_id');
                $table->integer('verify_level_id');
                $table->integer('verify_condition_group');
                // $table->integer('verify_logic_group');
                $table->string('verify_logical', 5);
                $table->string('field_code',15);
                $table->string('verify_comparison', 5);
                $table->string('verify_value', 50)->nullable();
                // $table->string('verify_operator', 50);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['verify_condition_id','verify_level_id'],'verifyconditions_index');
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
        /* Schema::dropIfExists('verifies');
        Schema::dropIfExists('verify_level');
        Schema::dropIfExists('verify_condition'); */

        if($this->ALL_ROLLBACK){
            Schema::dropIfExists('verifies');
            Schema::dropIfExists('verify_level');
            Schema::dropIfExists('verify_condition');
        }else{
            Verify::where('created_by',-1)->delete();
        }
        $verifyLevelPageId = Page::where("page_code","SY_VERIFY_LEVEL")->get()->pluck('page_id')->first();
        $verifyLevelFormId = Form::where("page_id",$verifyLevelPageId)->get()->pluck('form_id')->first();
        View::dropViewIfExists("SY_VERIFY_LEVEL_{$verifyLevelFormId}_verify_target_id");
    }
}
