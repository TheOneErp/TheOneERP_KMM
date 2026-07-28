<?php
use App\Models\Parameters;
namespace App\Console\Commands;
use Illuminate\Console\Command;
class update1 extends Command
{
    // 取名你要下的指令名稱，可以和 class name 不同
    protected $signature = 'update:num1';
    // 簡單的功能描述
    protected $description = 'Update num1 Sum';
    public function __construct()
    {
        parent::__construct();
    }
    // 這個命要要執行的內容
    public function handle()
    {
        $pra_data = Parameters::find(1);
		if($pra_data){
			$pra_data->delete();
			return redirect("/parameters")->withSuccess('刪除成功');
		}else{
			return redirect("/parameters")->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
		}
    }
   
    /*protected function mySum($x, $y) 
    {
       $pra_data = Parameters::find(1);
		if($pra_data){
			$pra_data->delete();
			return redirect("/parameters")->withSuccess('刪除成功');
		}else{
			return redirect("/parameters")->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
		}
    }*/
}
?>