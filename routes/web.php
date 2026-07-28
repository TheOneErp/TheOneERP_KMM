<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Load js as blade
View::addExtension("blade.js", "blade");

// Auth functions
Route::get('login', 'Auth\LoginController@showLoginForm')->name('system.auth.login.get');
Route::post('login', 'Auth\LoginController@login')->name("system.auth.login.post");
Route::get('logout', 'Auth\LoginController@logout')->name('system.auth.logout.get');
Route::post('logout', 'Auth\LoginController@logout')->name('system.auth.logout.post');
Route::get('api/system/auth/languages', 'Auth\LoginController@getLoginTranslation')->name('system.auth.translation.get');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/', 'Base\IndexController@index')->name("index");
    Route::get('test', 'System\DevelopingToolsController@test');
    Route::get('getcal', 'FullCalenderController@index')->name('inject.getcal');
    Route::post('action', 'FullCalenderController@action')->name('inject.action');
    // Base
    Route::group(['prefix' => 'api'], function () {

        Route::group(['prefix' => 'system'], function () {
            Route::get('menu', 'Base\IndexController@menu')->name("system.menu");

            Route::group(['prefix' => 'translation'], function () {
                Route::get('query', 'System\TranslationController@query')->name('system.translation.query.get');
                Route::post('save', 'System\TranslationController@save')->name('system.translation.save.post');
            });

            Route::group(['prefix' => 'page'], function () {
                Route::get('{page_id}/filter', 'Base\PageController@filter')->name('system.page.filter.get');
                Route::post('{page_id}/save', 'Base\PageController@save')->name('system.page.save.post');
                Route::get('{page_id}/view/{id?}', 'Base\PageController@view')->name('system.page.view.get');
                Route::delete('{page_id}/delete/{id?}', 'Base\PageController@delete')->name('system.page.delete.delete');
            });

            Route::group(['prefix' => 'pages'], function (){
                Route::get('getPageFields/{page_id}', 'System\PageController@getPageFields')->name('system.reference.getPageFields');
                Route::post('savePageOrder', 'System\PageController@page_reorder')->name('system.pages.reorder');
            });

            Route::group(['prefix' => 'reference'], function (){
                Route::get('getReferenceData/{field_id}', 'Base\ReferenceController@getReferenceData')->name('system.reference.getReferenceData');
            });

            Route::group(['prefix' => 'log'], function (){
                Route::get('filter', 'System\LogController@filter')->name('system.log.filter');
                Route::get('view/{logID?}', 'System\LogController@view')->name('system.log.view');
            });

            Route::group(['prefix' => 'verify'], function (){
                Route::get('{type}/{pageId}/{dataId}/{userId?}', 'System\VerifyController@dataVerify')->name('system.verify');
                // Route::get('execute/{pageId}/{dataId}/{userId?}', 'System\VerifyController@executeDataVerify')->name('system.verify.execute');
                // Route::get('return/{pageId}/{dataId}/{userId?}', 'System\VerifyController@returnDataVerify')->name('system.verify.return');
                // Route::get('init/{pageId}/{dataId}/{userId?}', 'System\VerifyController@initDataVerify')->name('system.verify.init');
            });

            Route::group(['prefix' => 'file'], function () {
                Route::post('excel', 'Base\FileController@parseRequestExcel')->name('system.excel');
            });

            Route::group(['prefix' => 'report'], function () {
                Route::post('output/{id}', 'Base\ReportController@output')->name('system.report.output');
            });
        });

		Route::group(['prefix' => 'inject','namespace' =>'API'], function () {
			Route::group(['namespace' =>'AA'], function () {
				//AA202
				Route::post('changeUnitCode', 'AA2\AA202Controller@changeUnitCode')->name('inject.changeUnitCode');
			});

			Route::group(['namespace' =>'BA'], function () {
                //BA105
                Route::post('getaddr', 'BA1\BA105Controller@getaddr')->name('inject.getaddr');
				//BA201
                Route::post('transToWork', 'BA2\BA201Controller@transToWork')->name('inject.transToOrder');
				Route::post('transToOrder', 'BA2\BA207Controller@transToOrder')->name('inject.transToOrder');
				Route::post('checkExisInWork', 'BA2\BA201Controller@checkExisInWork')->name('inject.checkExisInWork');
                Route::post('cited', 'BA2\BA201Controller@cited')->name('inject.cited'); //表身id被引用
                Route::post('getclientcurrencytax', 'BA2\BA201Controller@getclientcurrencytax')->name('inject.getclientcurrencytax'); //表身id被引用
				//BA202
				Route::post('getCustomerOrder', 'BA2\BA202Controller@getCustomerOrder')->name('inject.getCustomerOrder');
                Route::post('getCustomerOrder1', 'BA2\BA202Controller@getCustomerOrder1')->name('inject.getCustomerOrder1');
                Route::post('getProduct', 'BA2\BA202Controller@getProduct')->name('inject.getProduct');
				Route::post('checkExisInShipBack', 'BA2\BA202Controller@checkExisInShipBack')->name('inject.checkExisInShipBack');
				Route::post('getBucketProduct', 'BA2\BA202Controller@getBucketProduct')->name('inject.getBucketProduct');
                Route::post('addship', 'BA2\BA202Controller@addship')->name('inject.addship');
                Route::post('printShip', 'BA2\BA202Controller@printShip')->name('inject.printShip');
                Route::post('printShip1', 'BA2\BA202Controller@printShip1')->name('inject.printShip1');
                Route::post('printShip2', 'BA2\BA202Controller@printShip2')->name('inject.printShip2');
                Route::post('printOrder3', 'BA2\BA201Controller@printOrder3')->name('inject.printOrder3');
                Route::post('printOrder2', 'BA2\BA201Controller@printOrder2')->name('inject.printOrder2');
                Route::post('printOrder1', 'BA2\BA201Controller@printOrder1')->name('inject.printOrder1');
                Route::post('printOrder', 'BA2\BA207Controller@printOrder')->name('inject.printOrder');
				//BA203
				Route::post('getShipOrder', 'BA2\BA203Controller@getShipOrder')->name('inject.getShipOrder');
                //BA208
                Route::post('getChargeOff', 'BA2\BA208Controller@getChargeOff')->name('inject.getChargeOff');
                Route::post('getReceivable', 'BA2\BA208Controller@getReceivable')->name('inject.getReceivable');
                Route::post('getChargeOff2', 'BA2\BA208Controller@getChargeOff2')->name('inject.getChargeOff2');
                Route::post('getReceivable2', 'BA2\BA208Controller@getReceivable2')->name('inject.getReceivable2');
                //BA305


			});

			Route::group(['namespace' =>'CA'], function () {
				//CA201
				Route::post('changeProductCode', 'CA2\CA201Controller@changeProductCode')->name('inject.changeProductCode');
                //CA202
				Route::post('getCompanyOrder', 'CA2\CA202Controller@getCompanyOrder')->name('inject.getCompanyOrder');
				Route::post('havecited', 'CA2\CA202Controller@havecited')->name('inject.havecited');
                Route::post('getVendorOrder1', 'CA2\CA202Controller@getVendorOrder1')->name('inject.getVendorOrder1');
                Route::post('getProduct1', 'CA2\CA202Controller@getProduct1')->name('inject.getProduct1');
                //CA203
				Route::post('getCompanyReceive', 'CA2\CA203Controller@getCompanyReceive')->name('inject.getCompanyReceive');
                //CA207
                Route::post('transToOrder1', 'CA2\CA207Controller@transToOrder1')->name('inject.transToOrder1');
                //CA208
                Route::post('getChargeOff1', 'CA2\CA208Controller@getChargeOff1')->name('inject.getChargeOff1');
                Route::post('getPayable', 'CA2\CA208Controller@getPayable')->name('inject.getPayable');
			});

            Route::group(['namespace' =>'DA'], function () {
                //DA202
				Route::post('getStation', 'DA2\DA202Controller@getStation')->name('inject.getStation');
				Route::post('getStationComponent', 'DA2\DA202Controller@getStationComponent')->name('inject.getStationComponent');
				Route::post('getkeginfo', 'DA2\DA202Controller@getkeginfo')->name('inject.getkeginfo');
                Route::post('addfinished', 'DA2\DA202Controller@addfinished')->name('inject.addfinished');

			});
            Route::group(['namespace' =>'EA'], function () {
                //DA202
                Route::post('getsafenum', 'EA2\EA204Controller@getsafenum')->name('inject.getsafenum');

            });
            Route::group(['namespace' =>'GA'], function () {
                Route::post('gettype', 'GA2\GA201Controller@gettype')->name('inject.gettype');
                Route::post('checkimg', 'GA2\GA201Controller@checkimg')->name('inject.checkimg');

            });
            Route::group(['namespace' =>'OA'], function () {
				//OA101
                Route::post('getorderdata','OA1\OA101Controller@getOrderData')->name('inject.getOrderData');
                Route::post('getShipmentData', 'OA1\OA101Controller@getShipmentData')->name('inject.getShipmentData');
                Route::post('getExpenseData', 'OA1\OA101Controller@getExpenseData')->name('inject.getExpenseData');
                Route::post('getLastMonthExpenseData', 'OA1\OA101Controller@getLastMonthExpenseData')->name('inject.getLastMonthExpenseData');

			});
            Route::group(['namespace' =>'FA'], function () {
                //FA101
                Route::get('{page_id}/filter', 'FA1\FA101Controller@FA101_list')->name('FA101_list');
            });
            Route::group(['namespace' =>'DT'], function () {
                // //FA101
                // Route::get('{page_id}/filter', 'FA1\FA101Controller@FA101_list')->name('FA101_list');
                Route::post('getImport', 'ZZ999Controller@getImport')->name('inject.getImport');
			});
            Route::group(['namespace' =>'RE'], function () {
				//RE201
				Route::post('gethouseview', 'RE2\RE201Controller@gethouseview')->name('inject.gethouseview');
				//RE203
				Route::post('checkPayment', 'RE2\RE203Controller@checkPayment')->name('inject.checkPayment');
				//RE204
				Route::post('getRentDetails', 'RE2\RE204Controller@getRentDetails')->name('inject.getRentDetails');
			});


		});
    });

    Route::group(['prefix' => 'page'], function () {
            Route::get('{page_id}/list', 'Base\PageController@list')->name('system.page.list.show');
    });

    Route::group(['prefix' => 'download'], function () {
        Route::get('{fieldID}/{id}/{filename}', 'Base\FileController@download')->name('system.download');
    });

    Route::group(['namespace' => 'System'], function () {
        /*
            $SYSTEM = [
                'User' => ['users'],
                'Group' => ['groups'],
                'Permission' => ['permissions'],
                'Page' => ['pages','modules'],
                // 'notification' => 'notifications',
                'Verify' => ['verifies'],
                'Parameter' => ['parameters'],
                'Schedule' => ['schedules'],
            ];
            $ROUTE = [
                "get" => ["list", "form"],
                "post" => ["save"],
                "delete" => ["delete"],
            ];
            foreach ($SYSTEM as $controller => $pages) {
                foreach($pages as $page) {
                    Route::group(['prefix' => $page], function () use ($controller,$page,$ROUTE)
                    {
                        foreach($ROUTE as $method => $actions){
                            foreach($actions as $action){
                                Route::{$method}($action, "{$controller}Controller@{$page}_");
                            }
                        }
                    });
                }
            }
        */
        //用戶管理
        Route::group(['prefix' => 'users'], function () {
            Route::get('list', 'UserController@users_list')->name('users_list');
            Route::get('form/{type}/{id?}', 'UserController@users_form')->name('users_form');
            Route::post('save/{type}/{id?}', 'UserController@users_save')->name('users_save');
            Route::post('delete/{id}', 'UserController@users_delete')->name('users_delete');
        });
        //群組管理
        Route::group(['prefix' => 'groups'], function () {
            Route::get('list', 'GroupController@groups_list')->name('groups_list'); //list
            Route::get('form/{type}/{id?}', 'GroupController@groups_form')->name('groups_form'); //新增刪除跳轉至form頁
            Route::post('save/{type}/{id?}', 'GroupController@groups_save')->name('groups_save'); //在form頁點選送出
            Route::post('delete/{id}', 'GroupController@groups_delete')->name('groups_delete'); //刪除
        });
        //權限管理
        Route::group(['prefix' => 'permission'], function () {
            //跳轉到管理權限頁面
            Route::get('form/{type}/{id?}/{user_type?}', 'PermissionController@permission_form')->name('permission_form');
            //抓取欄位
            Route::post('getFields', 'PermissionController@permission_getFields')->name('permission_getFields');
            //儲存
            Route::post('permission_save', 'PermissionController@permission_save')->name('permission_save');
            //複製
            Route::post('permission_copy', 'PermissionController@permission_copy')->name('permission_copy');
        });

        // 模組設定
        Route::group(['prefix' => 'modules'], function () {
            Route::get('list', 'PageController@modules_list')->name('modules_list');
            Route::get('form/{type}/{id?}', 'PageController@modules_form')->name('modules_form');
            Route::post('save/{type}/{id?}', 'PageController@modules_save')->name('modules_save');
        });
        // 頁面設定
        Route::group(['prefix' => 'pages'], function () {
            Route::get('list', 'PageController@pages_list')->name('pages_list');
            Route::get('form/{type}/{id?}', 'PageController@pages_form')->name('pages_form');
            Route::post('save/{type}/{id?}', 'PageController@pages_save')->name('pages_save');
        });
        //通知管理
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('list', 'NotificationController@notification_setting_list')->name('notification_setting_list');
            //跳轉到管理通知頁面
            Route::get('form/{type}/{id?}', 'NotificationController@notification_setting_form')->name('notification_setting_form');
            //儲存通知設定
            Route::post('save/{type}/{id?}', 'NotificationController@notification_setting_save')->name('notification_setting_save');
            //刪除通知
            Route::post('delete/{id}', 'NotificationController@notification_setting_delete')->name('notification_setting_delete');
            //抓取通知
            Route::post('selectNotice', 'NotificationController@notification_setting_selectNotice')->name('notification_setting_selectNotice');
            //是否有通知
            Route::post('noticeOrNot', 'NotificationController@notification_setting_noticeOrNot')->name('notification_setting_noticeOrNot');
            //是否有通知
            Route::post('trandRead', 'NotificationController@notification_setting_trandRead')->name('notification_setting_trandRead');
            //email
            Route::get('testEmail', 'NotificationController@notification_setting_email')->name('notification_setting_email');
            //sms
            Route::get('testSMS', 'NotificationController@notification_setting_sms')->name('notification_setting_sms');
        });
        //審核管理
        Route::group(['prefix' => 'verifies'], function () {
            Route::get('list', 'VerifyController@verifies_list')->name('verifies_list');
            Route::get('form/{type}/{id}', 'VerifyController@verifies_form')->name('verifies_form');
            Route::post('save/{type}/{id}', 'VerifyController@verifies_save')->name('verifies_save');
            Route::delete('delete/{id}', 'VerifyController@verifies_delete')->name('verifies_delete');
        });

        // 翻譯設定
        Route::get('translation', 'TranslationController@list')->name('translation_list');

        //參數設定
        Route::group(['prefix' => 'parameters'], function () {
            Route::get('list', 'ParameterController@parameters_list')->name('parameters_list'); //參數管理list
            Route::get('form/{type}/{id?}', 'ParameterController@parameters_form')->name('parameters_form'); //新增刪除跳轉至form頁
            Route::post('save/{type}/{id?}', 'ParameterController@parameters_save')->name('parameters_save'); //在form頁點選送出
            Route::post('delete/{id}', 'ParameterController@parameters_delete')->name('parameters_delete'); //刪除
        });

        //排程管理
        Route::group(['prefix' => 'schedule'], function () {
            Route::get('list', 'ScheduleController@schedules_list')->name('schedules_list'); //排程管理list
            Route::get('form/{type}/{id?}', 'ScheduleController@schedules_form')->name('schedules_form'); //新增刪除跳轉至form頁
            Route::post('save/{type}/{id?}', 'ScheduleController@schedules_save')->name('schedules_save'); //在form頁點選送出
            Route::post('delete/{id?}', 'ScheduleController@schedules_del')->name('schedules_del'); //刪除
            Route::post('run/{id?}', 'ScheduleController@schedules_run')->name('schedules_run'); //立即執行
        });

        // Log
        Route::group(['prefix' => 'log'], function () {
            Route::get('list', 'LogController@list')->name('log_list'); //參數管理list
        });

        // 神奇小工具們
        Route::get('insert_sql', function(){return view('developing.form.insert_sql');})->name('insert_sql_list');
        Route::post('insert_sql', 'DevelopingToolsController@insert_sql')->name('insert_sql');
    });
});
