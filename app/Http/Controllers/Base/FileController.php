<?php

namespace App\Http\Controllers\Base;

use App\Utils\Excel;
use App\Utils\FileUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
// TODO : 權限 / 驗證

class FileController extends Controller
{
    public function download($fieldID, $id, $filename)
    {
        $filename = str_replace('/', '', $filename);
        $filename = str_replace('\\', '', $filename);
        //TODO:Check permission
        if (FileUtil::checkFileExistsByFilename($fieldID, $id, $filename) && is_numeric($fieldID))
            return response()->download(storage_path("app/uploads/$fieldID/$id-$filename"), $filename);
        else
            abort(404);
    }

    public function parseRequestExcel(Request $request)
    {
        $file = $request->file;
        $extensionValidation = Excel::checkExtension($file);
        $errorMessages = TranslationUtil::getTranslationByCode(["file.error.method_not_found", "file.error.extension_wrong"]);
        $result = [
            "success" => true,
            "messages" => [],
            "data" => null
        ];
        if ($extensionValidation) {
            $excel = new Excel($file);
            $method = empty($request->method) || $request->method === "getAllSheets" ? "getAllSheetData" : $request->method;
            $parameters = is_null($request->parameters) ? [] : json_decode($request->parameters);
            if (method_exists($excel, $method)) {
                $result["data"] = call_user_func_array(array($excel, $method), $parameters);
                return response()->json($result, 200);
            } else {
                $result["success"] = false;
                $result["messages"][] = $errorMessages["file.error.method_not_found"];
                return response()->json($result, 400);
            }
        } else {
            $result["success"] = false;
            $result["messages"][] = $errorMessages["file.error.extension_wrong"];
            return response()->json($result, 400);
        }
    }

    public function parseRequestCSV(Request $request)
    {
        dd("this is CSV.");
    }
}
