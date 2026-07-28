<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Language;
use App\Models\Translation;
class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('languages')){
            Schema::create('languages', function (Blueprint $table) {
                $table->increments('language_id');
                $table->string('language_code');
                $table->string('language_name');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['language_id','language_code'],'languages_index');
            });
        }

        if(!Schema::hasTable('translation')){
            Schema::create('translation', function (Blueprint $table) {
                $table->bigIncrements('translation_id');
                $table->integer('language_id');
                $table->string('translation_type');
                $table->string('translation_code');
                $table->integer('form_id')->nullable()->default(null);
                $table->string('translation');
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();

                $table->index(['language_id','translation_type','translation_code','form_id'],'translations_index');
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
            Schema::dropIfExists('languages');
            Schema::dropIfExists('translation');
        }else{
            Language::where('created_by',-1)->delete();
            Translation::where('created_by',-1)->delete();
        }
    }
}
