<?php
namespace App\Http\Controllers\System;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

use App\Utils\Json;
use App\Utils\Excel;
use App\Utils\PageUtil;
use App\Utils\DataUtil;
use App\Utils\UserUtil;
use App\Utils\FileUtil;
use App\Utils\VerifyUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;

use App\Http\Controllers\Controller;
use App\Http\Controllers\System\VerifyController;
class DevelopingToolsController extends Controller{
    public function insert_sql(Request $request, $data = null) {
        if(!UserUtil::isRoot()){
            abort(403);
        }
        if(is_null($data)){
            if (ValidationUtil::isJSONString($request->getContent()))
                $data = DataUtil::convertToArray(json_decode($request->getContent()));
            else
                abort(400);
        }

        // dd($data);
        $result = [
            "status" => true,
            "errors" => [],
            "commands" => []
        ];
        // $error = [];
        if($data['mode'] == "page_id"){
            $pageData = PageUtil::getPageData($data['page_id']);
            $page_code = $pageData["page"]["page_code"];
            if(explode("_",$page_code)[0] === "SY" || explode("_",$page_code)[0] === "DT"){
                $data["mode"] = "table_name";
                $data["table_name"] = '';
                foreach(explode("_",$page_code) as $key => $value){
                    if($key > 0){
                        if($data["table_name"] !== ''){
                            $data["table_name"] .= '_';
                        }
                        $data["table_name"] .= $value;
                    }
                }
                $data["table_name"] = strtolower ($data["table_name"]);
                return $this->insert_sql($request,$data);
            }
            foreach($pageData["forms"] as $form){
                // $table_name = $form["form_id"]
            }
            dd($pageData);
        }else if($data['mode'] == "table_name"){
            $q = DB::table($data['table_name']);
            try {
                $q = DatabaseUtil::groupExpression($q,$data["where"]);
                // dd($q->toSql());
                $allData = $q->get();
                $ignore = explode(",",$data["ignore"]);
                foreach($allData as $row){
                    $columns = "";
                    $values = "";
                    foreach ($row as $column => $value){
                        $value = is_string($value) ? "'$value'" : $value;
                        if(!empty($value) && array_search($column,$ignore) === false){
                            $columns = DataUtil::unionString($columns,$column);
                            $values = DataUtil::unionString($values,$value);
                        }
                    }
                    $temp = "INSERT INTO {$data['table_name']} ({$columns}) VALUES ($values)";
                    array_push($result["commands"],$temp);
                }

                // dd($result);
            } catch (\Throwable $th) {
                $result["status"] = false;
                array_push($result["errors"],$th->getMessage());
            }
            // dd($q->toSql());
        }

        return response()->json($result,200);
    }

    public function test(){
        // $json = new Json('{"A": 1, "B":[1,2,3,"4"]}');
        $json = new Json(public_path("test_copy.json"));
        dump($json);
        $jsonNew = $json->update(["A" => 5, "B" => ["C" => 6]])->saveAs(public_path("test_save_as.json"));

        // $jsonNew = new Json(public_path("test_save_as.json"));
        dd($jsonNew);
    }
}

?>
