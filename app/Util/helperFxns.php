<?php

use Illuminate\Support\Facades\DB;


function checkExists($table,$whereconditions)
{
    $exists = null; // should return true or false
    if(!empty($table) && is_array($whereconditions) && count($whereconditions) > 0)
    {
        try
        {
            $exists = DB::table($table)->where(function($query) use($whereconditions){
                foreach($whereconditions as $key => $value){
                    $query->whereIn($key,[$value]);
                }
            })
                ->exists();
        }
        catch(Exception $e)
        {
            // save error
            $whereJson = json_encode($whereconditions);
            $error = "[$table,$whereJson] : ".$e->getMessage();
            $insertVals = ['error_key' => 'checkExists()','error' => $error];
            return null;
        }
    }
    return $exists;
}
function unique_code($limit)
{
    return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
}
