<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;

class QueryBuilderMacroProvider extends ServiceProvider
{
    protected static $methods = ["insertWithTimeAndUser", "insertWithTimeAndUserThenReturnID", "updateWithTimeAndUser"];

    protected static function timestampAndUserValues($funcName, array $columns)
    {
        Builder::macro($funcName, function (array $values) use ($columns) {

            $user_id = Request::user()->user_id ? Request::user()->user_id : -1;
            $now = \Carbon\Carbon::now();

            if (array_key_exists(0, $values) && is_array($values[0])) {
                foreach ($values as &$value) {
                    $value[$columns[1]] = $now;
                    $value[$columns[2]] = $user_id;
                }
            } else {
                $values[$columns[1]] = $now;
                $values[$columns[2]] = $user_id;
            }

            return Builder::{$columns[0]}($values);
        });
    }

    /**
     * Insert with timestamp and user
     */
    protected static function insertWithTimeAndUser()
    {
        return self::timestampAndUserValues(__FUNCTION__, ["insert", "created_at", "created_by"]);
    }

    /**
     * Insert with timestamp and user then return id
     */
    protected static function insertWithTimeAndUserThenReturnID()
    {
        return self::timestampAndUserValues(__FUNCTION__, ["insertGetId", "created_at", "created_by"]);
    }

    /**
     * Update with timestamp and user
     */
    protected static function updateWithTimeAndUser()
    {
        return self::timestampAndUserValues(__FUNCTION__, ["update", "updated_at", "updated_by"]);
    }

    /**
     * Register services.
     * @return void
     */
    public function register()
    {
        foreach (self::$methods as $method) {
            self::{$method}();
        }
    }
}
