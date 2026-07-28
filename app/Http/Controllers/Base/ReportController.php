<?php
namespace App\Http\Controllers\Base;

use Exception;
use Carbon\Carbon;
use PHPJasper\PHPJasper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Utils\CSV;
use App\Utils\Json;
use App\Utils\FileUtil;
use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\PermissionUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    protected function filter(array $filters = [], $pageData){
        $result = [
            "success" => true,
            "filters" => [],
            "messages" => [],
        ];
        if(!empty($filters)){
            $toValidate = [];
            $rules = [];
            $attributes = [];
            $validation_message = $pageData["validation"];
            foreach($pageData["forms"][0]["fields"] as $key => $setting){
                if(!(isset($setting["field_options"]["system_field"]) && $setting["field_options"]["system_field"])){
                    $toValidate[$key] = $filters["data"][$key];
                    $pageRuleArray = $setting['field_rule'];
                    $rules[$key] = ValidationUtil::generateFieldRuleObject($pageRuleArray, $setting, $filters, [
                        'update' => false,
                        'required' => $setting['field_required']
                    ]);
                    $attributes[$key] = $setting['translation'];
                }
            }

            $validation = ValidationUtil::validationData($toValidate, $rules, $validation_message, $attributes);

            if($validation["passed"]){
                $result["filters"] = $filters["data"];
            }else{
                $result["success"] = false;
                $result["messages"] = $validation["errors"];
            }
        }

        return $result;
    }

    protected function build(string $jasperPath){
        $jasper = new PHPJasper;
        $jrxmlFile = $jasperPath.".jrxml";
        $jasperFile = $jasperPath.".jasper";
       // dd($jrxmlFile,$jasperFile);
        $compiler = function() use ($jasper, $jrxmlFile){
            try {
                $jasper->compile($jrxmlFile)->execute();
            } catch (\Throwable $th) {
                throw new Exception("Error when execute command: {$jasper->compile($jrxmlFile)->output()}");
            }
        };

        if(file_exists($jrxmlFile)){
            if(!file_exists($jasperFile)){
                $compiler();
            }else{
                $jrxmlTime = (new Carbon(filemtime($jrxmlFile)))->setTimezone("Asia/Taipei");
                $jasperTime = (new Carbon(filemtime($jasperFile)))->setTimezone("Asia/Taipei");
                if($jrxmlTime > $jasperTime){
                    $compiler();
                }
            }
        }else{
        $result = [
            "status" => false,
            "messages" => []
        ];

        $result["messages"][] = TranslationUtil::getTranslationByCode("report.error.jrxml_not_found");
        // or: array_push($result["messages"], ...);

        return response()->json($result, 404);
        }

        return $jasperFile;
    }

    protected function universal(){}

    protected function setCSVOutputWithBOM(string $path){
        $csv = new CSV($path);
        $rows = $csv->getAllRows();
        $csv->writeDatas($rows);
    }

    public function output(Request $request, $page_id){
        $result = [
            "status" => true,
            "messages" => [],
            "format" => $request->format,
            "file" => "",
        ];
        $reportData = [];

        // Check permission
        $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
        if (!$permission['permission_read'])
            abort(403);

        // Get page data
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }
        // dd($pageData);

        $isCustom = $pageData["page"]["page_list_template"] === "report";
        // Inject functions
        $class = 'App\\Http\\Inject\\Reports\\' . $pageData['path'];
        if($isCustom){
            if(!class_exists($class)){
                abort(404);
            }
        }

        if($isCustom){
            $filterCheck = $this->filter(DataUtil::decodeJSONToArray($request->filters), $pageData);
            if($filterCheck["success"]){
                $reportData = (new $class($filterCheck["filters"]))->datas;
            }else{
                $result["status"] = false;
                $result["messages"] = $filterCheck["messages"];
                return response()->json($result, 400);
            }
        }else{
            $select = $request->select;
            $ignoreField = ["id", "parent_id", "data_options"];
            $tableNames = PageUtil::getTableNameByPageId($page_id);
            if(isset($tableNames[0])){
                $reportData = ["data"=>[]];
                $fields = $pageData["forms"][0]["fields"];
                usort($fields, function ($a, $b){
                    return $a["field_order"] - $b["field_order"];
                });

                foreach(DB::table($tableNames[0])->get() as $rowIndex => $row){
                    foreach($fields as $field){
                        if( $field["field_type"] !== "button" ){
                            $value = $row->{$field["field_code"]};
                            if($field["field_type"] === "integer") $value = (int)$value;
                            else if($field["field_type"] === "decimal"){
                                $value = number_format($value, $field["field_options"]["decimal"]["decimal"], '.', '');
                            }

                            if((empty($select) || (!empty($select) && in_array($field["field_code"], $select))) && !in_array($field["field_code"], $ignoreField)){
                                array_push($reportData["data"],[
                                    "title" => $pageData["page"]["translation"],
                                    "column" => $field["translation"],
                                    "row" => $rowIndex+1,
                                    "value" => $value
                                ]);
                            }
                            }

                    }
                }
            }
        }

        $ignoreWidth = ["xlsx","csv"];
        $jasper = new PHPJasper;

        $jasperPath = resource_path("reports/universal");
        if (in_array($request->format, $ignoreWidth)) {
            $jasperPath .= "_ignore_width";
        }
        if ($isCustom) {
            $jasperPath = resource_path("reports/" . str_replace('\\', '/', $pageData['path']));
        }

        // === IMPORTANT: Handle error response from build() ===
        $buildResult = $this->build($jasperPath);

        if ($buildResult instanceof \Illuminate\Http\JsonResponse) {
            return $buildResult;   // ← Return 404 immediately
        }

        $jasperFile = $buildResult;
        /* $jrxmlFile = $jasperPath.".jrxml";
        $jasperFile = $jasperPath.".jasper";
        if(!file_exists($jasperFile)){
            if(file_exists($jrxmlFile)){
                try {
                    $jasper->compile($jrxmlFile)->execute();
                } catch (\Throwable $th) {
                    throw new Exception("Error when execute command: {$jasper->compile($jrxmlFile)->output()}");
                }
            }else{
                $result["status"] = false;
                array_push($result["messages"], TranslationUtil::getTranslationByCode("report.error.jrxml_not_found"));
                return response()->json($result,404);
            }
        } */

        $json = new Json($reportData, true);

        // Check and make the folder
        $reportsFolder = public_path("reports");
        $outputFolder = public_path("reports/{$request->format}");
        FileUtil::newFolder($reportsFolder, $outputFolder);
        foreach (scandir($outputFolder) as $file){
            $path = "$outputFolder/$file";
            if(pathinfo($path, PATHINFO_EXTENSION) === $request->format){
                $fileTime = (new Carbon(filemtime($path)))->setTimezone("Asia/Taipei");
                if(Carbon::now()->diffInHours($fileTime) > 2){
                    unlink($path);
                }
            }
        }
        $result["file"] = url("reports/{$request->format}/{$pageData['page']['page_code']}_{$json->getId()}.$request->format");
        $output = "$outputFolder/{$pageData['page']['page_code']}_{$json->getId()}";
        $options = [
            'format' => [$request->format],
            'db_connection' => [
                'driver' => 'json',
                'data_file' => $json->getPath(),
            ]
        ];
        // dd($json->getPath());
        // dd($jasperFile, $output, $options);
        try {
            /* if (!file_exists($json->getPath())) {
                dd("ssss");
            }else{
                dd($json->getPath());
            } */

            $jasper->process($jasperFile, $output, $options)->execute();
        } catch (\Throwable $th) {
            throw new Exception("Error when execute command: {$jasper->process($jasperFile, $output, $options)->output()}");
        }

        if($request->format === 'csv'){
            $this->setCSVOutputWithBOM("$output.csv");
        }
        // dd($result);
        return response()->json($result, 200);
    }

    public function export($format,$jasperName,$reportData=array()){
		$result = [
            "status" => true,
            "messages" => [],
            "format" => $format,
            "file" => "",
		];

		$jasper = new PHPJasper;
		// 放置報表的位置
		$jasperPath = resource_path("reports\other\\$jasperName");		
		// dd($jasperPath);
		// $reportClass = new ReportController;
		$buildResult = $this->build($jasperPath);
        if ($buildResult instanceof \Illuminate\Http\JsonResponse) {
            return $buildResult;
        }
        $jasperFile = $buildResult;
		$json = new Json($reportData, true);
		$reportsFolder = public_path("reports");
		$outputFolder = public_path("reports/{$format}");
		
        FileUtil::newFolder($reportsFolder, $outputFolder);
        foreach (scandir($outputFolder) as $file){
            $path = "$outputFolder/$file";
            if(pathinfo($path, PATHINFO_EXTENSION) === $format){
                $fileTime = (new Carbon(filemtime($path)))->setTimezone("Asia/Taipei");
                if(Carbon::now()->diffInHours($fileTime) > 2){
                    unlink($path);
                }
            }
		}
		$result["file"] = url("reports/{$format}/{$jasperName}_{$json->getId()}.$format");
		$output = "$outputFolder/{$jasperName}_{$json->getId()}";
        $options = [
            'format' => [$format],
            'db_connection' => [
                'driver' => 'json',
                'data_file' => $json->getPath(),
            ]
		];
		try {
            $jasper->process($jasperFile, $output, $options)->execute();
        } catch (\Throwable $th) {
            throw new Exception("Error when execute command: {$jasper->process($jasperFile, $output, $options)->output()}");
        }
        return $result;

        // return response()->json($result, 200);
	}
}
