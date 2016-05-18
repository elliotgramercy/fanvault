<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\FriendsInvite;
use App\User;

class FriendsInviteController extends Controller
{
	/*
    NAME: invite_friend
    DESCRIPTION: takes in id and friend id, checks if friend was already invited, invites the friend.
    PARAMETERS: 
        id              - user table row id
        friend_fb_id       - facebook_id for the friend to invite
    RETURNS: (str) ret - JSON whether or not friend was invited or error message.
    */
    public function invite_friend(Request $request){
    	$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $friend_fb_id = $request->input("friend_fb_id");
        if(!isset($friend_fb_id) || $friend_fb_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_fb_id was not recieved.'
            );
            die(json_encode($ret));
        }
        //check first to see if this record (id, fb_id) is already in the friends_invites table.
        $msg = array();
        if(FriendsInvite::where(['user'=>$id, 'invited_fb_id'=>$friend_fb_id])->exists()){
            //this record is already in database, check if status is declined, if so then set back to pending.
            $invite = FriendsInvite::where(['user'=>$id, 'invited_fb_id'=>$friend_fb_id])->first();
            if($invite->status == 'declined'){
            	//set status back to pending, reinvite the friend.	
            	$cur = $invite;
            	$msg[] = "This friend ({$friend_fb_id}) had declined the previous invited. Is now re-invited.";
            }
            else{
                $ret = array(
                  "success"=>false,
                  "msg"=>'The friend_fb_id was already invited and is still pending. No changes were made.'
                );
                die(json_encode($ret));
            }
        }
        else{
        	$cur = new FriendsInvite;
        }
    	$cur->user = $id;
    	$cur->invited_fb_id = $friend_fb_id;
    	$cur->status = 'pending';
    	$saved = $cur->save();
    	if($saved){
    		$ret = array(
              "success"=>true,
              "msg"=>'The friend_fb_id was invited and is now pending.' . implode(' ',$msg)
            );
    	}
    	else{
    		$ret = array(
              "success"=>false,
              "msg"=>'Unable to create record in database.'
            );
    	}
    	die(json_encode($ret));
    }
    /*
    NAME: get_all_invited
    DESCRIPTION: takes in id and returns a list of fb_ids for all invited friends.
    PARAMETERS: 
        id              - user table row id
    RETURNS: (str) ret - JSON array of fb_ids representing the friends that were invited where status is pending.
    */
    public function get_all_invited(Request $request){
    	$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
    	$invites = FriendsInvite::where(['user'=>$id, 'status'=>'pending'])->pluck('invited_fb_id')->all();
        die(json_encode($invites));
    }
    /*
    NAME: uninvite
    DESCRIPTION: takes in id and friend id, checks if friend was already invited, uninvites the friend if record exits by 
    	deleting the record.
    PARAMETERS: 
        id              - user table row id
        friend_fb_id       - facebook_id for the friend to uninvite
    RETURNS: (str) ret - rows affected. Should equal to 1 if friend was uninvited. Will also print a message.
    */
    public function uninvite(Request $request){
    	$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $friend_fb_id = $request->input("friend_fb_id");
        if(!isset($friend_fb_id) || $friend_fb_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_fb_id was not recieved.'
            );
            die(json_encode($ret));
        }
        //check first to see if this record (id, fb_id) is already in the friends_invites table.
        $msg = array();
        $invite = FriendsInvite::where(['user'=>$id, 'invited_fb_id'=>$friend_fb_id]);
        if(is_null($invite)){
        	$ret = array(
              "success"=>false,
              "msg"=>"It looks like user id ({$id}) never invited fb_id ({$friend_fb_id})."
            );
            die(json_encode($ret));
        }
        $affectedRows = $invite->delete();
        $ret = array(
          "success"=>true,
          "affected_rows"=>$affectedRows
        );
        die(json_encode($ret));
    }
}
