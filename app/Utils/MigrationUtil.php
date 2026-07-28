<?php

namespace App\Utils;

use Illuminate\Support\Facades\Artisan;

class MigrationUtil
{

    static public function getTemplates()
    {

        return [
            "add" => [
                "template" => "
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {className} extends Migration
{
    public function up()
    {
        Schema::create('{table}', function (Blueprint \$table) {
        \$table->bigIncrements('id');
        \$table->bigInteger('parent_id')->default(-1);

        {data}

        \$table->integer('created_by')->default(-1);
        \$table->integer('updated_by')->default(-1);
        \$table->timestamps();
        \$table->index(['id','parent_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('{table}');
    }
}",
                'fields' => [
                    'string' => "\$table->string('{code}'){required};",
                    'textarea' => "\$table->string('{code}'){required};",
                    'integer' => "\$table->integer('{code}'){required};",
                    'decimal' => "\$table->string('{code}',{total},{float}){required};",
                    'boolean' => "\$table->boolean('{code}'){required};",
                    'select' => "\$table->string('{code}'){required};",
                    'checkboxes' => "\$table->string('{code}'){required};",
                    'radio' => "\$table->string('{code}'){required};",
                    'date' => "\$table->date('{code}'){required};",
                    'time' => "\$table->time('{code}'){required};",
                    'datetime' => "\$table->datetime('{code}'){required};",
                    'file' => "\$table->string('{code}'){required};",
                    'reference' => "\$table->string('{code}'){required};",
                    'button' => ""
                ]
            ]
        ];
    }

    static public function getMigrationClass($formData, $pageData,$prefix){
        return $prefix . str_replace('_','',$pageData->page_code) . str_replace('_','',$formData->form_id) ;
    }


    static public function createFormTable($formData, $pageData)
    {
        $template = MigrationUtil::getTemplates();

        $table = $pageData->page_code . "_" . $formData->form_id;

        $migrationName = MigrationUtil::getMigrationClass($formData, $pageData,"AutoCreate");
        Artisan::call('make:migration', ['name' => $migrationName]);
        $migrationFile = scandir('database/migrations', SCANDIR_SORT_DESCENDING)[0];
        $output = $template["add"]["template"];
        $output = str_replace("{table}", $table, $output);
        $output = str_replace("{className}", $migrationName, $output);

        $data = "";
        foreach ($formData->fields as $field) {
            $tmp = $template["add"]["fields"][$field->field_type];

            $tmp = str_replace("{code}", $field->field_code, $tmp);
            $tmp = str_replace("{required}", $field->field_required ? '' : '->nullable()->default(null)', $tmp);

            if ($field->field_type == "decimal") {
                $tmp = str_replace("{total}", $field->field_options["decimal"][0], $tmp);
                $tmp = str_replace("{float}", $field->field_options["decimal"][1], $tmp);
            }
            $data = $data . $tmp . PHP_EOL . '        ';
        }
        $output = str_replace("{data}", $data, $output);
        file_put_contents('database/migrations/' . $migrationFile, $output);

        Artisan::call('migrate');
    }
}
