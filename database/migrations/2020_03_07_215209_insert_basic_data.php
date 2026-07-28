<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Migrations\Migration;

use App\Models\User;
use App\Models\Page;
use App\Models\Language;
use App\Models\Parameter;

use Staudenmeir\LaravelMigrationViews\Facades\Schema as View;

use App\Database\Migrations\Migration;
class InsertBasicData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();

        // Add User
        if(env('DB_CONNECTION') === 'sqlsrv'){
            $pdo->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
            DB::statement("SET IDENTITY_INSERT users ON");
        }
        $ROOT = User::create(['user_id' => 1, 'username' => 'root', 'password' => Hash::make('ggllmm'), 'name' => 'Root']);
        $ADMIN = User::create(['user_id' => 2,'username' => 'admin', 'password' => Hash::make('admin'), 'name' => 'Admin']);
        if(env('DB_CONNECTION') === 'sqlsrv'){DB::statement("SET IDENTITY_INSERT users OFF");}

        // Add Language
        if(env('DB_CONNECTION') === 'sqlsrv'){DB::statement("SET IDENTITY_INSERT languages ON");}
        $Language_zhTW = Language::create(['language_id' => 1,'language_code' => 'zh-TW','language_name' => '繁體中文(台灣)']);
        $Language_en = Language::create(['language_id' => 2,'language_code' => 'en','language_name' => 'English']);
        if(env('DB_CONNECTION') === 'sqlsrv'){
            DB::statement("SET IDENTITY_INSERT languages OFF");
            $pdo->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, false);
        }

        // Add Pages
            $Page_SY = Page::create([
                'page_code' => 'SY', 'page_module' => 0, 'page_list_template' => null, 'page_form_template' => null, 'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '系統管理模組'
            ]);

            $Page_SY_USER_MANAGE = Page::create([
                'page_code' => 'SY_USER_MANAGE', 'page_module' => $Page_SY->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '用戶管理'
            ]);
            $Page_SY_USERS = Page::create([
                'page_code' => 'SY_USERS', 'page_controller' => 'System\\Users\\UserController', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_list_template' => 'system.list.users', 'page_form_template' => 'system.form.users', 'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '用戶列表', 'page_options' => ['table' => 'users', 'primaryKey' => 'user_id']
            ]);
            $Page_SY_USER_AGENT = Page::create([
                'page_code' => 'SY_USER_AGENT', 'page_controller' => '', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null, 'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '用戶代理人', 'page_options' => ['table' => 'user_agent', 'primaryKey' => 'user_agent_id']
            ]);
            $Page_SY_USER_AGENT_PAGE = Page::create([
                'page_code' => 'SY_USER_AGENT_PAGE', 'page_controller' => '', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null, 'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '用戶代理人表身', 'page_options' => ['table' => 'user_agent_page', 'primaryKey' => 'user_agent_page_id']
            ]);
            $Page_SY_GROUPS = Page::create([
                'page_code' => 'SY_GROUPS', 'page_controller' => 'System\\Users\\GroupController', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_list_template' => 'system.list.groups', 'page_form_template' => 'system.form.groups', 'page_order' => 1, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '群組列表', 'page_options' => ['table' => 'groups', 'primaryKey' => 'group_id']
            ]);
            $Page_SY_GROUP_USER = Page::create([
                'page_code' => 'SY_GROUP_USER', 'page_controller' => 'System\\Users\\GroupUserController', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '群組設定', 'page_options' => ['table' => 'group_user', 'primaryKey' => 'group_user_id']
            ]);
            $Page_SY_PERMISSIONS = Page::create([
                'page_code' => 'SY_PERMISSIONS', 'page_controller' => 'System\\Users\\PermissionController', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '權限設定', 'page_options' => ['table' => 'permissions', 'primaryKey' => 'permission_id']
            ]);
            $Page_SY_PERMISSION_COLUMN = Page::create([
                'page_code' => 'SY_PERMISSION_COLUMN', 'page_controller' => 'System\\Users\\PermissionController', 'page_module' => $Page_SY_USER_MANAGE->page_id, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '權限設定表身', 'page_options' => ['table' => 'permission_column', 'primaryKey' => 'permission_column_id']
            ]);

            $Page_SY_PAGE_MANAGE = Page::create([
                'page_code' => 'SY_PAGE_MANAGE', 'page_module' => $Page_SY->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 1, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '表單管理'
            ]);
            $Page_SY_MODULES = Page::create([
                'page_code' => 'SY_MODULES', 'page_controller' => 'System\\Pages\\ModuleController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => 'system.list.modules', 'page_form_template' => 'system.form.modules',  'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '模組設定', 'page_options' => ['table' => 'modules', 'primaryKey' => 'module_id']
            ]);
            $Page_SY_PAGES = Page::create([
                'page_code' => 'SY_PAGES', 'page_controller' => 'System\\Pages\\PageController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => 'system.list.pages', 'page_form_template' => 'system.form.pages',  'page_order' => 1, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '頁面設定', 'page_options' => ['table' => 'pages', 'primaryKey' => 'page_id']
            ]);
            $Page_SY_FORMS = Page::create([
                'page_code' => 'SY_FORMS', 'page_controller' => 'System\\Pages\\FormController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '表單設定', 'page_options' => ['table' => 'forms', 'primaryKey' => 'form_id']
            ]);
            $Page_SY_FIELDS = Page::create(
                ['page_code' => 'SY_FIELDS', 'page_controller' => 'System\\Pages\\FieldController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '欄位設定', 'page_options' => ['table' => 'fields', 'primaryKey' => 'field_id']
            ]);
            $Page_SY_VERIFIES = Page::create([
                'page_code' => 'SY_VERIFIES', 'page_controller' => 'System\\Users\\VerifysController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => 'system.list.verifies', 'page_form_template' => 'system.form.verifies',  'page_order' => 2, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '審核管理', 'page_options' => ['table' => 'verifies', 'primaryKey' => 'verify_id']
            ]);
            $Page_SY_VERIFY_LEVEL = Page::create([
                'page_code' => 'SY_VERIFY_LEVEL', 'page_controller' => 'System\\Users\\VerifysController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '審核管理表身', 'page_options' => ['table' => 'verify_level', 'primaryKey' => 'verify_level_id']
            ]);
            $Page_SY_VERIFY_CONDITION = Page::create([
                'page_code' => 'SY_VERIFY_CONDITION', 'page_controller' => 'System\\Users\\VerifysController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '審核管理表身', 'page_options' => ['table' => '`verify_condition`', 'primaryKey' => 'verify_condition_id']
            ]);
            $Page_SY_NOTIFICATION_SETTING = Page::create([
                'page_code' => 'SY_NOTIFICATION_SETTING', 'page_controller' => 'System\\Users\\NotificationSettingController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => 'system.list.notification_setting', 'page_form_template' => 'system.form.notification_setting',  'page_order' => 3, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '通知設定', 'page_options' => ['table' => 'notification_setting', 'primaryKey' => 'notification_setting_id']
            ]);
            $Page_SY_NOTIFICATIONS = Page::create([
                'page_code' => 'SY_NOTIFICATIONS', 'page_controller' => 'System\\Notification\\NotificationController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '通知內容', 'page_options' => ['table' => 'notifications', 'primaryKey' => 'notification_id']
            ]);
            $Page_SY_NOTIFICATION_USER = Page::create([
                'page_code' => 'SY_NOTIFICATION_USER', 'page_controller' => 'System\\Notification\\NotificationUserController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '通知資料', 'page_options' => ['table' => 'notification_user', 'primaryKey' => 'user_id']
            ]);
            $Page_SY_NOTIFICATION_TARGET = Page::create([
                'page_code' => 'SY_NOTIFICATION_TARGET', 'page_controller' => 'System\\Notification\\NotificationUserController', 'page_module' => $Page_SY_PAGE_MANAGE->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 0, 'page_readonly' => true, 'page_visible' => false, 'page_remarks' => '通知資料', 'page_options' => ['table' => 'notification_target', 'primaryKey' => 'notification_target_id']
            ]);

            $Page_SY_LANGUAGE_MANAGE = Page::create([
                'page_code' => 'SY_LANGUAGE_MANAGE', 'page_module' => $Page_SY->page_id, 'page_list_template' => null, 'page_form_template' => null,  'page_order' => 2, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '語言管理'
            ]);
            $Page_SY_LANGUAGES = Page::create([
                'page_code' => 'SY_LANGUAGES', 'page_controller' => 'System\\Translation\\LanguageController', 'page_module' => $Page_SY_LANGUAGE_MANAGE->page_id, 'page_list_template' => 'system.list.languages', 'page_form_template' => 'system.form.languages', 'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '語言種類', 'page_options' => ['table' => 'languages', 'primaryKey' => 'language_id']
            ]);
            $Page_SY_TRANSLATION = Page::create([
                'page_code' => 'SY_TRANSLATION', 'page_controller' => 'System\\Translation\\TranslationController', 'page_module' => $Page_SY_LANGUAGE_MANAGE->page_id, 'page_list_template' => 'system.list.translation', 'page_form_template' => 'system.form.translation',  'page_order' => 1, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '語言列表', 'page_options' => ['table' => 'translation', 'primaryKey' => 'translation_id']
            ]);

            $Page_SY_PARAMETERS = Page::create([
                'page_code' => 'SY_PARAMETERS', 'page_module' => $Page_SY->page_id, 'page_list_template' => 'system.list.parameters', 'page_form_template' => 'system.form.parameters',  'page_order' => 6, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '參數設定', 'page_options' => ['table' => 'parameters', 'primaryKey' => 'parameter_id']
            ]);
            $Page_SY_LOG = Page::create([
                'page_code' => 'SY_LOG', 'page_module' => $Page_SY->page_id, 'page_list_template' => 'system.list.log', 'page_form_template' => '',  'page_order' => 7, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => 'Log', 'page_options' => ['table' => 'log']
            ]);

            $Page_DT = Page::create([
                'page_code' => 'DT', 'page_module' => 0, 'page_list_template' => null, 'page_form_template' => null, 'page_order' => 1, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '開發人員工具'
            ]);
            $Page_DT_MAGIC_TOOLS = Page::create([
                'page_code' => 'DT_MAGIC_TOOLS', 'page_module' => $Page_DT->page_id, 'page_list_template' => null, 'page_form_template' => null, 'page_order' => 1, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '神奇小工具'
            ]);
            $Page_DT_INSERT_SQL = Page::create([
                'page_code' => 'DT_INSERT_SQL', 'page_module' => $Page_DT_MAGIC_TOOLS->page_id, 'page_list_template' => 'a', 'page_form_template' => 'a', 'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => 'INSERT指令生產器'
            ]);

            $Page_DT_SCHEDULES = Page::create([
                'page_code' => 'DT_SCHEDULES', 'page_controller' => 'System\\DeveloperTools\\SchedulesController', 'page_module' => $Page_DT->page_id, 'page_list_template' => 'system.list.schedules', 'page_form_template' => 'system.form.schedules',  'page_order' => 0, 'page_readonly' => true, 'page_visible' => true, 'page_remarks' => '排程管理', 'page_options' => ['table' => 'schedules', 'primaryKey' => 'schedule_id']
            ]);

        // Add Forms
            $Form_SY_USERS = $Page_SY_USERS->forms()->create(['page_id' => $Page_SY_USERS->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_USER_AGENT = $Page_SY_USER_AGENT->forms()->create(['page_id' => $Page_SY_USER_AGENT->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_USER_AGENT_PAGE = $Page_SY_USER_AGENT_PAGE->forms()->create(['page_id' => $Page_SY_USER_AGENT_PAGE->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_GROUPS = $Page_SY_GROUPS->forms()->create(['page_id' => $Page_SY_GROUPS->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_GROUP_USER = $Page_SY_GROUP_USER->forms()->create(['page_id' => $Page_SY_GROUP_USER->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_PERMISSIONS = $Page_SY_PERMISSIONS->forms()->create(['page_id' => $Page_SY_PERMISSIONS->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_PERMISSION_COLUMN = $Page_SY_PERMISSION_COLUMN->forms()->create(['page_id' => $Page_SY_PERMISSION_COLUMN->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_VERIFIES = $Page_SY_VERIFIES->forms()->create(['page_id' => $Page_SY_VERIFIES->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_VERIFY_LEVEL = $Page_SY_VERIFY_LEVEL->forms()->create(['page_id' => $Page_SY_VERIFY_LEVEL->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_VERIFY_CONDITION = $Page_SY_VERIFY_CONDITION->forms()->create(['page_id' => $Page_SY_VERIFY_CONDITION->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_NOTIFICATION_SETTING = $Page_SY_NOTIFICATION_SETTING->forms()->create(['page_id' => $Page_SY_NOTIFICATION_SETTING->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_NOTIFICATIONS = $Page_SY_NOTIFICATIONS->forms()->create(['page_id' => $Page_SY_NOTIFICATIONS->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_NOTIFICATION_USER = $Page_SY_NOTIFICATION_USER->forms()->create(['page_id' => $Page_SY_NOTIFICATION_USER->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_NOTIFICATION_TARGET = $Page_SY_NOTIFICATION_TARGET->forms()->create(['page_id' => $Page_SY_NOTIFICATION_TARGET->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_MODULES = $Page_SY_MODULES->forms()->create(['page_id' => $Page_SY_MODULES->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_PAGES = $Page_SY_PAGES->forms()->create(['page_id' => $Page_SY_PAGES->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_FORMS = $Page_SY_FORMS->forms()->create(['page_id' => $Page_SY_FORMS->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_FIELDS = $Page_SY_FIELDS->forms()->create(['page_id' => $Page_SY_FIELDS->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_LANGUAGES = $Page_SY_LANGUAGES->forms()->create(['page_id' => $Page_SY_LANGUAGES->page_id, 'form_order' => 0, 'form_type' => 'head']);
            $Form_SY_TRANSLATION = $Page_SY_TRANSLATION->forms()->create(['page_id' => $Page_SY_TRANSLATION->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_PARAMETERS = $Page_SY_PARAMETERS->forms()->create(['page_id' => $Page_SY_PARAMETERS->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_SY_LOG = $Page_SY_LOG->forms()->create(['page_id' => $Page_SY_LOG->page_id, 'form_order' => 0, 'form_type' => 'head']);

            $Form_DT_SCHEDULES = $Page_DT_SCHEDULES->forms()->create(['page_id' => $Page_DT_SCHEDULES->page_id, 'form_order' => 0, 'form_type' => 'head']);

        // Add Fields
            $Form_SY_USERS->fields()->createMany([
                ['field_code' => 'username', 'field_type' => 'string', 'field_rule' => ['required','regex:/^[a-zA-Z0-9]+$/','max:20','unique:users'], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'password', 'field_type' => 'string', 'field_rule' => ['required','max:16','confirmed'], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'name', 'field_type' => 'string', 'field_rule' => ['required','max:30'], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'user_disabled', 'field_type' => 'boolean', 'field_rule' => ["boolean"], 'field_order' => 3, 'field_required' => false, 'field_show_on_list' => true],
                ['field_code' => 'user_remarks', 'field_type' => 'string', 'field_rule' => ['nullable','max:255'], 'field_order' => 4, 'field_required' => false, 'field_show_on_list' => true]
            ]);
            $Form_SY_USER_AGENT->fields()->createMany([
                /* ['field_code' => 'user_id', 'field_type' => 'integer', 'field_rule' => ['required','integer','unique:user_agent'], 'field_order' => 0, 'field_required' => true], */
                ['field_code' => 'user_agent_enabled', 'field_type' => 'boolean', 'field_rule' => ['boolean'], 'field_order' => 1, 'field_required' => true],
                ['field_code' => 'user_agent_enabled_at', 'field_type' => 'datetime', 'field_rule' => ['date_format:Y-m-d H:i:s'], 'field_order' => 2, 'field_required' => true],
                ['field_code' => 'user_agent_disabled_at', 'field_type' => 'datetime', 'field_rule' => ['date_format:Y-m-d H:i:s'], 'field_order' => 3, 'field_required' => true]
            ]);
            $Form_SY_USER_AGENT_PAGE->fields()->createMany([
                ['field_code' => 'page_id', 'field_type' => 'integer', 'field_rule' => ['required','integer'], 'field_order' => 0, 'field_required' => true],
                ['field_code' => 'user_agent_target_type', 'field_type' => 'string', 'field_rule' => ['required','string',['in' => ['user','group']]], 'field_order' => 1, 'field_required' => true, 'field_options' => ["options" => ["user","group"]]],
                ['field_code' => 'user_agent_target_id', 'field_type' => 'reference', 'field_rule' => ['required','integer'], 'field_order' => 2, 'field_required' => true, 'field_options' => ["reference" => ["tables"=> [],"fields"=> [["field_code"=> "user_agent_target_id","table_name"=> "","show"=> false,"target"=> "user_agent_target_id","order"=> 0],["field_code"=> "user_agent_target_name","table_name"=> "","show"=> true,"target"=> "user_agent_target_name","order"=> 1],["field_code"=> "user_agent_target_type","table_name"=> "","show"=> false,"target"=> null,"order"=> 2]],"front_field"=> ["enabled"=> true,"form_id"=> $Form_SY_USER_AGENT_PAGE->form_id,"field_code"=> "user_agent_target_type","target"=> null],"type"=> "list","select_field"=> null,"sql"=> ["native"=> ["enabled"=> true,"sql"=> "SELECT user_id as user_agent_target_id, name as user_agent_target_name, 'user' as user_agent_target_type FROM users WHERE user_disabled <> '1' AND user_id <> 0 UNION SELECT group_id, group_name, 'group' as type FROM groups"],"expression"=> ["where"=> []]]]]],
            ]);
            $Form_SY_GROUPS->fields()->createMany([
                ['field_code' => 'group_name', 'field_type' => 'string', 'field_rule' => ['required','max:50','unique:groups'], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true]
            ]);
            $Form_SY_GROUP_USER->fields()->createMany([
                ['field_code' => 'group_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'user_id', 'field_type' => 'integer', 'field_rule' => ['required'], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true]
            ]);

            $Form_SY_PAGES->fields()->createMany([
                ['field_code' => 'page_code', 'field_type' => 'string', 'field_rule' => ['required','max:5','unique:pages','string','regex:/^[A-Z]{2}[0-9]{3}$/'], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'page_module', 'field_type' => 'integer', 'field_rule' => ['required','integer','min:0'], 'field_order' => 1, 'field_required' => false, 'field_show_on_list' => false],
                ['field_code' => 'page_list_template', 'field_type' => 'string', 'field_rule' => ['required','max:255','string'], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'page_form_template', 'field_type' => 'string', 'field_rule' => ['required','max:255','string'], 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'page_visible', 'field_type' => 'boolean', 'field_rule' => ['boolean'], 'field_order' => 4, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'page_order', 'field_type' => 'integer', 'field_rule' => ['integer'], 'field_order' => 5, 'field_required' => true, 'field_show_on_list' => true, 'field_show_on_form' => false],
                ['field_code' => 'page_readonly', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 6, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'page_options', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 7, 'field_required' => false, 'field_show_on_list' => false, 'field_show_on_form' => false],
                ['field_code' => 'page_remarks', 'field_type' => 'string', 'field_rule' => ['max:2000'], 'field_order' => 8, 'field_required' => false, 'field_show_on_list' => true],
            ]);
            $Form_SY_FORMS->fields()->createMany([
                ['field_code' => 'page_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'form_order', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'form_type', 'field_type' => 'select', 'field_rule' => [], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'ref_page_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 3, 'field_required' => false, 'field_show_on_list' => true]
            ]);
            $Form_SY_FIELDS->fields()->createMany([
                ['field_code' => 'form_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_form' => false],
                ['field_code' => 'field_code', 'field_type' => 'string', 'field_rule' => ['required','string','max:80','regex:/^[A-Za-z]\w*[A-Za-z0-9]$|^[A-Za-z]$/'], 'field_order' => 1, 'field_required' => true, 'field_show_on_form' => true],
                ['field_code' => 'field_type', 'field_type' => 'select', 'field_rule' => ['required','string',['in' => ['string', 'textarea', 'integer', 'decimal', 'boolean', 'select', 'checkboxes', 'radio', 'date', 'time', 'datetime', 'file', 'reference', 'reference_page', 'button']]], 'field_order' => 2, 'field_required' => true, 'field_show_on_form' => true, 'field_options' => ['options' => ['string', 'textarea', 'integer', 'decimal', 'boolean', 'select', 'checkboxes', 'radio', 'date', 'time', 'datetime', 'file', 'reference', 'reference_page', 'button']]],
                ['field_code' => 'field_rule', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 3, 'field_required' => false, 'field_show_on_form' => false],
                ['field_code' => 'field_order', 'field_type' => 'integer', 'field_default_value' => 0, 'field_rule' => ['required','numeric'], 'field_order' => 4, 'field_required' => true, 'field_show_on_form' => true],
                ['field_code' => 'field_default_value', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 5, 'field_required' => false, 'field_show_on_form' => true],
                ['field_code' => 'field_required', 'field_type' => 'boolean', 'field_rule' => ['boolean'], 'field_order' => 6, 'field_required' => true, 'field_show_on_form' => true],
                ['field_code' => 'field_readonly', 'field_type' => 'boolean', 'field_rule' => ['boolean'], 'field_order' => 7, 'field_required' => true, 'field_show_on_form' => true],
                ['field_code' => 'field_show_on_form', 'field_type' => 'boolean', 'field_default_value' => true, 'field_rule' => ['boolean'], 'field_order' => 8, 'field_required' => true, 'field_show_on_form' => true],
                ['field_code' => 'field_show_on_list', 'field_type' => 'boolean', 'field_default_value' => true, 'field_rule' => ['boolean'], 'field_order' => 9, 'field_required' => true, 'field_show_on_form' => true],
                ['field_code' => 'field_options', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 10, 'field_required' => true, 'field_show_on_form' => false],
                ['field_code' => 'field_remarks', 'field_type' => 'string', 'field_rule' => ['nullable','string','max:2000'], 'field_order' => 11, 'field_required' => false, 'field_show_on_form' => true]
            ]);

            $Form_SY_VERIFIES->fields()->createMany([
                // ['field_code' => 'verify_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'page_id', 'field_type' => 'integer', 'field_rule' => ['required','unique:verifies','integer'], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
            ]);
            $Form_SY_VERIFY_LEVEL->fields()->createMany([
                ['field_code' => 'verify_level', 'field_type' => 'integer', 'field_rule' => ['required','integer'], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false, 'field_show_on_form' => false],
                ['field_code' => 'verify_target_type', 'field_type' => 'select', 'field_rule' => ['required','string',['in' => ['user','group']]], 'field_order' => 1, 'field_required' => false, 'field_show_on_list' => false, 'field_options' => ["options" => ["user","group"]]],
                ['field_code' => 'verify_target_id', 'field_type' => 'reference', 'field_rule' => ['required','string'], 'field_order' => 2, 'field_required' => false, 'field_show_on_list' => false, 'field_options' => ["reference" => ["tables"=> [],"fields"=> [["field_code"=> "verify_target_id","table_name"=> "","show"=> false,"target"=> "verify_target_id","order"=> 0],["field_code"=> "verify_target_name","table_name"=> "","show"=> true,"target"=> "verify_target_name","order"=> 1],["field_code"=> "verify_target_type","table_name"=> "","show"=> false,"target"=> null,"order"=> 2],["field_code"=> "page_id","table_name"=> "","show"=> false,"target"=> null,"order"=> 3],["field_code"=> "verify_population_max","table_name"=> "","show"=> true,"target"=> null,"order"=> 4]],"front_field"=> ["enabled"=> true,"fields"=> [["form_id"=> $Form_SY_VERIFIES->form_id,"field_code"=> "page_id","target"=> null],["form_id"=> $Form_SY_VERIFY_LEVEL->form_id,"field_code"=> "verify_target_type","target"=> null]]],"type"=> "list","select_field"=> null,"sql"=> ["native"=> ["enabled"=> true,"sql"=> "SELECT a.user_id as verify_target_id, a.name as verify_target_name, 'user' as verify_target_type, b.page_id, 1 as verify_population_max FROM users a LEFT JOIN permissions b ON a.user_id = b.permission_target_id AND b.permission_type = 'user' WHERE user_disabled <> '1' AND a.user_id <> 0 AND b.permission_read = 1UNION SELECT a.group_id as verify_target_id, a.group_name as verify_target_name, 'group' as verify_target_type, b.page_id,  (SELECT COUNT(*) FROM group_user c WHERE c.group_id = a.group_id) FROM groups a LEFT JOIN permissions b ON a.group_id = b.permission_target_id AND b.permission_type = 'group' WHERE b.permission_read = 1"],"expression"=> ["where"=> []]]]]],
                ['field_code' => 'verify_population', 'field_type' => 'integer', 'field_rule' => ['required','integer','min:1'], 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => false],
            ]);
            $Form_SY_VERIFY_CONDITION->fields()->createMany([
                ['field_code' => 'verify_condition_group', 'field_type' => 'integer', 'field_rule' => ['required','integer','min:0'], 'field_order' => 0, 'field_required' => false, 'field_show_on_list' => false],
                ['field_code' => 'verify_logical', 'field_type' => 'select', 'field_rule' => ['required','string',['in' => ["AND", "OR"]]], 'field_order' => 1, 'field_required' => false, 'field_show_on_list' => false, 'field_options' => ["options" => ["AND", "OR"]]],
                ['field_code' => 'field_code', 'field_type' => 'select', 'field_rule' => ['required'], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => false, 'field_options' => ["options" => []]],
                ['field_code' => 'verify_comparison', 'field_type' => 'select', 'field_rule' => ['required','string',['in' => ["=","<>",">","<",">=","<=","LIKE","NOT LIKE"]]], 'field_order' => 3, 'field_required' => false, 'field_show_on_list' => false, 'field_options' => ["options" => ["=","<>",">","<",">=","<=","LIKE","NOT LIKE"]]],
                ['field_code' => 'verify_value', 'field_type' => 'string', 'field_rule' => ['string','nullable'], 'field_order' => 4, 'field_required' => false, 'field_show_on_list' => false],
            ]);

            // $Form_SY_NOTIFICATIONS->fields()->createMany([]);
            $Form_SY_NOTIFICATION_SETTING->fields()->createMany([
                ['field_code' => 'page_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                // ['field_code' => 'notification_setting_target', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'notification_setting_trigger_type', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'notification_setting_content', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'notification_setting_mail', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 4, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'notification_setting_phone', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 5, 'field_required' => true, 'field_show_on_list' => true]
            ]);
            $Form_SY_NOTIFICATION_USER->fields()->createMany([
                ['field_code' => 'notification_user_phone', 'field_type' => 'string', 'field_rule' => ["nullable","regex: /^[0-9]+$/"], 'field_order' => 0, 'field_required' => false, 'field_show_on_list' => false],
                ['field_code' => 'notification_user_email', 'field_type' => 'string', 'field_rule' => ["nullable","email:rfc"], 'field_order' => 1, 'field_required' => false, 'field_show_on_list' => false],
            ]);
            $Form_SY_NOTIFICATION_TARGET->fields()->createMany([
                ['field_code' => 'notification_setting_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],

                ['field_code' => 'notification_target', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'notification_target_type', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => true]
            ]);

            $Form_SY_LANGUAGES->fields()->createMany([
                ['field_code' => 'language_code', 'field_type' => 'string', 'field_rule' => ['required','alphabet_dash','unique:languages'], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'language_name', 'field_type' => 'string', 'field_rule' => ['required'], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true]
            ]);
            $Form_SY_TRANSLATION->fields()->createMany([
                ['field_code' => 'language_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'translation_type', 'field_type' => 'select', 'field_rule' => 'required', 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'translation_code', 'field_type' => 'string', 'field_rule' => ['required','alpha_dash'], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'form_id', 'field_type' => 'integer', 'field_rule' => ['required','integer'], 'field_order' => 3, 'field_required' => false, 'field_show_on_list' => true],
                ['field_code' => 'translation', 'field_type' => 'string', 'field_rule' => ['required'], 'field_order' => 4, 'field_required' => true, 'field_show_on_list' => true]
            ]);

            $Form_SY_PERMISSIONS->fields()->createMany([
                ['field_code' => 'page_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_target_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_type', 'field_type' => 'select', 'field_rule' => [], 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_read', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 4, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_insert', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 5, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_update', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 6, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_delete', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 7, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_allow_rw_all', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 8, 'field_required' => true, 'field_show_on_list' => false]
            ]);
            $Form_SY_PERMISSION_COLUMN->fields()->createMany([
                ['field_code' => 'permission_column_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'field_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_column_attribute', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_column_logic', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_column_content', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_column_relative', 'field_type' => 'select', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'permission_column_remarks', 'field_type' => 'string', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
            ]);

            $Form_SY_PARAMETERS->fields()->createMany([
                ['field_code' => 'parameter_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'parameter_code', 'field_type' => 'string', 'field_rule' => ['required','max:50','unique:parameters'], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'parameter_value', 'field_type' => 'string', 'field_rule' => ['required','max:50'], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'parameter_deletable', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 3, 'field_required' => false, 'field_show_on_list' => true],
                ['field_code' => 'parameter_remarks', 'field_type' => 'string', 'field_rule' => ['required','max:200'], 'field_order' => 4, 'field_required' => true, 'field_show_on_list' => true]
            ]);

            $Form_SY_LOG->fields()->createMany([
                ['field_code' => 'log_id', 'field_type' => 'integer', 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'page_id', 'field_type' => 'integer', 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'form_id', 'field_type' => 'integer', 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'id', 'field_type' => 'integer', 'field_order' => 4, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'parent_id', 'field_type' => 'integer', 'field_order' => 5, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'action', 'field_type' => 'integer', 'field_order' => 6, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'data', 'field_type' => 'string', 'field_order' => 7, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'created_at', 'field_type' => 'datetime', 'field_order' => 8, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'created_by', 'field_type' => 'integer', 'field_order' => 9, 'field_required' => true, 'field_show_on_list' => true],
            ]);

            $Form_DT_SCHEDULES->fields()->createMany([
                ['field_code' => 'schedule_id', 'field_type' => 'integer', 'field_rule' => [], 'field_order' => 0, 'field_required' => true, 'field_show_on_list' => false],
                ['field_code' => 'schedule_name', 'field_type' => 'string', 'field_rule' => ['required','max:50','unique:schedules'], 'field_order' => 1, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'schedule_fun', 'field_type' => 'string', 'field_rule' => ['required','max:50','unique:schedules'], 'field_order' => 2, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'schedule_remarks', 'field_type' => 'string', 'field_rule' => ['required','max:200'], 'field_order' => 3, 'field_required' => true, 'field_show_on_list' => true],
                ['field_code' => 'schedule_active', 'field_type' => 'boolean', 'field_rule' => [], 'field_order' => 4, 'field_required' => false, 'field_show_on_list' => true],
            ]);

        $Language_en->translation()->createMany([
                // Rules
                    ['translation_type' => 'rule', 'translation_code' => 'rule_unique', 'form_id' => null, 'translation' => 'Unique in table of Database'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_distinct', 'form_id' => null, 'translation' => 'Unique in body of Table'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_word_limit', 'form_id' => null, 'translation' => 'Word limit'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_string_content', 'form_id' => null, 'translation' => 'String content'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_letter_numeric', 'form_id' => null, 'translation' => 'Letter and numeric'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_url', 'form_id' => null, 'translation' => 'URL format'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_email', 'form_id' => null, 'translation' => 'E-mail format'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_in', 'form_id' => null, 'translation' => 'In'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_not_in', 'form_id' => null, 'translation' => 'Not In'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_number_limit', 'form_id' => null, 'translation' => 'Number limit'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_digits_limit', 'form_id' => null, 'translation' => 'Digits limit'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_max', 'form_id' => null, 'translation' => 'Max'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_min', 'form_id' => null, 'translation' => 'Min'],
                // Rule Message
                    ['translation_type' => 'rule', 'translation_code' => 'accepted', 'form_id' => null, 'translation' => 'The :attribute must be accepted.'],
                    ['translation_type' => 'rule', 'translation_code' => 'active_url', 'form_id' => null, 'translation' => 'The :attribute is not a valid URL.'],
                    ['translation_type' => 'rule', 'translation_code' => 'after', 'form_id' => null, 'translation' => 'The :attribute must be a date after :date.'],
                    ['translation_type' => 'rule', 'translation_code' => 'after_or_equal', 'form_id' => null, 'translation' => 'The :attribute must be a date after or equal to :date.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha', 'form_id' => null, 'translation' => 'The :attribute may only contain letters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet_number_dash', 'form_id' => null, 'translation' => 'The :attribute may only contain letters, numbers, dashes and underscores.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha_dash', 'form_id' => null, 'translation' => 'The :attribute may only contain letters, numbers, dashes and underscores.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet_dash', 'form_id' => null, 'translation' => 'The :attribute may only contain letters, dashes and underscores.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet_number', 'form_id' => null, 'translation' => 'The :attribute may only contain letters, numbers.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet', 'form_id' => null, 'translation' => 'The :attribute may only contain letters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha_num', 'form_id' => null, 'translation' => 'The :attribute may only contain letters and numbers.'],
                    ['translation_type' => 'rule', 'translation_code' => 'array', 'form_id' => null, 'translation' => 'The :attribute must be an array.'],
                    ['translation_type' => 'rule', 'translation_code' => 'before', 'form_id' => null, 'translation' => 'The :attribute must be a date before :date.'],
                    ['translation_type' => 'rule', 'translation_code' => 'before_or_equal', 'form_id' => null, 'translation' => 'The :attribute must be a date before or equal to :date.'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.numeric', 'form_id' => null, 'translation' => 'The :attribute must be between :min and :max.'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.file', 'form_id' => null, 'translation' => 'The :attribute must be between :min and :max kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.string', 'form_id' => null, 'translation' => 'The :attribute must be between :min and :max characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.array', 'form_id' => null, 'translation' => 'The :attribute must have between :min and :max items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'boolean', 'form_id' => null, 'translation' => 'The :attribute field must be true or false.'],
                    ['translation_type' => 'rule', 'translation_code' => 'confirmed', 'form_id' => null, 'translation' => 'The :attribute confirmation does not match.'],
                    ['translation_type' => 'rule', 'translation_code' => 'date', 'form_id' => null, 'translation' => 'The :attribute is not a valid date.'],
                    ['translation_type' => 'rule', 'translation_code' => 'date_equals', 'form_id' => null, 'translation' => 'The :attribute must be a date equal to :date.'],
                    ['translation_type' => 'rule', 'translation_code' => 'date_format', 'form_id' => null, 'translation' => 'The :attribute does not match the format :format.'],
                    ['translation_type' => 'rule', 'translation_code' => 'different', 'form_id' => null, 'translation' => 'The :attribute and :other must be different.'],
                    ['translation_type' => 'rule', 'translation_code' => 'digits', 'form_id' => null, 'translation' => 'The :attribute must be :digits digits.'],
                    ['translation_type' => 'rule', 'translation_code' => 'digits_between', 'form_id' => null, 'translation' => 'The :attribute must be between :min and :max digits.'],
                    ['translation_type' => 'rule', 'translation_code' => 'dimensions', 'form_id' => null, 'translation' => 'The :attribute has invalid image dimensions.'],
                    ['translation_type' => 'rule', 'translation_code' => 'distinct', 'form_id' => null, 'translation' => 'The :attribute field has a duplicate value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'email', 'form_id' => null, 'translation' => 'The :attribute must be a valid email address.'],
                    ['translation_type' => 'rule', 'translation_code' => 'ends_with', 'form_id' => null, 'translation' => 'The :attribute must end with one of the following: :values'],
                    ['translation_type' => 'rule', 'translation_code' => 'exists', 'form_id' => null, 'translation' => 'The selected :attribute is invalid.'],
                    ['translation_type' => 'rule', 'translation_code' => 'file', 'form_id' => null, 'translation' => 'The :attribute must be a file.'],
                    ['translation_type' => 'rule', 'translation_code' => 'filled', 'form_id' => null, 'translation' => 'The :attribute field must have a value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.numeric', 'form_id' => null, 'translation' => 'The :attribute must be greater than :value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.file', 'form_id' => null, 'translation' => 'The :attribute must be greater than :value kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.string', 'form_id' => null, 'translation' => 'The :attribute must be greater than :value characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.array', 'form_id' => null, 'translation' => 'The :attribute must have more than :value items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.numeric', 'form_id' => null, 'translation' => 'The :attribute must be greater than or equal :value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.file', 'form_id' => null, 'translation' => 'The :attribute must be greater than or equal :value kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.string', 'form_id' => null, 'translation' => 'The :attribute must be greater than or equal :value characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.array', 'form_id' => null, 'translation' => 'The :attribute must have :value items or more.'],
                    ['translation_type' => 'rule', 'translation_code' => 'image', 'form_id' => null, 'translation' => 'The :attribute must be an image.'],
                    ['translation_type' => 'rule', 'translation_code' => 'in', 'form_id' => null, 'translation' => 'The selected :attribute is invalid.'],
                    ['translation_type' => 'rule', 'translation_code' => 'in_array', 'form_id' => null, 'translation' => 'The :attribute field does not exist in :other.'],
                    ['translation_type' => 'rule', 'translation_code' => 'integer', 'form_id' => null, 'translation' => 'The :attribute must be an integer.'],
                    ['translation_type' => 'rule', 'translation_code' => 'ip', 'form_id' => null, 'translation' => 'The :attribute must be a valid IP address.'],
                    ['translation_type' => 'rule', 'translation_code' => 'ipv4', 'form_id' => null, 'translation' => 'The :attribute must be a valid IPv4 address.'],
                    ['translation_type' => 'rule', 'translation_code' => 'ipv6', 'form_id' => null, 'translation' => 'The :attribute must be a valid IPv6 address.'],
                    ['translation_type' => 'rule', 'translation_code' => 'json', 'form_id' => null, 'translation' => 'The :attribute must be a valid JSON string.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.numeric', 'form_id' => null, 'translation' => 'The :attribute must be less than :value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.file', 'form_id' => null, 'translation' => 'The :attribute must be less than :value kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.string', 'form_id' => null, 'translation' => 'The :attribute must be less than :value characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.array', 'form_id' => null, 'translation' => 'The :attribute must have less than :value items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.numeric', 'form_id' => null, 'translation' => 'The :attribute must be less than or equal :value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.file', 'form_id' => null, 'translation' => 'The :attribute must be less than or equal :value kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.string', 'form_id' => null, 'translation' => 'The :attribute must be less than or equal :value characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.array', 'form_id' => null, 'translation' => 'The :attribute must not have more than :value items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.numeric', 'form_id' => null, 'translation' => 'The :attribute may not be greater than :max.'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.file', 'form_id' => null, 'translation' => 'The :attribute may not be greater than :max kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.string', 'form_id' => null, 'translation' => 'The :attribute may not be greater than :max characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.array', 'form_id' => null, 'translation' => 'The :attribute may not have more than :max items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'mimes', 'form_id' => null, 'translation' => 'The :attribute must be a file of type: :values.'],
                    ['translation_type' => 'rule', 'translation_code' => 'mimetypes', 'form_id' => null, 'translation' => 'The :attribute must be a file of type: :values.'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.numeric', 'form_id' => null, 'translation' => 'The :attribute must be at least :min.'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.file', 'form_id' => null, 'translation' => 'The :attribute must be at least :min kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.string', 'form_id' => null, 'translation' => 'The :attribute must be at least :min characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.array', 'form_id' => null, 'translation' => 'The :attribute must have at least :min items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'not_in', 'form_id' => null, 'translation' => 'The selected :attribute is invalid.'],
                    ['translation_type' => 'rule', 'translation_code' => 'not_regex', 'form_id' => null, 'translation' => 'The :attribute format is invalid.'],
                    ['translation_type' => 'rule', 'translation_code' => 'numeric', 'form_id' => null, 'translation' => 'The :attribute must be a number.'],
                    ['translation_type' => 'rule', 'translation_code' => 'password', 'form_id' => null, 'translation' => 'The password is incorrect.'],
                    ['translation_type' => 'rule', 'translation_code' => 'present', 'form_id' => null, 'translation' => 'The :attribute field must be present.'],
                    ['translation_type' => 'rule', 'translation_code' => 'regex', 'form_id' => null, 'translation' => 'The :attribute format is invalid.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required', 'form_id' => null, 'translation' => 'The :attribute field is required.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_if', 'form_id' => null, 'translation' => 'The :attribute field is required when :other is :value.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_unless', 'form_id' => null, 'translation' => 'The :attribute field is required unless :other is in :values.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_with', 'form_id' => null, 'translation' => 'The :attribute field is required when :values is present.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_with_all', 'form_id' => null, 'translation' => 'The :attribute field is required when :values are present.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_without', 'form_id' => null, 'translation' => 'The :attribute field is required when :values is not present.'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_without_all', 'form_id' => null, 'translation' => 'The :attribute field is required when none of :values are present.'],
                    ['translation_type' => 'rule', 'translation_code' => 'same', 'form_id' => null, 'translation' => 'The :attribute and :other must match.'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.numeric', 'form_id' => null, 'translation' => 'The :attribute must be :size.'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.file', 'form_id' => null, 'translation' => 'The :attribute must be :size kilobytes.'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.string', 'form_id' => null, 'translation' => 'The :attribute must be :size characters.'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.array', 'form_id' => null, 'translation' => 'The :attribute must contain :size items.'],
                    ['translation_type' => 'rule', 'translation_code' => 'starts_with', 'form_id' => null, 'translation' => 'The :attribute must start with one of the following: :values'],
                    ['translation_type' => 'rule', 'translation_code' => 'string', 'form_id' => null, 'translation' => 'The :attribute must be a string.'],
                    ['translation_type' => 'rule', 'translation_code' => 'timezone', 'form_id' => null, 'translation' => 'The :attribute must be a valid zone.'],
                    ['translation_type' => 'rule', 'translation_code' => 'unique', 'form_id' => null, 'translation' => 'The :attribute has already been taken.'],
                    ['translation_type' => 'rule', 'translation_code' => 'uploaded', 'form_id' => null, 'translation' => 'The :attribute failed to upload.'],
                    ['translation_type' => 'rule', 'translation_code' => 'url', 'form_id' => null, 'translation' => 'The :attribute format is invalid.'],
                    ['translation_type' => 'rule', 'translation_code' => 'uuid', 'form_id' => null, 'translation' => 'The :attribute must be a valid UUID.'],
                    ['translation_type' => 'rule', 'translation_code' => 'username.regex', 'form_id' => null, 'translation' => 'The username must be consisted of letters and numbers.'],
                    ['translation_type' => 'rule', 'translation_code' => 'page_code.regex', 'form_id' => null, 'translation' => 'The page code must be consisted of 2 uppercases and 3 numbers.'],
                    ['translation_type' => 'rule', 'translation_code' => 'page_module.min', 'form_id' => null, 'translation' => "The page module isn't selected yet."],
                    ['translation_type' => 'rule', 'translation_code' => 'page_options.native.no_sql', 'form_id' => null, 'translation' => "The page native SQL isn't filled yet."],
                    ['translation_type' => 'rule', 'translation_code' => 'page_options.native.sql_error', 'form_id' => null, 'translation' => "The native SQL is wrong."],
                    ['translation_type' => 'rule', 'translation_code' => 'field_code.regex', 'form_id' => null, 'translation' => 'The field code must be consisted of English letters, numbers, or underline( _ ), and the first word can\'t be number or underline.'],
                    ['translation_type' => 'rule', 'translation_code' => 'checkboxes_in', 'form_id' => null, 'translation' => 'The :attribute must in options.'],
                    ['translation_type' => 'rule', 'translation_code' => 'checkboxes_required', 'form_id' => null, 'translation' => 'The :attribute must select one option.'],

                // Basic Fields
                    ['translation_type' => 'field', 'translation_code' => 'username', 'form_id' => null, 'translation' => 'Account'],
                    ['translation_type' => 'field', 'translation_code' => 'password', 'form_id' => null, 'translation' => 'Password'],
                    ['translation_type' => 'field', 'translation_code' => 'login', 'form_id' => null, 'translation' => 'Login'],
                    ['translation_type' => 'field', 'translation_code' => 'rememberme', 'form_id' => null, 'translation' => 'Remember Me'],
                    ['translation_type' => 'field', 'translation_code' => 'language', 'form_id' => null, 'translation' => 'Languages'],
                    ['translation_type' => 'field', 'translation_code' => 'pagination.previous', 'form_id' => null, 'translation' => 'Previous Page'],
                    ['translation_type' => 'field', 'translation_code' => 'pagination.next', 'form_id' => null, 'translation' => 'Next Page'],
                    ['translation_type' => 'field', 'translation_code' => 'new', 'form_id' => null, 'translation' => 'New'],
                    // ['translation_type' => 'field', 'translation_code' => 'save', 'form_id' => null, 'translation' => 'Save'],
                    ['translation_type' => 'field', 'translation_code' => 'delete', 'form_id' => null, 'translation' => 'Delete'],
                    ['translation_type' => 'field', 'translation_code' => 'remove', 'form_id' => null, 'translation' => 'Remove'],
                    ['translation_type' => 'field', 'translation_code' => 'item', 'form_id' => null, 'translation' => 'Item'],
                    ['translation_type' => 'field', 'translation_code' => 'number', 'form_id' => null, 'translation' => 'Number'],
                    ['translation_type' => 'field', 'translation_code' => 'upper_case', 'form_id' => null, 'translation' => 'Upper case letter'],
                    ['translation_type' => 'field', 'translation_code' => 'lower_case', 'form_id' => null, 'translation' => 'Lower case letter'],
                    ['translation_type' => 'field', 'translation_code' => 'underline', 'form_id' => null, 'translation' => 'Underline'],
                    ['translation_type' => 'field', 'translation_code' => 'hyphen', 'form_id' => null, 'translation' => 'Hyphen'],
                    ['translation_type' => 'field', 'translation_code' => 'and', 'form_id' => null, 'translation' => 'And'],
                    ['translation_type' => 'field', 'translation_code' => 'or', 'form_id' => null, 'translation' => 'Or'],
                    ['translation_type' => 'field', 'translation_code' => 'unrestricted', 'form_id' => null, 'translation' => 'Unrestricted'],
                    ['translation_type' => 'field', 'translation_code' => 'other', 'form_id' => null, 'translation' => 'Other'],
                    ['translation_type' => 'field', 'translation_code' => 'field', 'form_id' => null, 'translation' => 'Field'],
                    ['translation_type' => 'field', 'translation_code' => 'show', 'form_id' => null, 'translation' => 'Show'],
                    ['translation_type' => 'field', 'translation_code' => 'order', 'form_id' => null, 'translation' => 'Order'],
                    ['translation_type' => 'field', 'translation_code' => 'target', 'form_id' => null, 'translation' => 'Target'],
                    ['translation_type' => 'field', 'translation_code' => 'level_number', 'form_id' => null, 'translation' => 'Level :number'],
                    ['translation_type' => 'field', 'translation_code' => 'logical_operator', 'form_id' => null, 'translation' => 'Logic Operator'],
                    ['translation_type' => 'field', 'translation_code' => 'comparison_operator', 'form_id' => null, 'translation' => 'Comparison Operator'],
                    ['translation_type' => 'field', 'translation_code' => 'value', 'form_id' => null, 'translation' => 'Value'],
                    ['translation_type' => 'field', 'translation_code' => 'user', 'form_id' => null, 'translation' => 'User'],
                    ['translation_type' => 'field', 'translation_code' => 'group', 'form_id' => null, 'translation' => 'Group'],

                    ['translation_type' => 'field', 'translation_code' => 'position', 'form_id' => null, 'translation' => 'Position'],
                    ['translation_type' => 'field', 'translation_code' => 'string', 'form_id' => null, 'translation' => 'String'],
                    ['translation_type' => 'field', 'translation_code' => 'textarea', 'form_id' => null, 'translation' => 'Text Area'],
                    ['translation_type' => 'field', 'translation_code' => 'integer', 'form_id' => null, 'translation' => 'Integer'],
                    ['translation_type' => 'field', 'translation_code' => 'decimal', 'form_id' => null, 'translation' => 'Decimal'],
                    ['translation_type' => 'field', 'translation_code' => 'boolean', 'form_id' => null, 'translation' => 'Switch'],
                    ['translation_type' => 'field', 'translation_code' => 'select', 'form_id' => null, 'translation' => 'Select'],
                    ['translation_type' => 'field', 'translation_code' => 'checkboxes', 'form_id' => null, 'translation' => 'Checkboxes'],
                    ['translation_type' => 'field', 'translation_code' => 'radio', 'form_id' => null, 'translation' => 'Radio Button'],
                    ['translation_type' => 'field', 'translation_code' => 'date', 'form_id' => null, 'translation' => 'Date'],
                    ['translation_type' => 'field', 'translation_code' => 'time', 'form_id' => null, 'translation' => 'Time'],
                    ['translation_type' => 'field', 'translation_code' => 'datetime', 'form_id' => null, 'translation' => 'Date & Time'],
                    ['translation_type' => 'field', 'translation_code' => 'file', 'form_id' => null, 'translation' => 'Upload File'],
                    ['translation_type' => 'field', 'translation_code' => 'reference', 'form_id' => null, 'translation' => 'Reference Data'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_page', 'form_id' => null, 'translation' => 'Reference Page'],
                    ['translation_type' => 'field', 'translation_code' => 'button', 'form_id' => null, 'translation' => 'Button'],

                // Messages
                    ['translation_type' => 'message', 'translation_code' => 'of', 'form_id' => null, 'translation' => ":b of :a"],
                    ['translation_type' => 'message', 'translation_code' => 'index', 'form_id' => null, 'translation' => 'Home'],
                    ['translation_type' => 'message', 'translation_code' => 'main_content', 'form_id' => null, 'translation' => 'Main Content'],
                    ['translation_type' => 'message', 'translation_code' => 'data_per_page', 'form_id' => null, 'translation' => 'Datas per page'],
                    ['translation_type' => 'message', 'translation_code' => 'user_is_disabled', 'form_id' => null, 'translation' => 'This user has been disabled.'],
                    ['translation_type' => 'message', 'translation_code' => 'logout', 'form_id' => null, 'translation' => 'Logout'],
                    ['translation_type' => 'message', 'translation_code' => 'welcome', 'form_id' => null, 'translation' => 'Welcome'],
                    ['translation_type' => 'message', 'translation_code' => 'yes', 'form_id' => null, 'translation' => 'Yes'],
                    ['translation_type' => 'message', 'translation_code' => 'no', 'form_id' => null, 'translation' => 'No'],
                    ['translation_type' => 'message', 'translation_code' => 'selecting', 'form_id' => null, 'translation' => 'Select'],
                    ['translation_type' => 'message', 'translation_code' => 'confirm', 'form_id' => null, 'translation' => 'Confirm'],
                    ['translation_type' => 'message', 'translation_code' => 'cancel', 'form_id' => null, 'translation' => 'Cancel'],
                    ['translation_type' => 'message', 'translation_code' => 'close', 'form_id' => null, 'translation' => 'Close'],
                    ['translation_type' => 'message', 'translation_code' => 'clear', 'form_id' => null, 'translation' => 'Clear'],
                    ['translation_type' => 'message', 'translation_code' => 'fill_one', 'form_id' => null, 'translation' => 'Please fill in at least one.'],
                    ['translation_type' => 'message', 'translation_code' => 'loading', 'form_id' => null, 'translation' => 'Loading'],
                    ['translation_type' => 'message', 'translation_code' => 'processing', 'form_id' => null, 'translation' => 'Processing'],
                    ['translation_type' => 'message', 'translation_code' => 'accessing', 'form_id' => null, 'translation' => 'Accessing'],
                    ['translation_type' => 'message', 'translation_code' => 'redirecting', 'form_id' => null, 'translation' => 'Redirecting'],
                    ['translation_type' => 'message', 'translation_code' => 'reloading', 'form_id' => null, 'translation' => 'Reloading'],
                    ['translation_type' => 'message', 'translation_code' => 'outputing', 'form_id' => null, 'translation' => 'Outputing'],
                    ['translation_type' => 'message', 'translation_code' => 'translation', 'form_id' => null, 'translation' => 'Translation'],
                    ['translation_type' => 'message', 'translation_code' => 'readonly', 'form_id' => null, 'translation' => 'Readonly'],
                    ['translation_type' => 'message', 'translation_code' => 'default', 'form_id' => null, 'translation' => 'Default'],
                    ['translation_type' => 'message', 'translation_code' => 'custom', 'form_id' => null, 'translation' => 'Custom'],
                    ['translation_type' => 'message', 'translation_code' => 'type', 'form_id' => null, 'translation' => "Type"],
                    ['translation_type' => 'message', 'translation_code' => 'list', 'form_id' => null, 'translation' => "List"],
                    ['translation_type' => 'message', 'translation_code' => 'level', 'form_id' => null, 'translation' => 'Level'],
                    ['translation_type' => 'message', 'translation_code' => 'name', 'form_id' => null, 'translation' => 'Name'],
                    ['translation_type' => 'message', 'translation_code' => 'notification', 'form_id' => null, 'translation' => "Notification"],
                    ['translation_type' => 'message', 'translation_code' => 'profile', 'form_id' => null, 'translation' => "Profile"],
                    ['translation_type' => 'message', 'translation_code' => 'menu', 'form_id' => null, 'translation' => "Menu"],
                    ['translation_type' => 'message', 'translation_code' => 'unsave_confirm', 'form_id' => null, 'translation' => "If cancel what has changed won't be saved, are you sure?"],
                    ['translation_type' => 'message', 'translation_code' => 'row_with_number', 'form_id' => null, 'translation' => "Row :row"],
                    ['translation_type' => 'message', 'translation_code' => 'user_deleted', 'form_id' => null, 'translation' => "This user has been deleted"],
                    ['translation_type' => 'message', 'translation_code' => 'cannot_remove_saved', 'form_id' => null, 'translation' => "Can't remove which :item has been saved."],
                    ['translation_type' => 'message', 'translation_code' => 'contact_maintenance', 'form_id' => null, 'translation' => "Please contact the maintenance personnel."],
                    ['translation_type' => 'message', 'translation_code' => 'save_success', 'form_id' => null, 'translation' => "Saved successfully."],
                    ['translation_type' => 'message', 'translation_code' => 'warning', 'form_id' => null, 'translation' => 'Warning'],
                    ['translation_type' => 'message', 'translation_code' => 'access_dined', 'form_id' => null, 'translation' => 'Access dined'],
                    ['translation_type' => 'message', 'translation_code' => 'data_count_exceeded', 'form_id' => null, 'translation' => 'Data count exceeded.'],

                    ['translation_type' => 'message', 'translation_code' => 'error.unknown', 'form_id' => null, 'translation' => "An unknown error occurred, "],
                    ['translation_type' => 'message', 'translation_code' => 'error.check_permission', 'form_id' => null, 'translation' => 'Please check you have the permission to do this.'],

                    ['translation_type' => 'message', 'translation_code' => 'messages.fillOrSelectAll', 'form_id' => null, 'translation' => 'Please select or input all fields.'],

                    ['translation_type' => 'message', 'translation_code' => 'delete.confirm', 'form_id' => null, 'translation' => 'Are you sure want to delete this data?'],
                    ['translation_type' => 'message', 'translation_code' => 'delete.successful', 'form_id' => null, 'translation' => 'Delete successful.'],
                    ['translation_type' => 'message', 'translation_code' => 'delete.failed', 'form_id' => null, 'translation' => 'Delete failed.'],

                    ['translation_type' => 'message', 'translation_code' => 'view', 'form_id' => null, 'translation' => 'View'],
                    ['translation_type' => 'message', 'translation_code' => 'add', 'form_id' => null, 'translation' => 'Add'],
                    ['translation_type' => 'message', 'translation_code' => 'delete', 'form_id' => null, 'translation' => 'Delete'],
                    ['translation_type' => 'message', 'translation_code' => 'edit', 'form_id' => null, 'translation' => 'Edit'],
                    ['translation_type' => 'message', 'translation_code' => 'copy', 'form_id' => null, 'translation' => 'Copy'],
                    ['translation_type' => 'message', 'translation_code' => 'save', 'form_id' => null, 'translation' => 'Save'],
                    ['translation_type' => 'message', 'translation_code' => 'output', 'form_id' => null, 'translation' => 'Output'],
                    ['translation_type' => 'message', 'translation_code' => 'download', 'form_id' => null, 'translation' => 'Download'],
                    ['translation_type' => 'message', 'translation_code' => 'output_format', 'form_id' => null, 'translation' => 'Output Format'],
                    ['translation_type' => 'message', 'translation_code' => 'preview', 'form_id' => null, 'translation' => 'Preview'],
                    ['translation_type' => 'message', 'translation_code' => 'query', 'form_id' => null, 'translation' => 'Query'],
                    ['translation_type' => 'message', 'translation_code' => 'verify', 'form_id' => null, 'translation' => 'Verify'],
                    ['translation_type' => 'message', 'translation_code' => 'report', 'form_id' => null, 'translation' => 'Download Report'],

                    ['translation_type' => 'message', 'translation_code' => 'field', 'form_id' => null, 'translation' => 'Field'],
                    ['translation_type' => 'message', 'translation_code' => 'form', 'form_id' => null, 'translation' => 'Form'],
                    ['translation_type' => 'message', 'translation_code' => 'content', 'form_id' => null, 'translation' => 'Content'],

                    ['translation_type' => 'message', 'translation_code' => 'condition', 'form_id' => null, 'translation' => 'Condition'],
                    ['translation_type' => 'message', 'translation_code' => 'filter', 'form_id' => null, 'translation' => 'Filter'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.requiredFrontField', 'form_id' => null, 'translation' => 'Please fill :field .'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.all_field', 'form_id' => null, 'translation' => 'All fields'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.group', 'form_id' => null, 'translation' => 'Filter Group'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.condition', 'form_id' => null, 'translation' => 'And / Or'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.condition.and', 'form_id' => null, 'translation' => 'And'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.condition.or', 'form_id' => null, 'translation' => 'Or'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator', 'form_id' => null, 'translation' => 'Operator'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.=', 'form_id' => null, 'translation' => 'Equal'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.!=', 'form_id' => null, 'translation' => 'Not equal'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.>', 'form_id' => null, 'translation' => 'More than the'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.>=', 'form_id' => null, 'translation' => 'Greater or equal to'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.<', 'form_id' => null, 'translation' => 'Less than'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.<=', 'form_id' => null, 'translation' => 'Less than or equal to'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.like', 'form_id' => null, 'translation' => 'Contain'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.not like', 'form_id' => null, 'translation' => 'Not contain'],

                    ['translation_type' => 'message', 'translation_code' => 'translation_setting', 'form_id' => null, 'translation' => 'Translations'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.type', 'form_id' => null, 'translation' => 'Type'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.code', 'form_id' => null, 'translation' => 'Code'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.types.message', 'form_id' => null, 'translation' => 'Message'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.types.rule', 'form_id' => null, 'translation' => 'Validation message'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.types.var', 'form_id' => null, 'translation' => 'Variable'],

                    ['translation_type' => 'message', 'translation_code' => 'reference.error.required_front_field', 'form_id' => null, 'translation' => 'Please input the :field first.'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.no_view', 'form_id' => null, 'translation' => 'The view table of reference is not found, '],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.join_error', 'form_id' => null, 'translation' => 'The setting of data source tables is error, '],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.field_error', 'form_id' => null, 'translation' => 'The setting of data source fields is error, '],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.where_error', 'form_id' => null, 'translation' => 'The setting of data source filter is error, '],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.front_error', 'form_id' => null, 'translation' => 'The setting of front field is error, '],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.no_front', 'form_id' => null, 'translation' => 'The front field is no data.'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.filter_error', 'form_id' => null, 'translation' => 'The data of filter is error, '],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.reference_error', 'form_id' => null, 'translation' => 'The setting of reference is error, '],

                    ['translation_type' => 'message', 'translation_code' => 'verifier', 'form_id' => null, 'translation' => 'Verifier'],
                    ['translation_type' => 'message', 'translation_code' => 'verify_at', 'form_id' => null, 'translation' => 'Verifier At'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.start', 'form_id' => null, 'translation' => 'Verification Start'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.confirm', 'form_id' => null, 'translation' => 'Confirm Verification'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.return', 'form_id' => null, 'translation' => 'Verification Return'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.init', 'form_id' => null, 'translation' => 'Verification Initial'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.error.delete_null', 'form_id' => null, 'translation' => 'The verify of this page hasn\'t be setted.'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.error.level', 'form_id' => null, 'translation' => 'The level of verify is error.'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.error.had_verified', 'form_id' => null, 'translation' => 'You had verified this data.'],

                    ['translation_type' => 'message', 'translation_code' => 'log', 'form_id' => null, 'translation' => 'Log'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.parent', 'form_id' => null, 'translation' => 'Parent data'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.child', 'form_id' => null, 'translation' => 'Child data'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.relation', 'form_id' => null, 'translation' => 'Related data'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.old', 'form_id' => null, 'translation' => 'Older data'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.new', 'form_id' => null, 'translation' => 'Newer data'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.this', 'form_id' => null, 'translation' => 'This data'],

                // Pages
                    ['translation_type' => 'page', 'translation_code' => 'SY', 'form_id' => null, 'translation' => 'System'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_USER_MANAGE', 'form_id' => null, 'translation' => 'User Management'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_USERS', 'form_id' => null, 'translation' => 'Users'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_VERIFIES', 'form_id' => null, 'translation' => 'Verifys'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_GROUPS', 'form_id' => null, 'translation' => 'Groups'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_GROUP_USER', 'form_id' => null, 'translation' => 'Groups Form Body'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATION_SETTING', 'form_id' => null, 'translation' => 'Notifications'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATIONS', 'form_id' => null, 'translation' => 'Notifications Content'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATION_USER', 'form_id' => null, 'translation' => 'Notifications User Information'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATION_TARGET', 'form_id' => null, 'translation' => 'Notifications Object'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_PAGE_MANAGE', 'form_id' => null, 'translation' => 'Page Management'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_PAGES', 'form_id' => null, 'translation' => 'Pages'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_MODULES', 'form_id' => null, 'translation' => 'Modules'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_LANGUAGE_MANAGE', 'form_id' => null, 'translation' => 'Language Management'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_LANGUAGES', 'form_id' => null, 'translation' => 'Languages'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_TRANSLATION', 'form_id' => null, 'translation' => 'Translation'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_LOG', 'form_id' => null, 'translation' => 'System Logs'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_PARAMETERS', 'form_id' => null, 'translation' => 'Parameters'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_PERMISSIONS', 'form_id' => null, 'translation' => 'Permissions'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_FORMS', 'form_id' => null, 'translation' => 'Form Setting'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_FIELDS', 'form_id' => null, 'translation' => 'Field Setting'],

                    ['translation_type' => 'page', 'translation_code' => 'DT', 'form_id' => null, 'translation' => 'Developer Tools'],
                    ['translation_type' => 'page', 'translation_code' => 'DT_SCHEDULES', 'form_id' => null, 'translation' => 'Schedules'],

                // Fields for User
                    ['translation_type' => 'field', 'translation_code' => 'name', 'form_id' => $Form_SY_USERS->form_id, 'translation' => 'Name'],
                    ['translation_type' => 'field', 'translation_code' => 'password_confirmation', 'form_id' => $Form_SY_USERS->form_id, 'translation' => 'Password Confirmation'],
                    ['translation_type' => 'field', 'translation_code' => 'user_disabled', 'form_id' => $Form_SY_USERS->form_id, 'translation' => 'Disabled'],
                    ['translation_type' => 'field', 'translation_code' => 'user_remarks', 'form_id' => $Form_SY_USERS->form_id, 'translation' => 'Remarks'],
                    ['translation_type' => 'message', 'translation_code' => 'user_setting', 'form_id' => null, 'translation' => 'User'],
                    ['translation_type' => 'message', 'translation_code' => 'agent_setting', 'form_id' => null, 'translation' => 'Agent'],

                // Fields for User Agent
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_target_type', 'form_id' => null, 'translation' => 'Agent Target Type'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_target_id', 'form_id' => null, 'translation' => 'Agent Target'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_target_name', 'form_id' => null, 'translation' => 'Target Name'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_enabled', 'form_id' => null, 'translation' => 'Enabled'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_enabled_at', 'form_id' => null, 'translation' => 'Enable At'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_disabled_at', 'form_id' => null, 'translation' => 'Disable At'],

                // Fields for Group Page
                    ['translation_type' => 'field', 'translation_code' => 'group_new', 'form_id' => null, 'translation' => 'New Group'],
                    ['translation_type' => 'field', 'translation_code' => 'group_name', 'form_id' => null, 'translation' => 'Group Name'],
                // Fields for Languages Page
                    ['translation_type' => 'field', 'translation_code' => 'language_code', 'form_id' => $Form_SY_LANGUAGES->form_id, 'translation' => 'Code'],
                    ['translation_type' => 'field', 'translation_code' => 'language_name', 'form_id' => $Form_SY_LANGUAGES->form_id, 'translation' => 'Name'],
                    ['translation_type' => 'field', 'translation_code' => 'translation_type', 'form_id' => $Form_SY_LANGUAGES->form_id, 'translation' => 'Language'],
                    ['translation_type' => 'field', 'translation_code' => 'translation_code', 'form_id' => $Form_SY_LANGUAGES->form_id, 'translation' => 'Code'],

                // Fields for Parameters Page
                    ['translation_type' => 'field', 'translation_code' => 'parameter_new', 'form_id' => null, 'translation' => 'New Parameter'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_code', 'form_id' => null, 'translation' => 'Code'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_value', 'form_id' => null, 'translation' => 'Value'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_deletable', 'form_id' => null, 'translation' => 'Deletable'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_remarks', 'form_id' => null, 'translation' => 'Remarks'],

                // Fields for Schedule Page
                    ['translation_type' => 'field', 'translation_code' => 'schedule_new', 'form_id' => null, 'translation' => 'New Schedule'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_name', 'form_id' => null, 'translation' => 'Name'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_fun', 'form_id' => null, 'translation' => 'Function'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_active', 'form_id' => null, 'translation' => 'Enable'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_remarks', 'form_id' => null, 'translation' => 'Remarks'],

                // Fields for Verify Page
                    ['translation_type' => 'field', 'translation_code' => 'field_code', 'form_id' => $Form_SY_VERIFY_CONDITION->form_id, 'translation' => 'Field'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_comparison', 'form_id' => null, 'translation' => 'Operator'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_value', 'form_id' => null, 'translation' => 'Value'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_target_id', 'form_id' => null, 'translation' => 'Target'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_target_name', 'form_id' => null, 'translation' => 'Target Name'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_target_type', 'form_id' => null, 'translation' => 'Type'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_population', 'form_id' => null, 'translation' => 'Population'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_population_max', 'form_id' => null, 'translation' => 'Population Max'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_logical', 'form_id' => null, 'translation' => 'Logic'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_condition_group', 'form_id' => null, 'translation' => 'Logic Group'],

                // Fields for Pages Page
                    ['translation_type' => 'field', 'translation_code' => 'module', 'form_id' => null, 'translation' => 'Module'],
                    ['translation_type' => 'field', 'translation_code' => 'submodule', 'form_id' => null, 'translation' => 'Sub Module'],
                    ['translation_type' => 'field', 'translation_code' => 'page', 'form_id' => null, 'translation' => 'Page'],
                    ['translation_type' => 'field', 'translation_code' => 'page_code', 'form_id' => null, 'translation' => 'Code'],
                    ['translation_type' => 'field', 'translation_code' => 'page_name', 'form_id' => null, 'translation' => 'Name'],
                    ['translation_type' => 'field', 'translation_code' => 'page_module', 'form_id' => null, 'translation' => 'Module'],
                    ['translation_type' => 'field', 'translation_code' => 'page_visible', 'form_id' => null, 'translation' => 'Visible'],
                    ['translation_type' => 'field', 'translation_code' => 'page_order', 'form_id' => null, 'translation' => 'Order'],
                    ['translation_type' => 'field', 'translation_code' => 'page_remarks', 'form_id' => null, 'translation' => 'Remarks'],
                    ['translation_type' => 'field', 'translation_code' => 'page_setting', 'form_id' => null, 'translation' => 'Page'],
                    ['translation_type' => 'field', 'translation_code' => 'module_setting', 'form_id' => null, 'translation' => 'Module'],
                    ['translation_type' => 'field', 'translation_code' => 'field_setting', 'form_id' => null, 'translation' => 'Fields'],
                    ['translation_type' => 'field', 'translation_code' => 'page_list_template', 'form_id' => null, 'translation' => 'List Template'],
                    ['translation_type' => 'field', 'translation_code' => 'page_form_template', 'form_id' => null, 'translation' => 'Form Template'],
                    ['translation_type' => 'field', 'translation_code' => 'page_template', 'form_id' => null, 'translation' => 'Page Template'],
                    ['translation_type' => 'field', 'translation_code' => 'page_readonly', 'form_id' => null, 'translation' => 'Readonly'],
                    ['translation_type' => 'field', 'translation_code' => 'page_has_body', 'form_id' => null, 'translation' => 'Has body'],
                    ['translation_type' => 'field', 'translation_code' => 'page_body_number', 'form_id' => null, 'translation' => 'Number of body'],
                    ['translation_type' => 'field', 'translation_code' => 'page_allow_empty_body', 'form_id' => null, 'translation' => 'Body Nullable'],
                    ['translation_type' => 'field', 'translation_code' => 'page_max', 'form_id' => null, 'translation' => 'Upper limit of data'],
                    ['translation_type' => 'field', 'translation_code' => 'page_head', 'form_id' => null, 'translation' => 'Head'],
                    ['translation_type' => 'field', 'translation_code' => 'page_body', 'form_id' => null, 'translation' => 'Body'],
                    ['translation_type' => 'message', 'translation_code' => 'page_max_message', 'form_id' => null, 'translation' => '-1 expresses unlimited.'],
                    ['translation_type' => 'message', 'translation_code' => 'attached_to', 'form_id' => null, 'translation' => "Attached to"],
                    ['translation_type' => 'message', 'translation_code' => 'edit_order', 'form_id' => null, 'translation' => "Edit Order"],
                    ['translation_type' => 'message', 'translation_code' => 'field_type_error', 'form_id' => null, 'translation' => "field type conversion is error."],
                    ['translation_type' => 'field', 'translation_code' => 'savable', 'form_id' => null, 'translation' => "Savable"],
                    ['translation_type' => 'field', 'translation_code' => 'query_mode', 'form_id' => null, 'translation' => "Query Mode"],
                    /* ['translation_type' => 'field', 'translation_code' => 'data_source', 'form_id' => null, 'translation' => "Data Source"],
                    ['translation_type' => 'field', 'translation_code' => 'independent_form', 'form_id' => null, 'translation' => "Independent Form"], */

                // Translations for Fields
                    ['translation_type' => 'field', 'translation_code' => 'field_code', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Code'],
                    ['translation_type' => 'field', 'translation_code' => 'field_type', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Type'],
                    ['translation_type' => 'field', 'translation_code' => 'field_rule', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Rules'],
                    ['translation_type' => 'field', 'translation_code' => 'field_order', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Order'],
                    ['translation_type' => 'field', 'translation_code' => 'field_default_value', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Default'],
                    ['translation_type' => 'field', 'translation_code' => 'field_required', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Required'],
                    ['translation_type' => 'field', 'translation_code' => 'field_readonly', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Readonly'],
                    ['translation_type' => 'field', 'translation_code' => 'field_show_on_form', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Show on form'],
                    ['translation_type' => 'field', 'translation_code' => 'field_show_on_list', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Show on list'],
                    ['translation_type' => 'field', 'translation_code' => 'field_options', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Special Setting'],
                    ['translation_type' => 'field', 'translation_code' => 'field_remarks', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Remarks'],
                    ['translation_type' => 'field', 'translation_code' => 'field_details', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Details'],
                    ['translation_type' => 'field', 'translation_code' => 'field_wide', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Column Wide'],
                    ['translation_type' => 'field', 'translation_code' => 'wide_label', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => 'Column Width'],
                    ['translation_type' => 'message', 'translation_code' => 'fill_default_first', 'form_id' => null, 'translation' => 'Please fill the default first.'],
                    ['translation_type' => 'field', 'translation_code' => 'editable', 'form_id' => null, 'translation' => 'Editable'],
                    ['translation_type' => 'field', 'translation_code' => 'cloneable', 'form_id' => null, 'translation' => 'Cloneable'],
                    ['translation_type' => 'field', 'translation_code' => 'decimal_options', 'form_id' => null, 'translation' => 'Decimal options'],
                    ['translation_type' => 'field', 'translation_code' => 'integer_digits', 'form_id' => null, 'translation' => 'Integer digits'],
                    ['translation_type' => 'field', 'translation_code' => 'decimal_digits', 'form_id' => null, 'translation' => 'Decimal digits'],
                    ['translation_type' => 'field', 'translation_code' => 'number_digits', 'form_id' => null, 'translation' => 'Number Digits'],
                    ['translation_type' => 'message', 'translation_code' => 'min_bigger', 'form_id' => null, 'translation' => "Min can't bigger than Max"],
                    ['translation_type' => 'message', 'translation_code' => 'field_no_details', 'form_id' => null, 'translation' => "hasn't be setted field details."],
                    ['translation_type' => 'message', 'translation_code' => 'field_type_first', 'form_id' => null, 'translation' => "Please select the field type first."],
                    ['translation_type' => 'field', 'translation_code' => 'options_options', 'form_id' => null, 'translation' => 'Options'],
                    ['translation_type' => 'field', 'translation_code' => 'file_type', 'form_id' => null, 'translation' => 'File Type'],
                    ['translation_type' => 'field', 'translation_code' => 'file_image', 'form_id' => null, 'translation' => 'Image File'],
                    ['translation_type' => 'field', 'translation_code' => 'file_video', 'form_id' => null, 'translation' => 'Video File'],
                    ['translation_type' => 'field', 'translation_code' => 'file_audio', 'form_id' => null, 'translation' => 'Audio File'],
                    ['translation_type' => 'field', 'translation_code' => 'file_document', 'form_id' => null, 'translation' => 'Document'],
                    ['translation_type' => 'field', 'translation_code' => 'file_spread_sheet', 'form_id' => null, 'translation' => 'Spread Sheet'],
                    ['translation_type' => 'field', 'translation_code' => 'file_presentation', 'form_id' => null, 'translation' => 'Presentation'],
                    ['translation_type' => 'field', 'translation_code' => 'file_pdf', 'form_id' => null, 'translation' => 'PDF'],
                    ['translation_type' => 'field', 'translation_code' => 'file_csv', 'form_id' => null, 'translation' => 'CSV'],
                    ['translation_type' => 'field', 'translation_code' => 'file_archive', 'form_id' => null, 'translation' => 'Archived File'],
                    ['translation_type' => 'field', 'translation_code' => 'file_text', 'form_id' => null, 'translation' => 'Text File'],
                    ['translation_type' => 'field', 'translation_code' => 'common_setting', 'form_id' => null, 'translation' => 'Common Setting'],
                    ['translation_type' => 'field', 'translation_code' => 'field_list', 'form_id' => null, 'translation' => 'Field List'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_source_field', 'form_id' => null, 'translation' => 'Data Source of Field'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_source_table', 'form_id' => null, 'translation' => 'Data Source of Form'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_where', 'form_id' => null, 'translation' => 'Data Source Filter'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_other', 'form_id' => null, 'translation' => 'Other Setting'],
                    ['translation_type' => 'field', 'translation_code' => 'join_left', 'form_id' => null, 'translation' => 'Join Field'],
                    ['translation_type' => 'field', 'translation_code' => 'join_right', 'form_id' => null, 'translation' => 'Join Target'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_front', 'form_id' => null, 'translation' => 'Front Field'],
                    ['translation_type' => 'field', 'translation_code' => 'native_sql', 'form_id' => null, 'translation' => 'Native SQL'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_select', 'form_id' => null, 'translation' => 'Reference Select'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_multiple', 'form_id' => null, 'translation' => 'Multiple Mode'],

                // Fields for Notification User Page
                    ['translation_type' => 'field', 'translation_code' => 'notification_user_phone', 'form_id' => null, 'translation' => 'Phone'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_user_email', 'form_id' => null, 'translation' => 'E-mail'],

                // Fields for Log Page
                    ['translation_type' => 'field', 'translation_code' => 'log_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Log ID'],
                    ['translation_type' => 'field', 'translation_code' => 'page_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Page'],
                    ['translation_type' => 'field', 'translation_code' => 'form_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Form'],
                    ['translation_type' => 'field', 'translation_code' => 'id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Data ID'],
                    ['translation_type' => 'field', 'translation_code' => 'parent_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Parent Data ID'],
                    ['translation_type' => 'field', 'translation_code' => 'action', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Action'],
                    ['translation_type' => 'field', 'translation_code' => 'data', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Data'],
                    ['translation_type' => 'field', 'translation_code' => 'created_at', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Created at'],
                    ['translation_type' => 'field', 'translation_code' => 'created_by', 'form_id' => $Form_SY_LOG->form_id, 'translation' => 'Created by'],
        ]);

        $Language_zhTW->translation()->createMany([
                // Rules
                    ['translation_type' => 'rule', 'translation_code' => 'rule_unique', 'form_id' => null, 'translation' => '資料表唯一值'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_distinct', 'form_id' => null, 'translation' => '表身唯一值'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_word_limit', 'form_id' => null, 'translation' => '字數限制'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_string_content', 'form_id' => null, 'translation' => '字串內容'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_letter_numeric', 'form_id' => null, 'translation' => '英數組合'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_number_limit', 'form_id' => null, 'translation' => '數值限制'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_digits_limit', 'form_id' => null, 'translation' => '位數限制'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_max', 'form_id' => null, 'translation' => '最大值'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_min', 'form_id' => null, 'translation' => '最小值'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_url', 'form_id' => null, 'translation' => '網址格式'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_email', 'form_id' => null, 'translation' => 'E-mail格式'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_in', 'form_id' => null, 'translation' => '包含於'],
                    ['translation_type' => 'rule', 'translation_code' => 'rule_not_in', 'form_id' => null, 'translation' => '不包含於'],
                // Rule Message
                    ['translation_type' => 'rule', 'translation_code' => 'accepted', 'form_id' => null, 'translation' => '必須接受 :attribute。'],
                    ['translation_type' => 'rule', 'translation_code' => 'active_url', 'form_id' => null, 'translation' => ':attribute 不是有效的網址。'],
                    ['translation_type' => 'rule', 'translation_code' => 'after', 'form_id' => null, 'translation' => ':attribute 必須要晚於 :date。'],
                    ['translation_type' => 'rule', 'translation_code' => 'after_or_equal', 'form_id' => null, 'translation' => ':attribute 必須要等於 :date 或更晚。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha', 'form_id' => null, 'translation' => ':attribute 只能以字母組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha_dash', 'form_id' => null, 'translation' => ':attribute 只能以字母、數字、連接線(-)及底線(_)組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha_num', 'form_id' => null, 'translation' => ':attribute 只能以字母及數字組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet_number_dash', 'form_id' => null, 'translation' => ':attribute 只能以字母、數字、連接線(-)及底線(_)組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alpha_dash', 'form_id' => null, 'translation' => ':attribute 只能以字母、連接線(-)及底線(_)組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet_dash', 'form_id' => null, 'translation' => ':attribute 只能以字母、連接線及底線(_)組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet_number', 'form_id' => null, 'translation' => ':attribute 只能以字母及數字組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'alphabet', 'form_id' => null, 'translation' => ':attribute 只能以字母組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'array', 'form_id' => null, 'translation' => ':attribute 必須為陣列。'],
                    ['translation_type' => 'rule', 'translation_code' => 'before', 'form_id' => null, 'translation' => ':attribute 必須要早於 :date。'],
                    ['translation_type' => 'rule', 'translation_code' => 'before_or_equal', 'form_id' => null, 'translation' => ':attribute 必須要等於 :date 或更早。'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.numeric', 'form_id' => null, 'translation' => ':attribute 必須介於 :min 至 :max 之間。'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.file', 'form_id' => null, 'translation' => ':attribute 必須介於 :min 至 :max KB 之間。 '],
                    ['translation_type' => 'rule', 'translation_code' => 'between.string', 'form_id' => null, 'translation' => ':attribute 必須介於 :min 至 :max 個字元之間。'],
                    ['translation_type' => 'rule', 'translation_code' => 'between.array', 'form_id' => null, 'translation' => ':attribute: 必須有 :min - :max 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'boolean', 'form_id' => null, 'translation' => ':attribute 必須為布林值。'],
                    ['translation_type' => 'rule', 'translation_code' => 'confirmed', 'form_id' => null, 'translation' => ':attribute 確認欄位的輸入不一致。'],
                    ['translation_type' => 'rule', 'translation_code' => 'date', 'form_id' => null, 'translation' => ':attribute 不是有效的日期。'],
                    ['translation_type' => 'rule', 'translation_code' => 'date_equals', 'form_id' => null, 'translation' => ':attribute 必須等於 :date。'],
                    ['translation_type' => 'rule', 'translation_code' => 'date_format', 'form_id' => null, 'translation' => ':attribute 不符合 :format 的格式。'],
                    ['translation_type' => 'rule', 'translation_code' => 'different', 'form_id' => null, 'translation' => ':attribute 與 :other 必須不同。'],
                    ['translation_type' => 'rule', 'translation_code' => 'digits', 'form_id' => null, 'translation' => ':attribute 必須是 :digits 位數字。'],
                    ['translation_type' => 'rule', 'translation_code' => 'digits_between', 'form_id' => null, 'translation' => ':attribute 必須介於 :min 至 :max 位數字。'],
                    ['translation_type' => 'rule', 'translation_code' => 'dimensions', 'form_id' => null, 'translation' => ':attribute 圖片尺寸不正確。'],
                    ['translation_type' => 'rule', 'translation_code' => 'distinct', 'form_id' => null, 'translation' => ':attribute 已經存在。'],
                    ['translation_type' => 'rule', 'translation_code' => 'email', 'form_id' => null, 'translation' => ':attribute 必須是有效的 E-mail。'],
                    ['translation_type' => 'rule', 'translation_code' => 'ends_with', 'form_id' => null, 'translation' => ':attribute 結尾必須包含下列之一：:values'],
                    ['translation_type' => 'rule', 'translation_code' => 'exists', 'form_id' => null, 'translation' => ':attribute 不存在。'],
                    ['translation_type' => 'rule', 'translation_code' => 'file', 'form_id' => null, 'translation' => ':attribute 必須是有效的檔案。'],
                    ['translation_type' => 'rule', 'translation_code' => 'filled', 'form_id' => null, 'translation' => ':attribute 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.numeric', 'form_id' => null, 'translation' => ':attribute 必須大於 :value。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.file', 'form_id' => null, 'translation' => ':attribute 必須大於 :value KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.string', 'form_id' => null, 'translation' => ':attribute 必須多於 :value 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gt.array', 'form_id' => null, 'translation' => ':attribute 必須多於 :value 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.numeric', 'form_id' => null, 'translation' => ':attribute 必須大於或等於 :value。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.file', 'form_id' => null, 'translation' => ':attribute 必須大於或等於 :value KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.string', 'form_id' => null, 'translation' => ':attribute 必須多於或等於 :value 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'gte.array', 'form_id' => null, 'translation' => ':attribute 必須多於或等於 :value 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'image', 'form_id' => null, 'translation' => ':attribute 必須是一張圖片。'],
                    ['translation_type' => 'rule', 'translation_code' => 'in', 'form_id' => null, 'translation' => '所選擇的 :attribute 選項無效。'],
                    ['translation_type' => 'rule', 'translation_code' => 'in_array', 'form_id' => null, 'translation' => ':attribute 沒有在 :other 中。'],
                    ['translation_type' => 'rule', 'translation_code' => 'integer', 'form_id' => null, 'translation' => ':attribute 必須是一個整數。'],
                    ['translation_type' => 'rule', 'translation_code' => 'ip', 'form_id' => null, 'translation' => ':attribute 必須是一個有效的 IP 位址。'],
                    ['translation_type' => 'rule', 'translation_code' => 'ipv4', 'form_id' => null, 'translation' => ':attribute 必須是一個有效的 IPv4 位址。'],
                    ['translation_type' => 'rule', 'translation_code' => 'ipv6', 'form_id' => null, 'translation' => ':attribute 必須是一個有效的 IPv6 位址。'],
                    ['translation_type' => 'rule', 'translation_code' => 'json', 'form_id' => null, 'translation' => ':attribute 必須是正確的 JSON 字串。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.numeric', 'form_id' => null, 'translation' => ':attribute 必須小於 :value。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.file', 'form_id' => null, 'translation' => ':attribute 必須小於 :value KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.string', 'form_id' => null, 'translation' => ':attribute 必須少於 :value 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lt.array', 'form_id' => null, 'translation' => ':attribute 必須少於 :value 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.numeric', 'form_id' => null, 'translation' => ':attribute 必須小於或等於 :value。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.file', 'form_id' => null, 'translation' => ':attribute 必須小於或等於 :value KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.string', 'form_id' => null, 'translation' => ':attribute 必須少於或等於 :value 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'lte.array', 'form_id' => null, 'translation' => ':attribute 必須少於或等於 :value 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.numeric', 'form_id' => null, 'translation' => ':attribute 不能大於 :max。'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.file', 'form_id' => null, 'translation' => ':attribute 不能大於 :max KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.string', 'form_id' => null, 'translation' => ':attribute 不能多於 :max 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'max.array', 'form_id' => null, 'translation' => ':attribute 最多有 :max 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'mimes', 'form_id' => null, 'translation' => ':attribute 必須為 :values 的檔案。'],
                    ['translation_type' => 'rule', 'translation_code' => 'mimetypes', 'form_id' => null, 'translation' => ':attribute 必須為 :values 的檔案。'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.numeric', 'form_id' => null, 'translation' => ':attribute 不能小於 :min。'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.file', 'form_id' => null, 'translation' => ':attribute 不能小於 :min KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.string', 'form_id' => null, 'translation' => ':attribute 不能小於 :min 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'min.array', 'form_id' => null, 'translation' => ':attribute 至少有 :min 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'not_in', 'form_id' => null, 'translation' => '所選擇的 :attribute 選項無效。'],
                    ['translation_type' => 'rule', 'translation_code' => 'not_regex', 'form_id' => null, 'translation' => ':attribute 的格式錯誤。'],
                    ['translation_type' => 'rule', 'translation_code' => 'numeric', 'form_id' => null, 'translation' => ':attribute 必須為一個數字。'],
                    ['translation_type' => 'rule', 'translation_code' => 'password', 'form_id' => null, 'translation' => '密碼錯誤'],
                    ['translation_type' => 'rule', 'translation_code' => 'present', 'form_id' => null, 'translation' => ':attribute 必須存在。'],
                    ['translation_type' => 'rule', 'translation_code' => 'regex', 'form_id' => null, 'translation' => ':attribute 的格式錯誤。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required', 'form_id' => null, 'translation' => ':attribute 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_if', 'form_id' => null, 'translation' => '當 :other 是 :value 時 :attribute 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_unless', 'form_id' => null, 'translation' => '當 :other 不是 :values 時 :attribute 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_with', 'form_id' => null, 'translation' => '當 :values 出現時 :attribute 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_with_all', 'form_id' => null, 'translation' => '當 :values 出現時 :attribute 不能為空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_without', 'form_id' => null, 'translation' => '當 :values 留空時 :attribute field 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'required_without_all', 'form_id' => null, 'translation' => '當 :values 都不出現時 :attribute 不能留空。'],
                    ['translation_type' => 'rule', 'translation_code' => 'same', 'form_id' => null, 'translation' => ':attribute 與 :other 必須相同。'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.numeric', 'form_id' => null, 'translation' => ':attribute 的大小必須是 :size。'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.file', 'form_id' => null, 'translation' => ':attribute 的大小必須是 :size KB。'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.string', 'form_id' => null, 'translation' => ':attribute 必須是 :size 個字元。'],
                    ['translation_type' => 'rule', 'translation_code' => 'size.array', 'form_id' => null, 'translation' => ':attribute 必須是 :size 個元素。'],
                    ['translation_type' => 'rule', 'translation_code' => 'starts_with', 'form_id' => null, 'translation' => ':attribute 開頭必須包含下列之一：:values'],
                    ['translation_type' => 'rule', 'translation_code' => 'string', 'form_id' => null, 'translation' => ':attribute 必須是一個字串。'],
                    ['translation_type' => 'rule', 'translation_code' => 'timezone', 'form_id' => null, 'translation' => ':attribute 必須是一個正確的時區值。'],
                    ['translation_type' => 'rule', 'translation_code' => 'unique', 'form_id' => null, 'translation' => ':attribute 已經存在。'],
                    ['translation_type' => 'rule', 'translation_code' => 'uploaded', 'form_id' => null, 'translation' => ':attribute 上傳失敗。'],
                    ['translation_type' => 'rule', 'translation_code' => 'url', 'form_id' => null, 'translation' => ':attribute 的格式錯誤。'],
                    ['translation_type' => 'rule', 'translation_code' => 'uuid', 'form_id' => null, 'translation' => ':attribute 必須是有效的 UUID。'],
                    ['translation_type' => 'rule', 'translation_code' => 'username.regex', 'form_id' => null, 'translation' => '帳號 必須由英文和數字組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'page_code.regex', 'form_id' => null, 'translation' => '代碼 必須由2個大寫字母及3個數字組成。'],
                    ['translation_type' => 'rule', 'translation_code' => 'page_module.min', 'form_id' => null, 'translation' => '模組 尚未選擇。'],
                    ['translation_type' => 'rule', 'translation_code' => 'page_options.native.no_sql', 'form_id' => null, 'translation' => "原生SQL 尚未填寫。"],
                    ['translation_type' => 'rule', 'translation_code' => 'page_options.native.sql_error', 'form_id' => null, 'translation' => "原生SQL 填寫錯誤。"],
                    ['translation_type' => 'rule', 'translation_code' => 'field_code.regex', 'form_id' => null, 'translation' => '代碼 只能由英文字母、數字或底線( _ )組成，且首字不能為數字或底線。'],
                    ['translation_type' => 'rule', 'translation_code' => 'checkboxes_in', 'form_id' => null, 'translation' => ':attribute 必須在選項內。'],
                    ['translation_type' => 'rule', 'translation_code' => 'checkboxes_required', 'form_id' => null, 'translation' => ':attribute 必須選一個選項。'],

                // Basic Fields
                    ['translation_type' => 'field', 'translation_code' => 'username', 'form_id' => null, 'translation' => '帳號'],
                    ['translation_type' => 'field', 'translation_code' => 'password', 'form_id' => null, 'translation' => '密碼'],
                    ['translation_type' => 'field', 'translation_code' => 'login', 'form_id' => null, 'translation' => '登入'],
                    ['translation_type' => 'field', 'translation_code' => 'rememberme', 'form_id' => null, 'translation' => '記住我'],
                    ['translation_type' => 'field', 'translation_code' => 'language', 'form_id' => null, 'translation' => '語言'],
                    ['translation_type' => 'field', 'translation_code' => 'pagination.previous', 'form_id' => null, 'translation' => '上一頁'],
                    ['translation_type' => 'field', 'translation_code' => 'pagination.next', 'form_id' => null, 'translation' => '下一頁'],
                    ['translation_type' => 'field', 'translation_code' => 'new', 'form_id' => null, 'translation' => '新增'],
                    // ['translation_type' => 'field', 'translation_code' => 'save', 'form_id' => null, 'translation' => '儲存'],
                    ['translation_type' => 'field', 'translation_code' => 'delete', 'form_id' => null, 'translation' => '刪除'],
                    ['translation_type' => 'field', 'translation_code' => 'remove', 'form_id' => null, 'translation' => '移除'],
                    ['translation_type' => 'field', 'translation_code' => 'item', 'form_id' => null, 'translation' => '項目'],
                    ['translation_type' => 'field', 'translation_code' => 'number', 'form_id' => null, 'translation' => '數字'],
                    ['translation_type' => 'field', 'translation_code' => 'upper_case', 'form_id' => null, 'translation' => '大寫字母'],
                    ['translation_type' => 'field', 'translation_code' => 'lower_case', 'form_id' => null, 'translation' => '小寫字母'],
                    ['translation_type' => 'field', 'translation_code' => 'underline', 'form_id' => null, 'translation' => '底線'],
                    ['translation_type' => 'field', 'translation_code' => 'hyphen', 'form_id' => null, 'translation' => '連字號'],
                    ['translation_type' => 'field', 'translation_code' => 'and', 'form_id' => null, 'translation' => '和'],
                    ['translation_type' => 'field', 'translation_code' => 'or', 'form_id' => null, 'translation' => '或'],
                    ['translation_type' => 'field', 'translation_code' => 'unrestricted', 'form_id' => null, 'translation' => '無限制'],
                    ['translation_type' => 'field', 'translation_code' => 'other', 'form_id' => null, 'translation' => '其他'],
                    ['translation_type' => 'field', 'translation_code' => 'field', 'form_id' => null, 'translation' => '欄位'],
                    ['translation_type' => 'field', 'translation_code' => 'show', 'form_id' => null, 'translation' => '顯示'],
                    ['translation_type' => 'field', 'translation_code' => 'order', 'form_id' => null, 'translation' => '排序'],
                    ['translation_type' => 'field', 'translation_code' => 'target', 'form_id' => null, 'translation' => '目標'],
                    ['translation_type' => 'field', 'translation_code' => 'level_number', 'form_id' => null, 'translation' => '第:number層'],
                    ['translation_type' => 'field', 'translation_code' => 'logical_operator', 'form_id' => null, 'translation' => '邏輯運算子'],
                    ['translation_type' => 'field', 'translation_code' => 'comparison_operator', 'form_id' => null, 'translation' => '比較運算子'],
                    ['translation_type' => 'field', 'translation_code' => 'value', 'form_id' => null, 'translation' => '值'],
                    ['translation_type' => 'field', 'translation_code' => 'user', 'form_id' => null, 'translation' => '用戶'],
                    ['translation_type' => 'field', 'translation_code' => 'group', 'form_id' => null, 'translation' => '群組'],
                    ['translation_type' => 'field', 'translation_code' => 'position', 'form_id' => null, 'translation' => '位置'],
                    ['translation_type' => 'field', 'translation_code' => 'string', 'form_id' => null, 'translation' => '字串'],
                    ['translation_type' => 'field', 'translation_code' => 'textarea', 'form_id' => null, 'translation' => '文字區塊'],
                    ['translation_type' => 'field', 'translation_code' => 'integer', 'form_id' => null, 'translation' => '整數'],
                    ['translation_type' => 'field', 'translation_code' => 'decimal', 'form_id' => null, 'translation' => '小數'],
                    ['translation_type' => 'field', 'translation_code' => 'boolean', 'form_id' => null, 'translation' => '開關'],
                    ['translation_type' => 'field', 'translation_code' => 'select', 'form_id' => null, 'translation' => '下拉式選單'],
                    ['translation_type' => 'field', 'translation_code' => 'checkboxes', 'form_id' => null, 'translation' => '勾選項(複選)'],
                    ['translation_type' => 'field', 'translation_code' => 'radio', 'form_id' => null, 'translation' => '勾選項(單選)'],
                    ['translation_type' => 'field', 'translation_code' => 'date', 'form_id' => null, 'translation' => '日期'],
                    ['translation_type' => 'field', 'translation_code' => 'time', 'form_id' => null, 'translation' => '時間'],
                    ['translation_type' => 'field', 'translation_code' => 'datetime', 'form_id' => null, 'translation' => '日期時間'],
                    ['translation_type' => 'field', 'translation_code' => 'file', 'form_id' => null, 'translation' => '上傳檔案'],
                    ['translation_type' => 'field', 'translation_code' => 'reference', 'form_id' => null, 'translation' => '資料引用'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_page', 'form_id' => null, 'translation' => '小視窗'],
                    ['translation_type' => 'field', 'translation_code' => 'button', 'form_id' => null, 'translation' => '按鈕'],

                // Messages
                    ['translation_type' => 'message', 'translation_code' => 'of', 'form_id' => null, 'translation' => ":a 的 :b"],
                    ['translation_type' => 'message', 'translation_code' => 'index', 'form_id' => null, 'translation' => '首頁'],
                    ['translation_type' => 'message', 'translation_code' => 'main_content', 'form_id' => null, 'translation' => '主要內容'],
                    ['translation_type' => 'message', 'translation_code' => 'data_per_page', 'form_id' => null, 'translation' => '每頁資料量'],
                    ['translation_type' => 'message', 'translation_code' => 'user_is_disabled', 'form_id' => null, 'translation' => '此用戶已被停用'],
                    ['translation_type' => 'message', 'translation_code' => 'logout', 'form_id' => null, 'translation' => '登出'],
                    ['translation_type' => 'message', 'translation_code' => 'welcome', 'form_id' => null, 'translation' => '歡迎'],
                    ['translation_type' => 'message', 'translation_code' => 'yes', 'form_id' => null, 'translation' => '是'],
                    ['translation_type' => 'message', 'translation_code' => 'no', 'form_id' => null, 'translation' => '否'],
                    ['translation_type' => 'message', 'translation_code' => 'selecting', 'form_id' => null, 'translation' => '選擇'],
                    ['translation_type' => 'message', 'translation_code' => 'confirm', 'form_id' => null, 'translation' => '確認'],
                    ['translation_type' => 'message', 'translation_code' => 'cancel', 'form_id' => null, 'translation' => '取消'],
                    ['translation_type' => 'message', 'translation_code' => 'close', 'form_id' => null, 'translation' => '關閉'],
                    ['translation_type' => 'message', 'translation_code' => 'clear', 'form_id' => null, 'translation' => '清除'],
                    ['translation_type' => 'message', 'translation_code' => 'fill_one', 'form_id' => null, 'translation' => '請至少填寫一項。'],
                    ['translation_type' => 'message', 'translation_code' => 'loading', 'form_id' => null, 'translation' => '讀取中'],
                    ['translation_type' => 'message', 'translation_code' => 'processing', 'form_id' => null, 'translation' => '處理中'],
                    ['translation_type' => 'message', 'translation_code' => 'accessing', 'form_id' => null, 'translation' => '存取中'],
                    ['translation_type' => 'message', 'translation_code' => 'redirecting', 'form_id' => null, 'translation' => '跳轉中'],
                    ['translation_type' => 'message', 'translation_code' => 'reloading', 'form_id' => null, 'translation' => '重新讀取中'],
                    ['translation_type' => 'message', 'translation_code' => 'outputing', 'form_id' => null, 'translation' => '輸出中'],
                    ['translation_type' => 'message', 'translation_code' => 'translation', 'form_id' => null, 'translation' => '翻譯'],
                    ['translation_type' => 'message', 'translation_code' => 'readonly', 'form_id' => null, 'translation' => '唯獨'],
                    ['translation_type' => 'message', 'translation_code' => 'default', 'form_id' => null, 'translation' => '預設值'],
                    ['translation_type' => 'message', 'translation_code' => 'custom', 'form_id' => null, 'translation' => '自定義'],
                    ['translation_type' => 'message', 'translation_code' => 'type', 'form_id' => null, 'translation' => "型態"],
                    ['translation_type' => 'message', 'translation_code' => 'list', 'form_id' => null, 'translation' => "列表"],
                    ['translation_type' => 'message', 'translation_code' => 'level', 'form_id' => null, 'translation' => '層級'],
                    ['translation_type' => 'message', 'translation_code' => 'name', 'form_id' => null, 'translation' => '名稱'],
                    ['translation_type' => 'message', 'translation_code' => 'notification', 'form_id' => null, 'translation' => "通知"],
                    ['translation_type' => 'message', 'translation_code' => 'profile', 'form_id' => null, 'translation' => "個人檔案"],
                    ['translation_type' => 'message', 'translation_code' => 'menu', 'form_id' => null, 'translation' => "選單"],
                    ['translation_type' => 'message', 'translation_code' => 'unsave_confirm', 'form_id' => null, 'translation' => '如果取消變更將不保存，確定取消嗎？'],
                    ['translation_type' => 'message', 'translation_code' => 'row_with_number', 'form_id' => null, 'translation' => "第:row行"],
                    ['translation_type' => 'message', 'translation_code' => 'user_deleted', 'form_id' => null, 'translation' => "該用戶已被刪除"],
                    ['translation_type' => 'message', 'translation_code' => 'cannot_remove_saved', 'form_id' => null, 'translation' => "已儲存的 :item 不能刪除"],
                    ['translation_type' => 'message', 'translation_code' => 'contact_maintenance', 'form_id' => null, 'translation' => "請洽維護人員。"],
                    ['translation_type' => 'message', 'translation_code' => 'save_success', 'form_id' => null, 'translation' => "保存成功。"],
                    ['translation_type' => 'message', 'translation_code' => 'warning', 'form_id' => null, 'translation' => '注意'],
                    ['translation_type' => 'message', 'translation_code' => 'access_dined', 'form_id' => null, 'translation' => '存取遭拒'],
                    ['translation_type' => 'message', 'translation_code' => 'data_count_exceeded', 'form_id' => null, 'translation' => '資料數量已超過設定值。'],

                    ['translation_type' => 'message', 'translation_code' => 'error.unknown', 'form_id' => null, 'translation' => "發生不明錯誤，"],
                    ['translation_type' => 'message', 'translation_code' => 'error.check_permission', 'form_id' => null, 'translation' => '請確認您有相應的權限。'],

                    ['translation_type' => 'message', 'translation_code' => 'messages.fillOrSelectAll', 'form_id' => null, 'translation' => '請輸入/選擇所有欄位。'],
                    ['translation_type' => 'message', 'translation_code' => 'delete.confirm', 'form_id' => null, 'translation' => '確定刪除此筆資料?'],
                    ['translation_type' => 'message', 'translation_code' => 'delete.successful', 'form_id' => null, 'translation' => '刪除成功'],
                    ['translation_type' => 'message', 'translation_code' => 'delete.failed', 'form_id' => null, 'translation' => '刪除失敗'],

                    ['translation_type' => 'message', 'translation_code' => 'view', 'form_id' => null, 'translation' => '查看'],
                    ['translation_type' => 'message', 'translation_code' => 'add', 'form_id' => null, 'translation' => '新增'],
                    ['translation_type' => 'message', 'translation_code' => 'delete', 'form_id' => null, 'translation' => '刪除'],
                    ['translation_type' => 'message', 'translation_code' => 'edit', 'form_id' => null, 'translation' => '編輯'],
                    ['translation_type' => 'message', 'translation_code' => 'copy', 'form_id' => null, 'translation' => '複製'],
                    ['translation_type' => 'message', 'translation_code' => 'save', 'form_id' => null, 'translation' => '儲存'],
                    ['translation_type' => 'message', 'translation_code' => 'output', 'form_id' => null, 'translation' => '產出'],
                    ['translation_type' => 'message', 'translation_code' => 'download', 'form_id' => null, 'translation' => '下載'],
                    ['translation_type' => 'message', 'translation_code' => 'output_format', 'form_id' => null, 'translation' => '輸出格式'],
                    ['translation_type' => 'message', 'translation_code' => 'preview', 'form_id' => null, 'translation' => '預覽'],
                    ['translation_type' => 'message', 'translation_code' => 'query', 'form_id' => null, 'translation' => '查詢'],
                    ['translation_type' => 'message', 'translation_code' => 'verify', 'form_id' => null, 'translation' => '審核'],
                    ['translation_type' => 'message', 'translation_code' => 'report', 'form_id' => null, 'translation' => '下載報表'],

                    ['translation_type' => 'message', 'translation_code' => 'field', 'form_id' => null, 'translation' => '欄位'],
                    ['translation_type' => 'message', 'translation_code' => 'form', 'form_id' => null, 'translation' => '表單'],
                    ['translation_type' => 'message', 'translation_code' => 'content', 'form_id' => null, 'translation' => '內容'],

                    ['translation_type' => 'message', 'translation_code' => 'condition', 'form_id' => null, 'translation' => '條件式'],
                    ['translation_type' => 'message', 'translation_code' => 'filter', 'form_id' => null, 'translation' => '篩選'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.all_field', 'form_id' => null, 'translation' => '全部欄位'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.group', 'form_id' => null, 'translation' => '篩選群組'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.condition', 'form_id' => null, 'translation' => '或/和'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.condition.and', 'form_id' => null, 'translation' => '和'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.condition.or', 'form_id' => null, 'translation' => '或'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator', 'form_id' => null, 'translation' => '條件'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.=', 'form_id' => null, 'translation' => '等於'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.!=', 'form_id' => null, 'translation' => '不等於'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.>', 'form_id' => null, 'translation' => '大於'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.>=', 'form_id' => null, 'translation' => '大於等於'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.<', 'form_id' => null, 'translation' => '小於'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.<=', 'form_id' => null, 'translation' => '小於等於'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.like', 'form_id' => null, 'translation' => '包含'],
                    ['translation_type' => 'message', 'translation_code' => 'filter.operator.not like', 'form_id' => null, 'translation' => '不包含'],

                    ['translation_type' => 'message', 'translation_code' => 'translation_setting', 'form_id' => null, 'translation' => '翻譯設定'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.type', 'form_id' => null, 'translation' => '類型'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.code', 'form_id' => null, 'translation' => '代碼'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.types.message', 'form_id' => null, 'translation' => '訊息'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.types.rule', 'form_id' => null, 'translation' => '驗證訊息'],
                    ['translation_type' => 'message', 'translation_code' => 'translation.types.var', 'form_id' => null, 'translation' => '變數'],

                    ['translation_type' => 'message', 'translation_code' => 'reference.error.required_front_field', 'form_id' => null, 'translation' => '選擇前，請輸入 :field 欄位'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.no_view', 'form_id' => null, 'translation' => '找無資料引用之View表，'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.join_error', 'form_id' => null, 'translation' => '資料源表單設定錯誤，'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.field_error', 'form_id' => null, 'translation' => '資料源欄位設定錯誤，'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.where_error', 'form_id' => null, 'translation' => '資料源篩選設定錯誤，'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.front_error', 'form_id' => null, 'translation' => '前置欄位設定錯誤，'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.no_front', 'form_id' => null, 'translation' => '前置欄位沒有資料。'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.filter_error', 'form_id' => null, 'translation' => '篩選資料錯誤，'],
                    ['translation_type' => 'message', 'translation_code' => 'reference.error.reference_error', 'form_id' => null, 'translation' => '資料引用設定錯誤，'],

                    ['translation_type' => 'message', 'translation_code' => 'verifier', 'form_id' => null, 'translation' => '審核人'],
                    ['translation_type' => 'message', 'translation_code' => 'verify_at', 'form_id' => null, 'translation' => '審核時間'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.start', 'form_id' => null, 'translation' => '審核啟動'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.confirm', 'form_id' => null, 'translation' => '確認審核'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.return', 'form_id' => null, 'translation' => '退回審核'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.init', 'form_id' => null, 'translation' => '審核初始化'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.error.delete_null', 'form_id' => null, 'translation' => '此頁面尚未設定審核。'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.error.level', 'form_id' => null, 'translation' => '審核層級錯誤。'],
                    ['translation_type' => 'message', 'translation_code' => 'verify.error.had_verified', 'form_id' => null, 'translation' => '您已審核過此筆資料。'],

                    ['translation_type' => 'message', 'translation_code' => 'log', 'form_id' => null, 'translation' => '紀錄'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.parent', 'form_id' => null, 'translation' => '查詢上層資料'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.child', 'form_id' => null, 'translation' => '查詢子資料'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.relation', 'form_id' => null, 'translation' => '查詢此筆相關資料'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.old', 'form_id' => null, 'translation' => '查詢舊資料'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.new', 'form_id' => null, 'translation' => '查詢新資料'],
                    ['translation_type' => 'message', 'translation_code' => 'log.search.this', 'form_id' => null, 'translation' => '查詢此筆資料'],
                // Pages
                    ['translation_type' => 'page', 'translation_code' => 'SY', 'form_id' => null, 'translation' => '系統管理'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_USER_MANAGE', 'form_id' => null, 'translation' => '用戶管理'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_USERS', 'form_id' => null, 'translation' => '用戶設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_GROUPS', 'form_id' => null, 'translation' => '群組設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_GROUP_USER', 'form_id' => null, 'translation' => '群組設定表身'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_VERIFIES', 'form_id' => null, 'translation' => '審核設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATION_SETTING', 'form_id' => null, 'translation' => '通知設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATIONS', 'form_id' => null, 'translation' => '通知內容'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATION_USER', 'form_id' => null, 'translation' => '通知通訊資料'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_NOTIFICATION_TARGET', 'form_id' => null, 'translation' => '通知對象'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_PAGE_MANAGE', 'form_id' => null, 'translation' => '頁面管理'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_PAGES', 'form_id' => null, 'translation' => '頁面設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_MODULES', 'form_id' => null, 'translation' => '模組設定'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_LANGUAGE_MANAGE', 'form_id' => null, 'translation' => '語言管理'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_LANGUAGES', 'form_id' => null, 'translation' => '語種設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_TRANSLATION', 'form_id' => null, 'translation' => '翻譯設定'],

                    ['translation_type' => 'page', 'translation_code' => 'SY_LOG', 'form_id' => null, 'translation' => '系統操作紀錄'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_PARAMETERS', 'form_id' => null, 'translation' => '參數設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_PERMISSIONS', 'form_id' => null, 'translation' => '權限設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_FORMS', 'form_id' => null, 'translation' => '表單設定'],
                    ['translation_type' => 'page', 'translation_code' => 'SY_FIELDS', 'form_id' => null, 'translation' => '欄位設定'],

                    ['translation_type' => 'page', 'translation_code' => 'DT', 'form_id' => null, 'translation' => '開發人員工具'],
                    ['translation_type' => 'page', 'translation_code' => 'DT_SCHEDULES', 'form_id' => null, 'translation' => '排程管理'],
                    ['translation_type' => 'page', 'translation_code' => 'DT_MAGIC_TOOLS', 'form_id' => null, 'translation' => '神奇小工具'],
                    ['translation_type' => 'page', 'translation_code' => 'DT_INSERT_SQL', 'form_id' => null, 'translation' => 'INSERT指令生產器'],

                // Fields for User Page
                    ['translation_type' => 'field', 'translation_code' => 'name', 'form_id' => $Form_SY_USERS->form_id, 'translation' => '名稱'],
                    ['translation_type' => 'field', 'translation_code' => 'password_confirmation', 'form_id' => null, 'translation' => '確認密碼'],
                    ['translation_type' => 'field', 'translation_code' => 'user_disabled', 'form_id' => null, 'translation' => '停用'],
                    ['translation_type' => 'field', 'translation_code' => 'user_remarks', 'form_id' => null, 'translation' => '備註'],
                    ['translation_type' => 'message', 'translation_code' => 'user_setting', 'form_id' => null, 'translation' => '用戶設定'],
                    ['translation_type' => 'message', 'translation_code' => 'agent_setting', 'form_id' => null, 'translation' => '代理人設定'],

                // Fields for User Agent
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_target_type', 'form_id' => null, 'translation' => '代理對象類型'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_target_id', 'form_id' => null, 'translation' => '代理對象'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_target_name', 'form_id' => null, 'translation' => '代理對象名稱'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_enabled', 'form_id' => null, 'translation' => '是否啟用'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_enabled_at', 'form_id' => null, 'translation' => '啟用時間'],
                    ['translation_type' => 'field', 'translation_code' => 'user_agent_disabled_at', 'form_id' => null, 'translation' => '停用時間'],
                // Fields for Groups Page
                    ['translation_type' => 'field', 'translation_code' => 'group_new', 'form_id' => null, 'translation' => '新增群組'],
                    ['translation_type' => 'field', 'translation_code' => 'group_name', 'form_id' => null, 'translation' => '名稱'],

                // Fields for Parameters Page
                    ['translation_type' => 'field', 'translation_code' => 'parameter_new', 'form_id' => null, 'translation' => '新增參數'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_code', 'form_id' => null, 'translation' => '代碼'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_value', 'form_id' => null, 'translation' => '值'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_deletable', 'form_id' => null, 'translation' => '是否能被刪除'],
                    ['translation_type' => 'field', 'translation_code' => 'parameter_remarks', 'form_id' => null, 'translation' => '備註'],

                // Fields for Verifys Page
                    ['translation_type' => 'field', 'translation_code' => 'field_code', 'form_id' => $Form_SY_VERIFY_CONDITION->form_id, 'translation' => '欄位'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_comparison', 'form_id' => null, 'translation' => '比較運算子'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_value', 'form_id' => null, 'translation' => '值'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_target_id', 'form_id' => null, 'translation' => '目標對象'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_target_name', 'form_id' => null, 'translation' => '目標名稱'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_target_type', 'form_id' => null, 'translation' => '對象型態'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_population', 'form_id' => null, 'translation' => '人數'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_population_max', 'form_id' => null, 'translation' => '人數上限'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_logical', 'form_id' => null, 'translation' => '邏輯運算子'],
                    ['translation_type' => 'field', 'translation_code' => 'verify_condition_group', 'form_id' => null, 'translation' => '條件群組'],

                // Fields for Schedules Page
                    ['translation_type' => 'field', 'translation_code' => 'schedule_new', 'form_id' => null, 'translation' => '新增排程'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_name', 'form_id' => null, 'translation' => '名稱'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_fun', 'form_id' => null, 'translation' => '函數'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_remarks', 'form_id' => null, 'translation' => '備註'],
                    ['translation_type' => 'field', 'translation_code' => 'schedule_active', 'form_id' => null, 'translation' => '啟用'],

                // Fields for Notifications Page
                    ['translation_type' => 'field', 'translation_code' => 'notification_target_account', 'form_id' => null, 'translation' => '通知對象'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_target_name', 'form_id' => null, 'translation' => '姓名'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_target_type', 'form_id' => null, 'translation' => '通知對象型態'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_setting_trigger_type', 'form_id' => null, 'translation' => '動作型態'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_setting_content', 'form_id' => null, 'translation' => '內容'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_setting_mail', 'form_id' => null, 'translation' => '電子信箱'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_setting_phone', 'form_id' => null, 'translation' => '簡訊'],

                // Fields for Notification User Page
                    ['translation_type' => 'field', 'translation_code' => 'notification_user_phone', 'form_id' => null, 'translation' => '電話'],
                    ['translation_type' => 'field', 'translation_code' => 'notification_user_email', 'form_id' => null, 'translation' => '電子信箱'],

                // Fields for Languages Page
                    ['translation_type' => 'field', 'translation_code' => 'language_code', 'form_id' => null, 'translation' => '語言代碼'],
                    ['translation_type' => 'field', 'translation_code' => 'language_name', 'form_id' => null, 'translation' => '語言名稱'],

                    ['translation_type' => 'field', 'translation_code' => 'translation_type', 'form_id' => null, 'translation' => '語言名稱'],
                    ['translation_type' => 'field', 'translation_code' => 'translation_code', 'form_id' => null, 'translation' => '翻譯代碼'],

                // Fields for Pages Page
                    ['translation_type' => 'field', 'translation_code' => 'module', 'form_id' => null, 'translation' => '模組'],
                    ['translation_type' => 'field', 'translation_code' => 'submodule', 'form_id' => null, 'translation' => '子模組'],
                    ['translation_type' => 'field', 'translation_code' => 'page', 'form_id' => null, 'translation' => '頁面'],
                    ['translation_type' => 'field', 'translation_code' => 'page_code', 'form_id' => null, 'translation' => '代碼'],
                    ['translation_type' => 'field', 'translation_code' => 'page_name', 'form_id' => null, 'translation' => '名稱'],
                    ['translation_type' => 'field', 'translation_code' => 'page_module', 'form_id' => null, 'translation' => '模組'],
                    ['translation_type' => 'field', 'translation_code' => 'page_visible', 'form_id' => null, 'translation' => '是否能見'],
                    ['translation_type' => 'field', 'translation_code' => 'page_order', 'form_id' => null, 'translation' => '排序'],
                    ['translation_type' => 'field', 'translation_code' => 'page_remarks', 'form_id' => null, 'translation' => '備註'],
                    ['translation_type' => 'field', 'translation_code' => 'page_setting', 'form_id' => null, 'translation' => '頁面設定'],
                    ['translation_type' => 'field', 'translation_code' => 'module_setting', 'form_id' => null, 'translation' => '模組設定'],
                    ['translation_type' => 'field', 'translation_code' => 'field_setting', 'form_id' => null, 'translation' => '欄位設定'],
                    ['translation_type' => 'field', 'translation_code' => 'page_list_template', 'form_id' => null, 'translation' => '列表模板'],
                    ['translation_type' => 'field', 'translation_code' => 'page_form_template', 'form_id' => null, 'translation' => '表單模板'],
                    ['translation_type' => 'field', 'translation_code' => 'page_template', 'form_id' => null, 'translation' => '頁面模板'],
                    ['translation_type' => 'field', 'translation_code' => 'page_readonly', 'form_id' => null, 'translation' => '是否唯讀'],
                    ['translation_type' => 'field', 'translation_code' => 'page_has_body', 'form_id' => null, 'translation' => '是否有表身'],
                    ['translation_type' => 'field', 'translation_code' => 'page_body_number', 'form_id' => null, 'translation' => '表身個數'],
                    ['translation_type' => 'field', 'translation_code' => 'page_allow_empty_body', 'form_id' => null, 'translation' => '表身是否可空'],
                    ['translation_type' => 'field', 'translation_code' => 'page_max', 'form_id' => null, 'translation' => '資料上限數'],
                    ['translation_type' => 'field', 'translation_code' => 'page_head', 'form_id' => null, 'translation' => '表頭'],
                    ['translation_type' => 'field', 'translation_code' => 'page_body', 'form_id' => null, 'translation' => '表身'],
                    ['translation_type' => 'message', 'translation_code' => 'page_max_message', 'form_id' => null, 'translation' => '-1表示無上限'],
                    ['translation_type' => 'message', 'translation_code' => 'attached_to', 'form_id' => null, 'translation' => "附屬於"],
                    ['translation_type' => 'message', 'translation_code' => 'edit_order', 'form_id' => null, 'translation' => "編輯排序"],
                    ['translation_type' => 'message', 'translation_code' => 'field_type_error', 'form_id' => null, 'translation' => "欄位型態轉換錯誤。"],
                    ['translation_type' => 'field', 'translation_code' => 'savable', 'form_id' => null, 'translation' => "能否保存"],
                    ['translation_type' => 'field', 'translation_code' => 'query_mode', 'form_id' => null, 'translation' => "查詢模式"],
                    /* ['translation_type' => 'field', 'translation_code' => 'data_source', 'form_id' => null, 'translation' => "資料來源"],
                    ['translation_type' => 'field', 'translation_code' => 'independent_form', 'form_id' => null, 'translation' => "獨立表單"], */

                // Translations for Fields
                    ['translation_type' => 'field', 'translation_code' => 'field_code', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '代碼'],
                    ['translation_type' => 'field', 'translation_code' => 'field_type', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '型態'],
                    ['translation_type' => 'field', 'translation_code' => 'field_rule', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '欄位規則'],
                    ['translation_type' => 'field', 'translation_code' => 'field_order', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '欄位排序'],
                    ['translation_type' => 'field', 'translation_code' => 'field_default_value', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '預設值'],
                    ['translation_type' => 'field', 'translation_code' => 'field_required', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '是否必填'],
                    ['translation_type' => 'field', 'translation_code' => 'field_readonly', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '是否唯讀'],
                    ['translation_type' => 'field', 'translation_code' => 'field_show_on_form', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '顯示於表單'],
                    ['translation_type' => 'field', 'translation_code' => 'field_show_on_list', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '顯示於清單'],
                    ['translation_type' => 'field', 'translation_code' => 'field_options', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '欄位特殊設定'],
                    ['translation_type' => 'field', 'translation_code' => 'field_remarks', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '備註'],
                    ['translation_type' => 'field', 'translation_code' => 'field_details', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '欄位詳細'],
                    ['translation_type' => 'field', 'translation_code' => 'field_wide', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '格寬'],
                    ['translation_type' => 'field', 'translation_code' => 'wide_label', 'form_id' => $Form_SY_FIELDS->form_id, 'translation' => '欄位寬度'],
                    ['translation_type' => 'message', 'translation_code' => 'fill_default_first', 'form_id' => null, 'translation' => '請先填寫預設欄位'],
                    ['translation_type' => 'message', 'translation_code' => 'field_no_details', 'form_id' => null, 'translation' => "尚未設定欄位詳細。"],
                    ['translation_type' => 'message', 'translation_code' => 'field_type_first', 'form_id' => null, 'translation' => "請先選擇欄位型態。"],
                    ['translation_type' => 'field', 'translation_code' => 'editable', 'form_id' => null, 'translation' => '能否被修改'],
                    ['translation_type' => 'field', 'translation_code' => 'cloneable', 'form_id' => null, 'translation' => '能否被複製'],
                    ['translation_type' => 'field', 'translation_code' => 'decimal_options', 'form_id' => null, 'translation' => '小數設定'],
                    ['translation_type' => 'field', 'translation_code' => 'integer_digits', 'form_id' => null, 'translation' => '整數位數'],
                    ['translation_type' => 'field', 'translation_code' => 'decimal_digits', 'form_id' => null, 'translation' => '小數位數'],
                    ['translation_type' => 'field', 'translation_code' => 'number_digits', 'form_id' => null, 'translation' => '位數限制'],
                    ['translation_type' => 'message', 'translation_code' => 'min_bigger', 'form_id' => null, 'translation' => '最小值不能大於最大值！'],
                    ['translation_type' => 'field', 'translation_code' => 'options_options', 'form_id' => null, 'translation' => '選項設定'],
                    ['translation_type' => 'field', 'translation_code' => 'file_type', 'form_id' => null, 'translation' => '檔案類型'],
                    ['translation_type' => 'field', 'translation_code' => 'file_image', 'form_id' => null, 'translation' => '圖片檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_video', 'form_id' => null, 'translation' => '影片檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_audio', 'form_id' => null, 'translation' => '音訊檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_document', 'form_id' => null, 'translation' => '文件檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_spread_sheet', 'form_id' => null, 'translation' => '試算表檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_presentation', 'form_id' => null, 'translation' => '報表檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_pdf', 'form_id' => null, 'translation' => 'PDF檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_csv', 'form_id' => null, 'translation' => 'CSV檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_archive', 'form_id' => null, 'translation' => '壓縮檔'],
                    ['translation_type' => 'field', 'translation_code' => 'file_text', 'form_id' => null, 'translation' => '文字檔'],
                    ['translation_type' => 'field', 'translation_code' => 'common_setting', 'form_id' => null, 'translation' => '通用設定'],
                    ['translation_type' => 'field', 'translation_code' => 'field_list', 'form_id' => null, 'translation' => '欄位列表'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_source_field', 'form_id' => null, 'translation' => '資料源欄位'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_source_table', 'form_id' => null, 'translation' => '資料源表單'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_where', 'form_id' => null, 'translation' => '資料源篩選'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_other', 'form_id' => null, 'translation' => '其他設定'],
                    ['translation_type' => 'field', 'translation_code' => 'join_left', 'form_id' => null, 'translation' => '關聯欄位'],
                    ['translation_type' => 'field', 'translation_code' => 'join_right', 'form_id' => null, 'translation' => '關聯對象'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_front', 'form_id' => null, 'translation' => '前置欄位'],
                    ['translation_type' => 'field', 'translation_code' => 'native_sql', 'form_id' => null, 'translation' => '原生SQL'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_select', 'form_id' => null, 'translation' => '動態下拉式選單'],
                    ['translation_type' => 'field', 'translation_code' => 'reference_multiple', 'form_id' => null, 'translation' => '複選模式'],

                // Fields for Permissions Page
                // Fields for PermissionColumn Page
                    ['translation_type' => 'field', 'translation_code' => 'permission_column_attribute', 'form_id' => null, 'translation' => '內容屬性'],
                    ['translation_type' => 'field', 'translation_code' => 'permission_column_logic', 'form_id' => null, 'translation' => '邏輯'],
                    ['translation_type' => 'field', 'translation_code' => 'permission_column_content', 'form_id' => null, 'translation' => '內容'],
                    ['translation_type' => 'field', 'translation_code' => 'permission_column_relative', 'form_id' => null, 'translation' => '關聯'],
                    ['translation_type' => 'field', 'translation_code' => 'permission_column_remarks', 'form_id' => null, 'translation' => '備註'],
                    ['translation_type' => 'field', 'translation_code' => 'permission_column_position', 'form_id' => null, 'translation' => '欄位位置'],

                // Fields for Log Page
                    ['translation_type' => 'field', 'translation_code' => 'log_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '紀錄 ID'],
                    ['translation_type' => 'field', 'translation_code' => 'page_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '頁面'],
                    ['translation_type' => 'field', 'translation_code' => 'form_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '表單'],
                    ['translation_type' => 'field', 'translation_code' => 'id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '資料 ID'],
                    ['translation_type' => 'field', 'translation_code' => 'parent_id', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '上層資料 ID'],
                    ['translation_type' => 'field', 'translation_code' => 'action', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '動作'],
                    ['translation_type' => 'field', 'translation_code' => 'data', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '資料'],
                    ['translation_type' => 'field', 'translation_code' => 'created_at', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '操作時間'],
                    ['translation_type' => 'field', 'translation_code' => 'created_by', 'form_id' => $Form_SY_LOG->form_id, 'translation' => '操作者'],
        ]);

        Parameter::create(['parameter_code' => 'default_language', 'parameter_value' => 1, 'parameter_deletable' => false, 'parameter_remarks' => '預設語言']);

        $userAgentTargetQuery =
        DB::table('users')
        ->select('user_id as user_agent_target_id')
        ->addSelect('name as user_agent_target_name')
        ->addSelect(DB::raw("'user' as user_agent_target_type"))
        ->where('user_disabled','<>',1)
        ->where('user_id','<>',0)
        ->union(
            DB::table('groups')->select('group_id','group_name',DB::raw("'group' as type"))
        );
        View::createView(
            "SY_USER_AGENT_PAGE_{$Form_SY_USER_AGENT_PAGE->form_id}_user_agent_target_id",
            $userAgentTargetQuery
        );

        $verifyTargetQuery =
        DB::table('users AS a')
        ->leftjoin('permissions AS b',function($q){
            $q->on('a.user_id','=','b.permission_target_id')
            ->on('b.permission_type','=',DB::raw("'user'"));
        })
        ->select('a.user_id as verify_target_id')
        ->addSelect('a.name as verify_target_name')
        ->addSelect(DB::raw("'user' as verify_target_type"))
        ->addSelect('b.page_id')
        ->addSelect(DB::raw("1 as verify_population_max"))
        ->where('user_disabled','<>',1)
        ->where('a.user_id', '<>', 0)
        ->where('b.permission_read', '=', 1)
        ->union(
            DB::table('groups AS a')
            ->leftjoin('permissions AS b', function($q){
                $q->on('a.group_id','=','b.permission_target_id')
                ->on('b.permission_type','=',DB::raw("'group'"));
            })
            ->select('a.group_id')
            ->addSelect('a.group_name')
            ->addSelect(DB::raw("'group' as verify_target_type"))
            ->addSelect('b.page_id')
            ->selectSub(
                DB::table('group_user AS c')
                ->whereColumn('c.group_id', 'a.group_id')
                ->selectRaw("COUNT(*)"),
                "verify_population_max"
            )
            ->where('b.permission_read', '=', 1)
        );
        View::createView(
            "SY_VERIFY_LEVEL_{$Form_SY_VERIFY_LEVEL->form_id}_verify_target_id",
            $verifyTargetQuery
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 痾 我可以直接把資料表炸掉是ㄅ
    }
}
