<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\cards;
use App\Models\selling;
use App\Models\collection;
use App\Models\cards_collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CardsController extends Controller
{
    public function createCard (Request $req){
        $response = ['status'=>1, "msg"=>""];

        $data = $req->getContent();

        $validator = Validator::make(json_decode($req->getContent(),true),[
            'name' => 'required|max:255',
            'description' => 'required|max:255',
            'collection' => 'required|exists:collections,id',
        ]); 
        if ($validator->fails()){
           $response['msg'] = 'There was an error ' . $validator->errors()->first();
           $response['status'] = 0;
        }else{

            $data = json_decode($data);
            
            try{

                $card = new cards();
                $card->name = $data->name;
                $card->description = $data->description;
                $card->save();

                $relation = new cards_collection();
                $relation->card_id = $card->id;
                $relation->collection_id = $data->collection;
                $relation->save();


                $response['msg'] = "Card saved";
            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }
        }
           return response()->json($response);
    }


    public function addToCollection(Request $req){
        $response = ['status'=>1, "msg"=>""];
        $data = $req->getContent();

        $validator = Validator::make(json_decode($req->getContent(),true),[
            'card_id' => 'required|integer',
            'collection_id' => 'required|integer',
        ]);
        if ($validator->fails()){
            $response['msg'] = 'There was an error ' . $validator->errors()->first();
            $response['status'] = 0;
        }else{

            $data = json_decode($data);
            try{
                $card = cards::where('id', $data->card_id)->first();
                if($card){

                    $collection = collections::where('id', $data->collection_id)->first();
                    if($collection){

                        $relation = new cards_collection();
                        $relation->card_id = $data->card_id;
                        $relation->collection_id = $data->collection_id;
                        $relation->save();

                        $response['msg'] = "The card has been added to the collection";
                        
                    }else{
                        $response['msg'] = "There is no existing collection with this id";
                        $response['status'] = 0;  
                    }

                }else{
                 $response['msg'] = "There is no existing card with this id";
                 $response['status'] = 0;   
                }


               


                
            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }
        }
    }

    public function search(Request $req){
        $response = ['status'=>1, "msg"=>""];
        $data = $req->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'card_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            $response['msg'] = 'There was an error ' . $validator->errors()->first();
            $response['status'] = 0;
        } else {

            $data = json_decode($data);
            try{
                $cards = cards::where('name', 'like', '%'.$data->card_name.'%')->get();
                if(cards::where('name', 'like', '%'.$data->card_name.'%')->first()){
                    $response['msg'] = "Card found";
                    $response['data'] = $cards;
                }else{
                    $response['msg'] = "There is no card with that name";
                    $response['status'] = 0;
                }
            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }
        }
        return response()->json($response);
    }


    public function sell(Request $req){
        $response = ['status'=>1, "msg"=>""];
        $data = $req->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'card_id' => 'required',
            'units' => 'required|integer',
            'price' => 'required',
        ]);

        if ($validator->fails()){
            $response['msg'] = 'There was an error ' . $validator->errors()->first();
            $response['status'] = 0;
        }else{

            $data = json_decode($data);
            try{
                $card = cards::where('id', $data->card_id)->first();
                if($card){
                    $selling = new selling();
                    $selling->user_id = $req->user->id;
                    $selling->card_id = $data->card_id;
                    $selling->units = $data->units;
                    $selling->price = $data->price;
                    $selling->save();

                    $response['msg'] = "The offer has been created";
                }else{
                    $response['msg'] = "There is no card with that id";
                    $response['status'] = 0;
                }
            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }
        }
        return response()->json($response);
    }

    

}
