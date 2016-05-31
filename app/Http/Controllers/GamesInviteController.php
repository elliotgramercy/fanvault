<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Game;
use App\GamesInvite;
use App\GamesAttendee;

class GamesInviteController extends Controller
{
    /*
	NAME: invite_friend
	DESCRIPTION: takes in user_id and friend id, checks if friend was already invited, invites the friend to the game_id. This will not allow you to invite any friend_ids that are already attending the game.
	IMPORTANT: It is very important that we DO NOT invite friend_ids that are already attending the game, because that would be redundant. It is also important that only one user can invite that friend to the game. It would not make sence to allow multiple users to invite the same friend to the game. Once one user invites a friend, that friend is marked as invited and others will not be able to invite that friend.
	PARAMETERS: 
	    user_id         - (Required) users id
	    friend_id       - (Required) friends user_id
	    game_id			- (Required) game_id
	RETURNS: (str) ret - JSON whether or not friend was invited or error message.
    */
    public function invite_friend(Request $request){
    	$user_id = $request->input("user_id");
        if(!isset($user_id) || $user_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The user_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $user = User::where('id',$user_id)->first();
        if(is_null($user)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The user_id ({$user_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $friend_id = $request->input("friend_id");
        if(!isset($friend_id) || $friend_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $friend = User::where('id',$friend_id)->first();
        if(is_null($friend)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The friend_id ({$friend_id}) provided was not found in the database."
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
        $game = Game::where('id',$game_id)->first();
        if(is_null($game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        //at this point we know we got valid ids
        //lets check that the given user isnt already attending the event
        $friend_attending = GamesAttendee::where(['user_id'=>$friend_id,'game_id'=>$game_id])->first();
        if(!is_null($friend_attending)){
        	$ret = array(
              "success"=>false,
              "msg"=>"The friend_id ({$friend_id}) already attending this game."
            );
            die(json_encode($ret));
        }
        //lets check that ANOTHER user hasnt invited this friend to the game
        $existing_invite = GamesInvite::where(['friend_id'=>$friend_id,'game_id'=>$game_id])->first();
        if(!is_null($existing_invite)){
        	$ret = array(
              "success"=>false,
              'invite'=>$existing_invite,
              "msg"=>"Another user has already invited this friend_id to the game."
            );
            die(json_encode($ret));
        }
        //lets check if this invite record already exists
        $existing_invite = GamesInvite::where(['user_id'=>$user_id,'friend_id'=>$friend_id,'game_id'=>$game_id])->first();
        if(!is_null($existing_invite)){
        	if($existing_invite->status == 'accepted'){
        		$ret = array(
	              "success"=>false,
	              'invite'=>$existing_invite,
	              "msg"=>"The user already accepted this invite and is attending this game."
	            );
	            die(json_encode($ret));
        	}
        	elseif($existing_invite->status == 'pending'){
        		$ret = array(
	              "success"=>false,
	              'invite'=>$existing_invite,
	              "msg"=>"The user has already been invited. Status pending."
	            );
	            die(json_encode($ret));
        	}
        	elseif($existing_invite->status == 'declined'){
        		$existing_invite->status = 'pending';
        		$saved = $existing_invite->save();
        		if($saved){
        			$ret = array(
		              "success"=>true,
		              'invite'=>$existing_invite
		            );
		            die(json_encode($ret));
        		}
        		else{
        			$ret = array(
		              "success"=>false,
		              "msg"=>"There was a problem saving the record."
		            );
		            die(json_encode($ret));
        		}
        	}
        }
        else{
        	$existing_invite = new GamesInvite;
        	$existing_invite->user_id = $user_id;
        	$existing_invite->friend_id = $friend_id;
        	$existing_invite->game_id = $game_id;
        	$existing_invite->status = 'pending';
        	$saved = $existing_invite->save();
    		if($saved){
    			$ret = array(
	              "success"=>true,
	              'invite'=>$existing_invite
	            );
	            die(json_encode($ret));
    		}
    		else{
    			$ret = array(
	              "success"=>false,
	              "msg"=>"There was a problem saving the record."
	            );
	            die(json_encode($ret));
    		}
        }
    }
    /*
	NAME: get_friends_invited_to_game
	DESCRIPTION: takes in user_id and a game_id and and returns all friends that were invited to the game
	PARAMETERS: 
	    user_id         - (Required) users id
	    game_id			- (Required) game_id
	RETURNS: (str) ret - JSON object containing list of friends invited or error message
    */
    public function get_friends_invited_to_game(Request $request){
    	$user_id = $request->input("user_id");
        if(!isset($user_id) || $user_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The user_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $user = User::where('id',$user_id)->first();
        if(is_null($user)){  //user not found
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
        $game = Game::where('id',$game_id)->first();
        if(is_null($game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $invited_friends = GamesInvite::with('friend_user_obj')->where(['user_id'=>$user_id,'game_id'=>$game_id])->select('friend_id')->get();
        $ret = array(
          "success"=>true,
          "invited_friends"=>$invited_friends
        );
        die(json_encode($ret));
    }
    /*
	NAME: accept_invite
	DESCRIPTION: takes in friend_id and a game_id and changes the status of invite to accepted. Also adds friend_id to game_attendees table which marks the friend as attending. The friend_id in this case would be the user_id of the user that got the invite.
	IMPORTANT: It is very important that we DO NOT invite friend_ids that are already attending the game, because that would be redundant. It is also important that only one user can invite that friend to the game. It would not make sence to allow multiple users to invite the same friend to the game. Once one user invites a friend, that friend is marked as invited and others will not be able to invite that friend.
	PARAMETERS: 
	    friend_id         - (Required) users id
	    game_id			- (Required) game_id
	RETURNS: (str) ret - JSON object containng error message if needed, success status, and an invite if successful.
    */
    public function accept_invite(Request $request){
    	$friend_id = $request->input("friend_id");
        if(!isset($friend_id) || $friend_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $friend = User::where('id',$friend_id)->first();
        if(is_null($friend)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The friend_id ({$friend_id}) provided was not found in the database."
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
        $game = Game::where('id',$game_id)->first();
        if(is_null($game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        //we have valid ids. change the status to accepted
        $existing_invite = GamesInvite::where(['friend_id'=>$friend_id,'game_id'=>$game_id])->first();
        if(is_null($existing_invite)){
        	$ret = array(
              "success"=>false,
              "msg"=>"This friend_id ({$friend_id}) was not invited to this game_id ({$game_id}). Invite record not found."
            );
            die(json_encode($ret));
        }
        $existing_invite->status = 'accepted';
        $saved = $existing_invite->save();
		if($saved){
			//record was saved. Lets add user to Attending the game, if not already.
			$friend_attending = GamesAttendee::where(['user_id'=>$friend_id,'game_id'=>$game_id])->first();
        	if(is_null($friend_attending)){
        		//this friend is already attending, which means that the invite should not have been sent out.
        		//but there is a chance that invite was sent, and the user manually clicked "Attend Game" without
        		//accepting.
        		//add this friend to attending.
        		$friend_attending = new GamesAttendee;
		        $friend_attending->user_id = $friend_id;
		        $friend_attending->game_id = $game_id;
		        $saved = $friend_attending->save();
        	}
			$ret = array(
              "success"=>true,
              'invite_obj'=>$existing_invite,
              'attending_obj'=>$friend_attending
            );
            die(json_encode($ret));
		}
		else{
			$ret = array(
              "success"=>false,
              "msg"=>"There was a problem saving the record."
            );
            die(json_encode($ret));
		}
    }
    /*
	NAME: decline_invite
	DESCRIPTION: takes in friend_id and a game_id and changes the status of invite to declined. Does nothing else.
	PARAMETERS: 
	    friend_id         - (Required) users id
	    game_id			- (Required) game_id
	RETURNS: (str) ret - JSON object containng error message if needed and success status
    */
    public function decline_invite(Request $request){
    	$friend_id = $request->input("friend_id");
        if(!isset($friend_id) || $friend_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $friend = User::where('id',$friend_id)->first();
        if(is_null($friend)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The friend_id ({$friend_id}) provided was not found in the database."
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
        $game = Game::where('id',$game_id)->first();
        if(is_null($game)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $existing_invite = GamesInvite::where(['friend_id'=>$friend_id,'game_id'=>$game_id])->first();
        if(is_null($existing_invite)){
        	$ret = array(
              "success"=>false,
              "msg"=>"This friend_id ({$friend_id}) was not invited to this game_id ({$game_id}). Invite record not found."
            );
            die(json_encode($ret));
        }
        $existing_invite->status = 'declined';
        $saved = $existing_invite->save();
		if($saved){
			$ret = array(
              "success"=>true,
              'invite_obj'=>$existing_invite,
            );
            die(json_encode($ret));
		}
		else{
			$ret = array(
              "success"=>false,
              "msg"=>"There was a problem saving the record."
            );
            die(json_encode($ret));
		}
    }
}
