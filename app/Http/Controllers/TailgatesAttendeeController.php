<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Tailgate;
use App\TailgatesAttendee;
use App\User;

class TailgatesAttendeeController extends Controller
{
	/*
    NAME: remove_tailgate_attendee
    DESCRIPTION: This will remove user_id and tailgate_id record from the table which means user is no longer attending the tailgate
    PARAMETERS: 
        user_id              - existing user id
        tailgatee_id      - existing tailgate id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, and a message if needed.
    Ex: {"success":true}
    */
    public function remove_tailgate_attendee(Request $request){
        $user_id = $request->input("user_id");
        if(!isset($user_id) || $user_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The user_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_user = User::where('id',$user_id)->first();
        if(is_null($cur_user)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The user_id ({$user_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $tailgate_id = $request->input("tailgate_id");
        if(!isset($tailgate_id) || $tailgate_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The tailgate_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_tailgate = Tailgate::where('id',$tailgate_id)->first();
        if(is_null($cur_tailgate)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The tailgate_id ({$tailgate_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $cur = TailgatesAttendee::where(['tailgate_id'=>$tailgate_id,'user_id'=>$user_id])->first();
        if(is_null($cur)){
            $ret = array(
              "success"=>false,
              "msg"=>"This user ({$user_id}) not attending this tailgate ({$tailgate_id}). Nothing was changed."
            );
            die(json_encode($ret));
        }
        $deleted = $cur->delete();
        if($deleted){
            $ret = array(
              "success"=>true
            );
        }
        else{
            $ret = array(
              "success"=>false,
              "msg"=>"There was a problem deleting the record."
            );
        }
        die(json_encode($ret));
    }
    /*
    NAME: add_tailgate_attendee
    DESCRIPTION: This will add user_id to the tailgate_id, which then means that the user is attending that tailgate.
    PARAMETERS: 
        user_id      	- existing user id
        tailgate_id     - existing tailgate id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, and a message if needed.
    Ex: {"success":true}
    */
    public function add_tailgate_attendee(Request $request){
        $user_id = $request->input("user_id");
        if(!isset($user_id) || $user_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The user_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_user = User::where('id',$user_id)->first();
        if(is_null($cur_user)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The user_id ({$user_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $tailgate_id = $request->input("tailgate_id");
        if(!isset($tailgate_id) || $tailgate_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The tailgate_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_tailgate = Tailgate::where('id',$tailgate_id)->first();
        if(is_null($cur_tailgate)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The tailgate_id ({$tailgate_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $cur = TailgatesAttendee::where(['tailgate_id'=>$tailgate_id,'user_id'=>$user_id])->first();
        if(!is_null($cur)){
        	$ret = array(
              "success"=>false,
              "msg"=>"This user ({$user_id}) is already attending this tailgate ({$tailgate_id}). Nothing was changed."
            );
            die(json_encode($ret));
        }
        //we got valid user_id and tailgate_id so create new row in TailgatesAttendee
        $cur = new TailgatesAttendee;
        $cur->user_id = $user_id;
        $cur->tailgate_id = $tailgate_id;
        $saved = $cur->save();
        if($saved){
        	$ret = array(
              "success"=>true
            );
        }
        else{
        	$ret = array(
              "success"=>false,
              "msg"=>"Record was not saved for some reason."
            );
        }
        die(json_encode($ret));
    }
}
