<?php
namespace App\Http\Controllers\API\GA\GA2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class GA201Controller extends Controller{
	static public function checkimg(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
		$data = DataUtil::convertToArray(json_decode($request->getContent()));
		else
		abort(400);
		$imagename = $data['image'];
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imagename);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        if(curl_exec($ch)!==FALSE){
            $success=["text" => "Exits"];
        }else{
            $success=["text" => "noExits"];
        }


		return $success;
	}
    static public function gettype(Request $request){
        $getitem= DB::table('GA101_5223')
                  ->where('fixed',1)
                  ->get();
                  
                  return $getitem;     
    }
}