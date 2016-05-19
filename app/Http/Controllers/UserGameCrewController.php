<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Game;
use App\UserGameCrew;

class UserGameCrewController extends Controller
{
	/*
    Name: delete
    Description: deletes a crew_record
    Parameters: 
    	crew_record_id	- (Required) existing crew id id. Cannot be blank or non existing user id.
    Returns: (str) ret - success
    Ex:{"success":true}
    */
    public function delete(Request $request){
    	$crew_record_id = $request->input("crew_record_id");
        if(!isset($crew_record_id) || $crew_record_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The crew_record_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = UserGameCrew::where('id',$crew_record_id)->first();
        if(is_null($cur)){  //image not found
            $ret = array(
              "success"=>false,
              "msg"=>"The crew_record_id ({$crew_record_id}) provided was not found in the database."
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
              "msg"=>"The system was unable to delete that record."
            );
        }
        die(json_encode($ret));
    }
	/*
    Name: get_user_crew_members_for_game
    Description: gets all game crew members from one user for one game
    Parameters: 
    	user_id	- (Required) existing user id. Cannot be blank or non existing user id.
    	game_id - (Required) existing game id. Cannot be blank or non existing game id.
    Returns: (str) ret - JSON array of image objects
    Ex:[{"id":1,"user_id":"63","game_id":"62","crew_member_user_id":"66","crew_member_first_name":null,"crew_member_last_name":null,"created_at":"2016-05-19 15:21:15","updated_at":"2016-05-19 15:29:03"}]
    */
	public function get_user_crew_members_for_game(Request $request){
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
        if(is_null($cur_game)){  //game not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        return UserGameCrew::where(['user_id'=>$user_id,'game_id'=>$game_id])->get();
	}
    /*
    Name: update
    Description: adds a new crew member or updates existing
    Parameters: 
    	crew_record_id 		- 	existing record id. This is used only when updating existing.
    	user_id				- 	(Required if adding new crew member) existing user id.
    	crew_member_user_id - 	(This or first and last name are required when creating new crew member)existing user id.
    	game_id 			- 	(Required if adding new crew member) existing game id.
    	crew_member_first_name 	- 	string for first anem (if crew member is NOT an app user)
    	crew_member_last_name	-	string for last name (if crew member NOT an app user)
	IMPORTANT: crew_member_user_id OR first_name and last name are always required.
    Returns: (str) ret - JSON object containing success, crew record object, and message
    Ex: {"success":true,"crew_member":{"user_id":"63","game_id":"62","crew_member_user_id":"66","updated_at":"2016-05-19 15:16:56","created_at":"2016-05-19 15:16:56","id":1}}
    */
    public function update(Request $request){
    	$crew_record_id = $request->input("crew_record_id");
        if(!isset($crew_record_id) || $crew_record_id === ''){	//this means that he is creating a new crew member.
            //this means we need to check that the required user_id, game_id and crew_member_user_id are all there and valid.
            $user_id = $request->input("user_id");
	        if(!isset($user_id) || $user_id === ''){
	            $ret = array(
	              "success"=>false,
	              "msg"=>'The user_id was not recieved and is required when creating a new crew member.'
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
	              "msg"=>'The game_id was not recieved and is required when creating a new crew member.'
	            );
	            die(json_encode($ret));
	        }
	        $cur_game = Game::where('id',$game_id)->first();
	        if(is_null($cur_game)){  //game not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
	        //at this point it means we got a valid user_id and game.
	        $cur = new UserGameCrew;
	        $cur->user_id = $user_id;
	        $cur->game_id = $game_id;
        }
        else{
        	//get existing crew member.
        	$cur = UserGameCrew::where('id',$crew_record_id)->first();
	        if(is_null($cur)){  //user not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The crew_record_id ({$crew_record_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
	        //at this point we know that cur is valid.
	        $user_id = $request->input("user_id");
	        if(isset($user_id) && $user_id !== ''){	//if user_id is set then check if valid.
	            $cur_user = User::where('id',$user_id)->first();
		        if(is_null($cur_user)){  //user not found
		            $ret = array(
		              "success"=>false,
		              "msg"=>"The user_id ({$user_id}) provided was not found in the database."
		            );
		            die(json_encode($ret));
		        }
		        $cur->user_id = $user_id;
	        }
	        $game_id = $request->input("game_id");
	        if(isset($game_id) && $game_id !== ''){
	            $cur_game = Game::where('id',$game_id)->first();
		        if(is_null($cur_game)){  //game not found
		            $ret = array(
		              "success"=>false,
		              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
		            );
		            die(json_encode($ret));
		        }
		        $cur->game_id = $game_id;
	        }
        }
        //here we need to check that either the crew_member_id was passed or first and last names are submitted.
        $crew_member_user_id = $request->input("crew_member_user_id");
        $crew_member_first_name = $request->input("crew_member_first_name");
        $crew_member_last_name = $request->input("crew_member_last_name");
        if(
        	(!isset($crew_member_user_id) || $crew_member_user_id === '') && 
        	(
	        	(!isset($crew_member_first_name) || $crew_member_first_name === '') &&
	        	(!isset($crew_member_last_name) || $crew_member_last_name === '')
        	)
    	){
            $ret = array(
              "success"=>false,
              "msg"=>'Either the crew_member_user_id or the first and last names must be submitted, none recieved.'
            );
            die(json_encode($ret));
        }
        //lets also check that BOTH arent passed in. Should either be user ID OR the first and last name. Not both.
        if((isset($crew_member_user_id) && $crew_member_user_id !== '') &&
        	(isset($crew_member_first_name) && $crew_member_first_name !== '')&&
        	(isset($crew_member_last_name) && $crew_member_last_name !== '')){
        	$ret = array(
              "success"=>false,
              "msg"=>'Either the crew_member_user_id or the first and last names must be submitted, but not both.'
            );
            die(json_encode($ret));
        }
        if(isset($crew_member_user_id) && $crew_member_user_id !== ''){
        	$cur_crew_member = User::where('id',$crew_member_user_id)->first();
	        if(is_null($cur_crew_member)){  //user not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The crew_member_user_id ({$crew_member_user_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
	        //valid crew_member_user_id was passed so use it.
	        $cur->crew_member_user_id = $crew_member_user_id;
	        //this means clear the first and last names
	        $cur->crew_member_first_name = null;
        	$cur->crew_member_last_name = null;
        }
        else{
        	if(
        		(!isset($crew_member_first_name)||$crew_member_first_name === ''||strlen($crew_member_first_name) < 2) ||
	        	(!isset($crew_member_last_name)||$crew_member_last_name === ''||strlen($crew_member_last_name) < 2)
        	){
        		$ret = array(
	              "success"=>false,
	              "msg"=>"If the crew_member_user_id is not passed then the crew_member_first_name and crew_member_last_name must both be present, not blank, and atleast 2 characters long."
	            );
	            die(json_encode($ret));
        	}
        	//at this point we know that we got the first and last names so lets set them.
        	$cur->crew_member_first_name = $crew_member_first_name;
        	$cur->crew_member_last_name = $crew_member_last_name;
        	//this means clear the crew_member_user_id
        	$cur->crew_member_user_id = null;
        }
        //I mean if we got all the way down here, then we got all the valid info in the cur object so all that is left is to save.
        $saved = $cur->save();
        if($saved){
        	$ret = array(
              "success"=>true,
              'crew_member'=>$cur
            );
        }
        else{
        	$ret = array(
              "success"=>false,
              "msg"=>'There was a problem saving the record'
            );
        }
        die(json_encode($ret));
    }
}
