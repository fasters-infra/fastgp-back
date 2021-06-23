<?php

namespace App\Services;

use App\Models\User;
use DB;

/**
 * DBServices
 *
 * classe para serviços comuns relacionados à base de dados
 */
class DBServices
{
    /**
     * getEnumValues
     *
     * retorna os valores possíveis para um enum
     *
     * @param  string $table
     * @param  string $column
     * @return string
     */
    public function getEnumValues ($table, $column) {
        $return = '';
        $values = DB::select(DB::raw("SHOW COLUMNS FROM `$table` LIKE '$column'"));
        if (!empty($values)) {
            if (isset($values[0]->Type)) {
                $values = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $values[0]->Type));
                $return = implode(',', $values);
            }
        }
        return $return;
    }

    public function getUserS3BucketCode () {
        $id = '';
        while (!$id) {
            $id = md5(uniqid(rand(), true));
            $exists = User::where('s3_bucket', $id)->get();
            if ($exists->isEmpty()) {
                return $id;
            }
            $id = '';
        }
    }
}
