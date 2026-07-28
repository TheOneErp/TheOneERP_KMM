<?php

namespace App\Http\Controllers\Base;

use DB;
use App\Http\Controllers\Controller;

use App\Utils\DataUtil;
use App\Utils\SessionUtil;
use App\Utils\PermissionUtil;
use App\Utils\TranslationUtil;
use App\Utils\UserUtil;
use App\Models\Translation;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Http\Request;
class IndexController extends Controller
{
    public function index(Request $req)
    {
        $allIndexLanguage = Translation::where("translation_code", "index")->orWhere("translation_code", "main_content")->get();
        $defaultLanguage = $allIndexLanguage->where("language_id", 1);
        $currentLanguage = $allIndexLanguage->where("language_id", SessionUtil::getLanguageID());
        $languages = [];
        foreach ($defaultLanguage as $item) {
            if ($currentLanguage->where("translation_code", $item->translation_code)->isEmpty()) {
                $languages[$item->translation_code] = $item->translation;
            } else {
                $languages[$item->translation_code] = $currentLanguage->where("translation_code", $item->translation_code)->pluck("translation")->first();
            }
        }
   
        //dd($shipmentsArr);
  
        //$years = DB::table('BA201_40')
        //->select('advanceday')
        //->get()
        //->groupBy(function($date) {
        //    return Carbon::parse($date->advanceday)->format('Y'); // grouping by months
        //});
        //$yearcount = [];
        //foreach ($years as $key => $value) {
           // $yearcount[(int)$key] = count($value);
        //}
        //$Rank = 1;
        //$leaderdata=[];
        //$month=date("m");
        //$lastmonth=$month-1;
        //dd( $lastmonth);
        //$leaderdata=DB::table('BA202_52 as a')
        //->leftjoin('BA202_53 as b', 'b.parent_id','=','a.id')
        //->whereYear('ship_date', '=',  date("Y"))
        //->whereMonth('ship_date', '=', $lastmonth)
        //->selectRaw('product_name,sum(body_num) as sum') 
        //->groupBy('product_name')
        //->selectRaw('RANK() OVER(ORDER BY sum(body_num) DESC) as rank,product_name')
        //->limit(10)
        //->get();

        //$leaderdata2=array_slice($leaderdata,0,9);
         //dd($shipmentsArr);
        
        return view("index")->with("languages", $languages);
    }

    public static function menu()
    {
        $pages = DataUtil::convertToArray(
            Page::visible()
                ->orderBy('page_module', 'ASC')
                ->orderBy('page_order', 'ASC')->get()->toArray()
        );
        $translation = DataUtil::convertToArray(Translation::where('translation_type', 'page')->get()->toArray());
        $languageID = SessionUtil::getLanguageID();

        $menu = [];
        $generateMenuItem = function ($pageItem) use ($translation, $languageID) {
            return [
                "page_id" => $pageItem['page_id'],
                "page_text" => TranslationUtil::getTranslationInArray($translation, $pageItem['page_code'], $languageID),
                "page_module" => $pageItem['page_module'],
                "page_code" => $pageItem['page_code'],
                "page_order" => $pageItem['page_order']
            ];
        };

        $DeveloperToolsPageIDs = [];

        foreach ($pages as $page) {
            if($page['page_code'] == "DT") $DeveloperToolsPageIDs[] = $page['page_id'];
            if(in_array($page['page_id'],$DeveloperToolsPageIDs) || in_array($page['page_module'],$DeveloperToolsPageIDs)){
                $DeveloperToolsPageIDs[] = $page['page_id'];
                if(UserUtil::isRoot()){
                    array_push($menu, $generateMenuItem($page));
                }
            }else if(UserUtil::isAdmin()){
                array_push($menu, $generateMenuItem($page));
            }else if (PermissionUtil::getCurrentUserPagePermission($page['page_id'])['permission_read']) {
                array_push($menu, $generateMenuItem($page));
            }
        }

        // Build modules
        foreach ($menu as $page) {
            $showModule = function ($page) use (&$menu, $pages, &$showModule, $generateMenuItem) {
                $moduleID = $page['page_module'];
                if ($moduleID != 0 ) {
                    $module = DataUtil::arraySearch($menu, function ($menuItem) use ($moduleID) {
                        return ($menuItem['page_id'] == $moduleID);
                    });
                    if ($module == null) {
                        $pageItem = DataUtil::arraySearch($pages, function ($pageItem) use ($moduleID) {
                            return ($pageItem['page_id'] == $moduleID);
                        });
                        if ($pageItem != null) {
                            $pageItem = $generateMenuItem($pageItem);
                            array_push($menu, $pageItem);
                            $module = $pageItem;
                        }
                    }
                    if ($module != null)
                        $showModule($module);
                }
            };
            $showModule($page);
        }
        return response()->json($menu,200);
    }
    //public function chart(Request $request)
    //{
       // if($request->ajax())
     //{
        //$orders = DB::table('BA201_40')
        //->whereYear('advanceday', '=', date($request->year))
        //->get()
        //->groupBy(function($date) {
            //return Carbon::parse($date->advanceday)->format('m'); 
       //});
        //$ordercount = [];
        //$orderArr = [];
        
        //foreach ($orders as $key => $value) {
            //$ordercount[(int)$key] = count($value);
        //}
        
        //for($i = 1; $i <= 12; $i++){
            //if(!empty($ordercount[$i])){
                //$orderArr[$i] = $ordercount[$i];    
            //}else{
                //$orderArr[$i] = 0;    
           // }
        //}
  //}
       //return response()->json($orderArr);
   //}

    }


