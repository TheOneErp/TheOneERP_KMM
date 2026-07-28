<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

use App\Database\Migrations\Migration;

use App\Models\Log;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('logs')){
            Schema::create('logs', function (Blueprint $table) {
                $table->bigIncrements('log_id');

                $table->integer('page_id');
                $table->integer('form_id')->nullable();

                $table->bigInteger('id');
                $table->bigInteger('parent_id')->nullable();

                $table->integer('action'); // 1 = ADD 2 = UPDATE 3 = DELETE
                $table->longText('data');

                $table->timestamp('created_at')->useCurrent();
                $table->integer('created_by');
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
        // Schema::dropIfExists('logs');
    }
}
