<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Database\Migrations\Migration;

use App\Models\Language;

class AddPageStayToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 讓現有(已跑過 migrate 的)資料庫也能補上「保存後停留在頁面」欄位。
     * 這支 migration 是額外新增的，不影響原本的 create_pages_table /
     * insert_basic_data migration(那兩支是給全新安裝用的)。
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pages') && !Schema::hasColumn('pages', 'page_stay')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->boolean('page_stay')->default(false)->after('page_visible');
            });
        }

        // 補上欄位翻譯(僅補繁中，與織然 TheOneERP_YO 的作法一致)
        if (Schema::hasTable('translation') && Schema::hasTable('languages')) {
            $zhTW = Language::where('language_code', 'zh-TW')->first();
            if ($zhTW != null) {
                $exists = DB::table('translation')
                    ->where('language_id', $zhTW->language_id)
                    ->where('translation_type', 'field')
                    ->where('translation_code', 'page_stay')
                    ->whereNull('form_id')
                    ->exists();
                if (!$exists) {
                    DB::table('translation')->insert([
                        'language_id' => $zhTW->language_id,
                        'translation_type' => 'field',
                        'translation_code' => 'page_stay',
                        'form_id' => null,
                        'translation' => '保存後是否停留在頁面',
                        'created_by' => -1,
                        'updated_by' => -1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->ALL_ROLLBACK) {
            if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'page_stay')) {
                Schema::table('pages', function (Blueprint $table) {
                    $table->dropColumn('page_stay');
                });
            }
            DB::table('translation')->where('translation_type', 'field')->where('translation_code', 'page_stay')->delete();
        }
    }
}
