<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Schedule;
class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('schedules')){
            Schema::create('schedules', function (Blueprint $table) {
                $table->increments('schedule_id');
                $table->string('schedule_code', 50);
                $table->string('schedule_function', 50);
                $table->string('schedule_remarks', 255)->nullable();
                $table->boolean('schedule_active')->default(false);
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
            Schema::dropIfExists('schedules');
        }else{
            Schedule::where('created_by',-1)->delete();
        }
    }
}
