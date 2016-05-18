<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Ticket;
use App\Game;
use App\User;

class TicketController extends Controller
{
    /*
    NAME: update
    DESCRIPTION: This will update an existing ticket or add a new one if one does not already exist.
    PARAMETERS: 
        user_id      - existing user id
        game_id      - existing game id
        section 	 - section string (section number)
        row 		 - row string (row number)
        seat 	 	 - seat string (seat number)
    RETURNS: (str) ret - JSON object with status of whether success was true or false, the ticket object, and a message if needed.
    Ex: {"success":true,"msg":"Record created or updated successfully. section field was saved successfully. row field DID NOT have a value. seat field DID NOT have a value.","ticket":{"game_id":"1","user_id":"63","section":"bah","updated_at":"2016-05-10 20:44:59","created_at":"2016-05-10 20:44:59","id":2}}
    */
    public function update(Request $request){
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
        $game_id = $request->input("game_id");
        if(!isset($game_id) || $game_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The game_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_game = Game::where('id',$game_id)->first();
        if(is_null($cur_game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $cur = Ticket::where(['game_id'=>$game_id,'user_id'=>$user_id])->first();
        if(is_null($cur)){
            $cur = new Ticket;
            $cur->game_id = $game_id;
            $cur->user_id = $user_id;
        }
        $msg = array();
        $valid_fields = ['section','row','seat'];
        $all_empty = true;
        foreach($valid_fields as $field){
            $field_value = $request->input($field);
            if(isset($field_value) && $field_value !== ''){
            	$all_empty = false;
                $cur->$field = $field_value;
                //add to message the field that was successfully saved.
                $msg[] = $field .' field was saved successfully.';
            }
            else{
                //add to message that field listed did not have a value passed.
                $msg[] = $field .' field DID NOT have a value.';
            }
        }
        if($all_empty){
    		$ret = array(
              "success"=>false,
              "msg"=>'Section, row, and seat fields where all empty so nothing was created or updated.'
            );
            die(json_encode($ret));
        }
        $saved = $cur->save();
        if($saved){
            $ret = array(
              "success"=>true,
              "msg"=>'Record created or updated successfully. ' . implode(' ',$msg),
              'ticket'=>$cur
            );
        }
        else{
            $ret = array(
              "success"=>false,
              "msg"=>'Record was not created or saved. ' . implode(' ',$msg)
            );
        }
        die(json_encode($ret));
    }

    /*
    NAME: get
    DESCRIPTION: Gets and returns success status and ticket object if found.
    PARAMETERS: 
        user_id      - existing user id
        game_id      - existing game id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, the ticket object, and a message if needed.
    Ex: {"success":false,"ticket":{"id":2,"game_id":"1","user_id":"63","section":"bah","row":"","seat":"","created_at":"2016-05-10 20:44:59","updated_at":"2016-05-10 20:44:59"}}
    */
    public function get(Request $request){
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
        $game_id = $request->input("game_id");
        if(!isset($game_id) || $game_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The game_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_game = Game::where('id',$game_id)->first();
        if(is_null($cur_game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $cur = Ticket::where(['game_id'=>$game_id,'user_id'=>$user_id])->first();
        $ret = array(
          "success"=>true,
          "ticket"=>$cur
        );
        die(json_encode($ret));
    }
    /*
    NAME: delete
    DESCRIPTION: Deletes the ticket if found.
    PARAMETERS: 
        user_id      - existing user id
        game_id      - existing game id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, and a message if needed.
    Ex: {"success":true}
    */
    public function delete(Request $request){
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
        $game_id = $request->input("game_id");
        if(!isset($game_id) || $game_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The game_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_game = Game::where('id',$game_id)->first();
        if(is_null($cur_game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $cur = Ticket::where(['game_id'=>$game_id,'user_id'=>$user_id])->first();
        if(is_null($cur)){
        	$ret = array(
              "success"=>false,
              "msg"=>"Ticket for game_id ({$game_id}) and user_id ({$user_id}) was not found.."
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
              "msg"=>"There was a problem deleting the ticket record."
            );
        }
        die(json_encode($ret));
    }
}
