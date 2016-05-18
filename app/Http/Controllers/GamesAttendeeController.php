<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Game;
use App\GamesAttendee;

use DB;

class GamesAttendeeController extends Controller
{
    /*
    NAME: remove_game_attendee
    DESCRIPTION: This will remove user_id and game_id record from the table which means user is no longer attending the game
    PARAMETERS: 
        user_id              - existing user id
        game_id      - existing game id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, and a message if needed.
    Ex: {"success":true}
    */
    public function remove_game_attendee(Request $request){
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
        $cur = GamesAttendee::where(['game_id'=>$game_id,'user_id'=>$user_id])->first();
        if(is_null($cur)){
            $ret = array(
              "success"=>false,
              "msg"=>"This user ({$user_id}) not attending this game ({$game_id}). Nothing was changed."
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
    NAME: add_game_attendee
    DESCRIPTION: This will add user_id to the game_id, which then means that the user is attending that game.
    PARAMETERS: 
        user_id              - existing user id
        game_id      - existing game id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, and a message if needed.
    Ex: {"success":true}
    */
    public function add_game_attendee(Request $request){
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
        $cur = GamesAttendee::where(['game_id'=>$game_id,'user_id'=>$user_id])->first();
        if(!is_null($cur)){
            $ret = array(
              "success"=>false,
              "msg"=>"This user ({$user_id}) is already attending this game ({$game_id}). Nothing was changed."
            );
            die(json_encode($ret));
        }
        //we got valid user_id and game_id so create new row in GamesAttendee
        $cur = new GamesAttendee;
        $cur->user_id = $user_id;
        $cur->game_id = $game_id;
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
    /*
    NAME: get_user_games_attending
    DESCRIPTION: This will return all games that are in the future that the user is attending
    PARAMETERS: 
        user_id              - existing user id
    RETURNS: (str) ret - JSON object with status of whether success was true or false, as well as the array of game objects, and a message if error.
    Ex: {"success": true, "future_games": [...], "past_games": [...]}
    */
    public function get_user_games_attending(Request $request){
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
        $user_obj = User::where('id',$user_id)->with('future_games_attending','past_games_attending')->first();
        $ret = array(
          "success"=>true,
          "user"=>$user_obj
        );
        die(json_encode($ret));
    }
}
