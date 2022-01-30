<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UsersController extends Controller
{
    public function createUser (Request $req){
        $response = ['status'=>1, "msg"=>""];

        $data = $req->getContent();

        $validator = Validator::make(json_decode($req->getContent(),true),[
            'name' => 'required|max:255|unique:users',
            'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users|max:255',
            'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            'rol' => 'required|in:particular,profesional,admin',

        ]); 
        if ($validator->fails()){
           $response['msg'] = 'There was an error' . $validator->errors()->first();
           $response['status'] = 0;
        }else{

            $data = json_decode($data);

            try{

                $user = new users();

                $user->name = $data->name;
                $user->email = $data->email;
                $user->password = Hash::make($data->password);
                $user->rol = $data->rol;

                $user->save();
                $response['msg'] = "User saved";
            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
            }

         
        }
           return response()->json($response);
    }


    public function login (Request $req){
        $response = ['status'=>1, "msg"=>""];

        $users = "";

        if ($req->has('name') && ($req->input('name') != "")){
            $users = users::where('name', $req->input('name'))->first();
        }else{
            $response['msg']= 'Enter a nickname';
            $response['status'] = 0;
        }
        if($users){
            if(Hash::check($req->input('password'), $users->password)){
                try{
                    $token = Hash::make(now(). $users->id);
                    $users->api_token = $token;
                    $users->save();
                    $response['msg'] = "Session token: " . $users->api_token;

                }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
                }
            }else{
                $response['msg'] = 'Invalid password';
                $response['status'] = 0;
            }
        }else{
            $response['msg'] .= ', There are no users with this nickname';
            $response['status'] = 0;
        }

        return response()->json($response);

    }


    public function newPassword(Request $req){
        $response = ['status'=>1, "msg"=>""];

        $email = $req->input('email');
        $user = users::where('email', $req->input('email'))->first();

        if($user){

            try{

                $newPassword = $this->randomPassword(10);
                $newPassword = $newPassword . "0!";
                $user -> password = Hash::make($newPassword);

                $response['msg'] = "La nueva contraseña es:" . $newPassword;

            }catch(\Exception $e){
                $response['msg'] = $e->getMessage();
                $response['status'] = 0;
                }
        }else{
            $response['msg'] = "Usuario no encontrado";
            $response['status'] = 0;
        }


        return response() -> json($response);    
    }

    public function randomPassword($length){
        $variables = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($variables),0,$length);
    }


    public function searchSellings(Request $req){
        $response = ['status'=>1, "msg"=>""];
        $data = $req->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'card_name' => 'required|string',
        ]);

        if($validator->fails()){
            $response['msg'] = 'There was an error ' . $validator->errors()->first();
            $response['status'] = 0;
        }else{

            $data = json_decode($data);
            try{
                $cards = DB::table('sellings')->select(['sellings.id', 'cards.name', 'sellings.units', 'sellings.price', 'users.name'])
                                ->where('cards.name', 'like', '%'.$data->card_name.'%')
                                ->join('users', 'sellings.user_id', '=', 'users.id')
                                ->join('cards', 'sellings.card_id', '=', 'cards.id')
                                ->orderBy('sellings.price', 'asc')
                                ->get();
                if(count($cards) > 0){
                    $response['msg'] = "Card offer found";
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


}
