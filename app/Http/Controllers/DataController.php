<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function sendResponse($data, $message, $status = 200)
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $data
        ];
        return response()->json($response, $status);
    }
    public function sendError($errorData, $message, $status = 200)
    {
        $response = [];
        $response['status'] = false;
        $response['message'] = $message;
        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }
        return response()->json($response, $status);
    }
    public function allItems()
    {
        try
        {
            $returnFields = ['items.id','items.item_name as title','items.desc as description','items.created_at','users.name as created_by'];
            $items = DB::table('items')->join('users','items.user_id','=','users.id')->select($returnFields)->orderby('items.created_at','desc')->get();
            $data = [
                "items" => $items
            ];
            return $this->sendResponse($items, 'success');
        }
        catch(\Exception $e){
            return $this->sendError([], $e->getMessage());
        }
    }
}
