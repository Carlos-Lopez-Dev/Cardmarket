<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\collection;
use App\Models\cards;
use App\Models\cards_collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function createCollection (Request $req){
        $response = ['status'=>1, "msg"=>""];

        $data = $req->getContent();

        $validator = Validator::make(json_decode($req->getContent(),true),[
            'name' => 'required|unique:collections|max:255',
            'symbol' => 'required|max:255',
            'editDate' => 'required|max:255',
            'cardID' => 'required|max:255',
        ]); 
        if ($validator->fails()){

            $response['msg'] = 'There was an error' . $validator->errors()->first();
            $response['status'] = 0;  
        }else{

            $data = json_decode($data);

            try{


                $card = cards::where('id', $data->cardID)->first();
                if(!$card){

                    $collection = new collection();
                    $collection->name = $data->name;
                    $collection->symbol = $data->symbol;
                    $collection->editDate = $data->editDate;
                    $collection->save();

                    $card = new cards();
                    $card->name = "Default name";
                    $card->description = "Default description";
                    $card->save();

                    $relation = new cards_collection();
                    $relation->card_id = $card->id;
                    $relation->collection_id = $collection->id;
                    $relation->save();

                    $response['msg'] = 'Collection created with a default card';

                }else{


                    $collection = new collection();
                    $collection->name = $data->name;
                    $collection->symbol = $data->symbol;
                    $collection->editDate = $data->editDate;
                    $collection->save();

                    $relation = new cards_collection();
                    $relation->card_id = $data->cardID;
                    $relation->collection_id = $collection->id;
                    $relation->save();

                    $response['msg'] = 'Collection created';
                }



            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }

         
        }
           return response()->json($response);
    }
}
