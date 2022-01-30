<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\selling;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SellingController extends Controller
{
     public function createSelling (Request $req){
        $response = ['status'=>1, "msg"=>""];

        $data = $req->getContent();

        $validator = Validator::make(json_decode($req->getContent(),true),[
            'card' => 'required|max:255',
            'units' => 'required|max:255',
            'price' => 'required|max:255',
        ]); 
        if ($validator->fails()){
           $response['msg'] = 'There was an error' . $validator->errors()->first();
           $response['status'] = 0;
        }else{

            $data = json_decode($datos);

            try{

                $selling= new selling();

                $selling->card = $data->card;
                $selling->units = $data->units;
                $selling->price = $data->price;

                $user->save();
                $response['msg'] = "Collection saved";
            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }

         
        }
           return response()->json($response);
    }
}
