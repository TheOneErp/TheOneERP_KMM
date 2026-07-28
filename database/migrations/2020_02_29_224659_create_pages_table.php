<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;
class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 頁面
        if(!Schema::hasTable('pages')){
            Schema::create('pages', function (Blueprint $table) {
                $table->increments('page_id');
                $table->string('page_code', 50)->unique();
                $table->integer('page_module')->default(0);
                $table->string('page_controller',60)->nullable()->default(null);
                $table->string('page_list_template')->nullable()->default(null);
                $table->string('page_form_template')->nullable()->default(null);
                $table->boolean('page_visible')->default(true);
                $table->integer('page_order')->default(0);
                $table->boolean('page_readonly')->default(false);
                $table->longText('page_options')->nullable()->default(null);
                $table->string('page_remarks')->nullable()->default(null);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['page_id','page_code','page_visible','page_readonly'],'pages_index');
            });
        }

        // 表單
        if(!Schema::hasTable('forms')){
            Schema::create('forms', function (Blueprint $table) {
                $table->increments('form_id');
                $table->integer('page_id');
                $table->integer('form_order')->default(0);
                $table->string('form_type');
                $table->integer('ref_page_id')->nullable()->default(null);
                $table->integer('form_parent')->nullable()->default(null);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['form_id','page_id','form_type'],'forms_index');
            });
        }

        // 欄位
        if(!Schema::hasTable('fields')){
            Schema::create('fields', function (Blueprint $table) {
                $table->bigIncrements('field_id');
                $table->integer('form_id');
                $table->string('field_code', 60);
                $table->string('field_type');
                $table->longText('field_rule')->nullable()->default(null);
                $table->integer('field_order')->default(0);
                $table->string('field_default_value')->nullable()->default(null);
                $table->boolean('field_required')->default(false);
                $table->boolean('field_readonly')->default(false);
                $table->boolean('field_show_on_form')->default(true);
                $table->boolean('field_show_on_list')->default(true);
                $table->longText('field_options')->nullable()->default(null);
                $table->string('field_remarks', 4000)->nullable()->default(null);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['form_id','field_code','field_type'],'fields_index');
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
            Schema::dropIfExists('pages');
            Schema::dropIfExists('forms');
            Schema::dropIfExists('fields');
        }else{
            Page::where('created_by',-1)->delete();
            /* Form::where('created_by',-1)->delete();
            Field::where('created_by',-1)->delete(); */
        }
    }
}
