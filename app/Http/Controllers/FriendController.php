<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Friend;

class FriendController extends Controller
{
    /*
    NAME: check_friends
    DESCRIPTION: helper function takes in id and friend id. Both are expected to be valid ids.
    PARAMETERS: 
        id              - user table row id
        friend_id       - user table row id for friend.
    RETURNS:            - true or false.
    */
    public function check_friends($id,$friend_id){
        $friends = Friend::where(['user_1'=>$id,'user_2'=>$friend_id])->orwhere(['user_2'=>$id,'user_1'=>$friend_id]);
        if($friends->count() > 0){
            return true;
        }
        else{
            return false;
        }
    }
    /*
    NAME: are_friends
    DESCRIPTION: takes in id and friend id, checks if they are valid users, returns whether or not they are friends.
    PARAMETERS: 
        id              - user table row id
        friend_id       - user table row id for friend.
    RETURNS: (str) ret - true or false, or an error message.
    */
    public function are_friends(Request $request){
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
        $friend_id = $request->input("friend_id");
        if(!isset($friend_id) || $friend_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$friend_id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The friend_id ({$friend_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $ret = $this->check_friends($id,$friend_id);
        die(json_encode($ret)); 
    }
    /*
    NAME: get_all_friends
    DESCRIPTION: takes in id and returns all app users that are fiends with that id.
    PARAMETERS: 
        id              - user table row id
    RETURNS: (str) ret - JSON object with all users friends or error msg.
    Ex: 
    [{"id":5,"name":"","email":"test@TEST.COM","created_at":"2016-05-02 00:29:43","updated_at":"2016-05-02 00:29:43","first_name":"mac","last_name":"d","dob":"0000-00-00 00:00:00","gender":"male","fb_user_id":"1633910033509123","fb_auth_tok":"","photo":""}]
    */
    public function get_all_friends(Request $request){
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
        $friends_one = Friend::where(['user_1'=>$id])->pluck('user_2')->all();
        $friends_two = Friend::where(['user_2'=>$id])->pluck('user_1')->all();
        $friends = array_merge($friends_one,$friends_two);
        $ret = User::whereIn('id', $friends)->get();
        die(json_encode($ret));
    }
    /*
    NAME: make_friends
    DESCRIPTION: Will take a id and a list of friend_ids that are friends, and connect them in db to create a link 
        between friends. 
    PARAMETERS: 
        id              - user table row id
        friend_ids      - user table row ids to be friends
    RETURNS: (str) ret - JSON object with status of whether success was true or false, as well as an explanation
        message explaining what was done.
    Ex: {"success":true,"msg":"Records created or updated successfully. Users 5 and 1 were already friends, so nothing was changed. Friend id 3 is not a valid user id. Users 5 and 8 are now friends."}
    */
    public function make_friends(Request $request){
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
        $friend_ids = $request->input("friend_ids");
        if(!isset($friend_ids) || $friend_ids === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_ids were not set or blank.'
            );
            die(json_encode($ret));
        }
        $friend_ids = explode(',',$friend_ids);
        $msg = array();
        //loop through friend_ids and make sure that the facebook friends table has the links.
        foreach($friend_ids as $friend_id){
            //first lets check that the friend_id is a valid id in users table
            $cur = User::where('id',$friend_id)->first();
            if(is_null($cur)){
                $msg[] = "Friend id {$friend_id} is not a valid user id.";
                continue;
            }
            //lets check if there is already a link between the two ides in the friends_table.
            $already_friends = $this->check_friends($id,$friend_id);
            if(!$already_friends){
                //if not already friends then create the new row in friends_table.
                $cur = new Friend;
                $cur->user_1 = $id;
                $cur->user_2 = $friend_id;
                $saved = $cur->save();
                if($saved){
                    //add to msg.
                    $msg[] = "Users {$id} and {$friend_id} are now friends.";
                }
                else{
                    $msg[] = "Unable to save a link between Users {$id} and {$friend_id}.";
                }
            }
            else{
                //add to msg that these are already friends.
                $msg[] = "Users {$id} and {$friend_id} were already friends, so nothing was changed.";
            }
        }
        $ret = array(
          "success"=>true,
          "msg"=>'Records created or updated successfully. ' . implode(' ',$msg)
        );
        die(json_encode($ret));
    }
    /*
    NAME: delete_friend_link
    DESCRIPTION: Takes in the id and friend id, which are expected to already be friends in database. Then finds the record
    	and removes it.
    PARAMETERS: 
        id              - user table row id
        friend_id       - user table row id
    RETURNS: (str) ret  - number of affected rows.
    */
    public function delete_friend_link($id,$friend_id){
        $affectedRows = Friend::where(['user_1'=>$id,'user_2'=>$friend_id])->orwhere(['user_2'=>$id,'user_1'=>$friend_id])->delete();
        return $affectedRows;
    }
    /*
    NAME: unfriend
    DESCRIPTION: Will take a id and a list of friend_ids that are friends, and remove all links in the friends table
    	thus making them no longer friends.
    PARAMETERS: 
        id              - user table row id
        friend_ids      - user table row ids to unfriend
    RETURNS: (str) ret - JSON object with status of whether success was true or false, as well as an explanation
        message explaining what was done.
    Ex: {"success":true,"msg":"Records updated successfully. Users 5 and 6 are no longer friends. Friend id 3 is not a valid user id. Users 5 and 7 were not friends, so nothing was changed. Users 5 and 8 are no longer friends."}
    */
    public function unfriend(Request $request){
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
        $friend_ids = $request->input("friend_ids");
        if(!isset($friend_ids) || $friend_ids === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The friend_ids were not set or blank.'
            );
            die(json_encode($ret));
        }
        $friend_ids = explode(',',$friend_ids);
        $msg = array();
        //loop through friend_ids and make sure that the facebook friends table has the links.
        foreach($friend_ids as $friend_id){
            //first lets check that the friend_id is a valid id in users table
            $cur = User::where('id',$friend_id)->first();
            if(is_null($cur)){
                $msg[] = "Friend id {$friend_id} is not a valid user id.";
                continue;
            }
            //lets check if there is already a link between the two ides in the friends_table.
            $already_friends = $this->check_friends($id,$friend_id);
            if($already_friends){
                //if  already friends then unfriend.
                $deletedRows = $this->delete_friend_link($id,$friend_id);
                if($deletedRows > 0){
                	$msg[] = "Users {$id} and {$friend_id} are no longer friends.";
                }
                else{
                	$msg[] = "Unable to delete a link between Users {$id} and {$friend_id}.";
                }
            }
            else{
                //add to msg that these are already friends.
                $msg[] = "Users {$id} and {$friend_id} were not friends, so nothing was changed.";
            }
        }
        $ret = array(
          "success"=>true,
          "msg"=>'Records updated successfully. ' . implode(' ',$msg)
        );
        die(json_encode($ret));
    }
}
