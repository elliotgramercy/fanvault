<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Game;
use App\UserGameImage;

class UserGameImageController extends Controller
{
	/*
    Name: update
    Description: adds a new image or updates existing
    Parameters: 
    	image_id - existing image id, if left blank, then new image will be created.
    	user_id	- (Required) existing user id. Cannot be blank or non existing user id.
    	game_id - (Required) existing game id. Cannot be blank or non existing game id.
    	caption - string caption
    	photo - (Required) This will either be a file or a string. String will be considered url and stored. 
    		File on the other hand will be uploaded to Amazon
    Returns: (str) ret - JSON object containing the number of rows created/updated, or an error string.
    Ex: {"rows_created":30}
    */
    public function update(){
    	$user_id = $request->input("user_id");
        if(!isset($user_id) || $user_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The user_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_user = User::where('id'=>$user_id)->first();
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
        $cur_game = Game::where('id'=>$game_id)->first();
        if(is_null($cur_game)){  //game not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $image_id = $request->input("image_id");
        if(isset($image_id) && $image_id !== ''){
            $cur = UserGameImage::where('id'=>$image_id)->first();
	        if(is_null($cur_image)){  //image not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The image_id ({$image_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
        }
        else{
        	$cur = new UserGameImage;
        }
        $valid_fields = ['user_id','game_id','caption','photo'];
        $msg = array();
        foreach($valid_fields as $field){
            if($field == 'photo'){
                $photo_image = $request->file('photo');
                $photo_url = $request->input('photo');
                if(isset($photo_image) || (isset($photo_url) && $photo_url !== '')){
                	//lets delete existing amazon photo if it exists.
	                $aws_controller = new AwsController;
	                if($cur->id !== null){
	                    //this means that the user is not new which means get current photo and delete it.
	                    $aws_image_file_name = $cur->photo;
	                    if(strpos($aws_image_file_name,'amazonaws') !== FALSE){ //if current photo is on amazon.
	                        $aws_image_file_name = basename($aws_image_file_name);
	                        $aws_controller->delete_aws_image($aws_image_file_name,'usergames');   //nothing checking success for this
	                        //lets just hope this deletes and works. Not sure what to do if doesnt.. maybe email someone? Maybe save in a different table..idk, im just going to ignore for now..
	                    }
	                }
	                if(isset($photo_image)){
	                    $uploaded_image_url = $aws_controller->upload_user_game_image($photo_image,'usergames');
	                    if($uploaded_image_url !== false){
	                        $cur->photo = $uploaded_image_url;
	                        $msg[] = 'Photo file was successfully uploaded to AWS S3.';
	                    }
	                    else{
	                        $msg[] = "The photo file provided was not uploaded successfully.";
	                        continue;
	                    }
	                }
	                if(isset($photo_url) && $photo_url !== ''){
	                    $cur->photo = $photo_url;
	                    $msg[] = 'Photo url string provided was saved successfully.';
	                }
	                else{
	                    $msg[] = 'The photo provided DID NOT have a value.';
	                }
                }
            }
            else{
                $field_value = $request->input($field);
                if($field == 'email'){
                    if(!isset($field_value) || $field_value == ''){
                        if($new_user){
                            $ret = array(
                              "success"=>false,
                              "msg"=>'Email field must have a value when creating a new user'
                            );
                            die(json_encode($ret));
                        }
                    }
                    else{
                        $temp_email_check = User::where('email',$field_value)->first();
                        if($temp_email_check->id !== $cur->id){
                            $ret = array(
                              "success"=>false,
                              "msg"=>'Email field must be unique, this email is aleady being used by a different user.'
                            );
                            die(json_encode($ret));
                        }
                    }
                }
                if($field != 'device_token' && isset($field_value)){
                    $cur->$field = $field_value;
                    //add to message the field that was successfully saved.
                    $msg[] = $field .' field was saved successfully.';
                }
                else{
                    //add to message that field listed did not have a value passed.
                    $msg[] = $field .' field DID NOT have a value.';
                }
            }
        }
        
    }
}
