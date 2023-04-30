<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class ItemsController extends Controller
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
    // make directory if not exists
    public function dirExists($filepath)
    {
        $dir_exists = false;
        if($filepath)
        {
            if(!File::isDirectory($filepath)){
                if(File::makeDirectory($filepath,0777,true,true)) $dir_exists = true;
            }
            else $dir_exists = true;
        }
        return $dir_exists;
    }

    public function index()
    {
        try
        {
            $userId = auth()->user()->id;
            $totalItems = Items::all()->count();
            $newItems = Items::whereDate('created_at', Carbon::today())->count();
            $myItems = Items::whereIN('user_id',[$userId ])->count();
            $totalUsers = User::all()->count();
            $newUsers = User::whereDate('created_at', Carbon::today())->count();
            $returnFields = ['items.id','items.user_id','items.item_name as title','items.desc as description','items.filename','items.created_at',
            'users.name as created_by'];
            $items = DB::table('items')->join('users','items.user_id','=','users.id')->select($returnFields)->orderby('items.created_at','desc')->get();
            $data = [
                "totalItems" => $totalItems,
                "newItems" => $newItems,
                "myItems" => $myItems,
                "totalUsers" => $totalUsers,
                "newUsers" => $newUsers,
                "totalVisits" => 0,
                "newVisits" => 0,
                "items" => $items
            ];
            return $this->sendResponse($data, 'success');
        }
        catch(\Exception $e){
            return $this->sendError([], $e->getMessage());
        }
    }
    public function store(Request $request)
    {
        $input = $request->only('title','description','image');
        $validator = Validator::make($input,[
            'title' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif|max:3072' //mimes:jpeg,jpg,png 3MB = 1024 kbs
        ],[
            'image.mimes' => 'upload a png, jpg, jpeg or gif image of max 3MB'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error');
        }

        $mess = 'user not found';
        $userId = auth()->user()->id;
        if($userId)
        {
            $mess = 'You have a similar item';
            $name = $request->title;
            $conditions = ["user_id" => $userId,"item_name" => $name];
            if(!checkExists('items',$conditions))
            {
                $mess = 'File operation error';
                $path = public_path('uploads');
                if($this->dirExists($path))
                {
                    $file_key = trim(unique_code(6)).'_'.date('Y-m-d-His');
                    if($request->hasFile('image'))
                    {
                        $ext = $request->image->getClientOriginalExtension();
                        $filename = $file_key.'.'.$request->image->getClientOriginalExtension();
                        $status = $request->image->move($path,$filename);
                        if($status)
                        {
                            $mess = 'success';
                            $item = Items::create([
                                'user_id' => $userId,
                                'item_name' => $name,
                                'desc' => $request->description,
                                'filename' => $filename
                            ]);
                            return $this->sendResponse($item, 'Item created successfully');
                        }
                    }
                }
            }
        }

        return $this->sendError([], $mess);
    }
    public function show($id)
    {
        $mess = 'Item could not be found';
        $item = Items::find($id);
        if($item){
            $mess = 'success';
        }
        return $this->sendResponse($item,$mess);
    }
    public function update(Request $request, $id)
    {
        $input = $request->only('title','description','image');
        $validator = Validator::make($input,[
            'title' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif|max:3072' //3MB 1mb = 1024 kbs
        ],[
            'image.mimes' => 'upload a png, jpg, jpeg or gif image of max 3MB'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error');
        }
        $item = Items::find($id);
        $mess = 'Item could not be found!';
        if($item)
        {
            if($item->user_id != auth()->user()->id){
                return $this->sendError(null, 'action denied!');
            }

            $old_filename = $item->filename;
            $mess = 'File operation error';
            $path = public_path('uploads');
            if($this->dirExists($path))
            {
                $file_key = trim(unique_code(6)).'_'.date('Y-m-d-His');
                if($request->hasFile('image'))
                {
                    $ext = $request->image->getClientOriginalExtension();
                    $filename = $file_key.'.'.$request->image->getClientOriginalExtension();
                    $status = $request->image->move($path,$filename);
                    if($status) {
                        $mess = 'success';
                        $item->item_name = $request->title;
                        $item->desc = $request->description;
                        $item->filename = $filename;
                        $item->save();
                        if($old_filename){
                            $old_file = public_path('uploads/'.$old_filename);
                            if(file_exists($old_file)){
                                // delete import file
                                unlink($old_file);
                            }
                        }
                        return $this->sendResponse($item, 'Item updated successfully');
                    }
                }
            }
        }
        return $this->sendError($mess, 'Item could not be found!');
    }
    public function destroy($id)
    {
        $item = Items::find($id);
        if($item)
        {
            if($item->user_id != auth()->user()->id){
                return $this->sendError(null, 'action denied!');
            }
            $item->delete();
            return $this->sendResponse($item, 'Item deleted successfully');
        }
        return $this->sendError(null, 'Item could not be found!');
    }
}
