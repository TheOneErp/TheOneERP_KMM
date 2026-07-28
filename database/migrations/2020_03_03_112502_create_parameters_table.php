<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Parameter;

class CreateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('parameters')){
            Schema::create('parameters', function (Blueprint $table) {
                $table->increments('parameter_id');
                $table->string('parameter_code', 50);
                $table->string('parameter_value', 50);
                $table->boolean('parameter_deletable')->default(true);
                $table->string('parameter_remarks', 255);
                $table->integer('created_by')->default(-1);
                $table->integer('updated_by')->default(-1);
                $table->timestamps();
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
            Schema::dropIfExists('parameters');
        }else{
            Parameter::where('created_by',-1)->delete();
        }
    }
}
