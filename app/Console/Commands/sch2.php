<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\Parameters;
use File;

class sch2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[測試] 寫入資料庫';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        DB::table('log')
//         ->insert([
//          'time' => date('Y-m-d H:i:s')
//         ]);
//        $id = 6;
//        $pra_data = Parameters::find($id);
//        if($pra_data){
//            $pra_data->delete();
//            return redirect("/parameters")->withSuccess('刪除成功');
//        }else{
//            return redirect("/parameters")->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
//        }
        // 檔案紀錄在 storage/test.log
        $log_file_path = storage_path('test.log');

        // 記錄當時的時間
        $log_info = [
            'date'=>date('Y-m-d H:i:s')
        ];

        // 記錄 JSON 字串
        $log_info_json = json_encode($log_info) . "\r\n";

        // 記錄 Log
        File::append($log_file_path, $log_info_json);
    }
}
