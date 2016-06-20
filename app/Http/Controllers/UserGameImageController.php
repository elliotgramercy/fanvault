<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Game;
use App\UserGameImage;
use App\Http\Controllers\AwsController as AwsController;

class UserGameImageController extends Controller
{
	/*
    Name: delete
    Description: deletes an image
    Parameters: 
    	image_id	- (Required) existing image id. Cannot be blank or non existing image id.
    Returns: (str) ret - success
    Ex:{"success":true}
    */
    public function delete(Request $request){
    	$image_id = $request->input("image_id");
        if(!isset($image_id) || $image_id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The image_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur_image = UserGameImage::where('id',$image_id)->first();
        if(is_null($cur_image)){  //image not found
            $ret = array(
              "success"=>false,
              "msg"=>"The image_id ({$image_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $aws_controller = new AwsController;
        $aws_image_file_name = $cur_image->photo;
        if(strpos($aws_image_file_name,'amazonaws') !== FALSE){ //if current photo is on amazon.
            $aws_image_file_name = basename($aws_image_file_name);
            $aws_controller->delete_aws_image($aws_image_file_name,'usergames');   //nothing checking success for this
            //lets just hope this deletes and works. Not sure what to do if doesnt.. maybe email someone? Maybe save in a different table..idk, im just going to ignore for now..
        }
        $deleted = $cur_image->delete();
        if($deleted){
        	$ret = array(
              "success"=>true
            );
        }
        else{
        	$ret = array(
              "success"=>false,
              "msg"=>"The system was unable to delete that image."
            );
        }
        die(json_encode($ret));
    }
	/*
    Name: get_all_game_images
    Description: gets all images for one game
    Parameters: 
    	user_id	- (Required) existing user id. Cannot be blank or non existing user id.
    	game_id - (Required) existing game id. Cannot be blank or non existing game id.
    Returns: (str) ret - JSON array of image objects
    Ex:[{"id":12,"user_id":"66","game_id":"62","caption":"","photo":"https:\/\/s3.amazonaws.com\/fanvaultapp\/usergames\/%7B223C91ED-49F6-7532-1FAB-0EE49DD2C32B%7D.jpg","created_at":"2016-05-18 21:07:37","updated_at":"2016-05-18 21:07:37"}]
    */
    public function get_all_game_images(Request $request){
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
        return UserGameImage::where('game_id',$game_id)->get();
    }
	/*
    Name: get_users_images_for_game
    Description: gets all game images from one user for one game
    Parameters: 
    	user_id	- (Required) existing user id. Cannot be blank or non existing user id.
    	game_id - (Required) existing game id. Cannot be blank or non existing game id.
    Returns: (str) ret - JSON array of image objects
    Ex:[{"id":12,"user_id":"66","game_id":"62","caption":"","photo":"https:\/\/s3.amazonaws.com\/fanvaultapp\/usergames\/%7B223C91ED-49F6-7532-1FAB-0EE49DD2C32B%7D.jpg","created_at":"2016-05-18 21:07:37","updated_at":"2016-05-18 21:07:37"}]
    */
    public function get_users_images_for_game(Request $request){
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
        return UserGameImage::where(['user_id'=>$user_id,'game_id'=>$game_id])->get();
    }
	/*
    Name: update
    Description: adds a new image or updates existing
    Parameters: 
    	image_id - existing image id, if left blank, then new image will be created.
    	user_id	- (Required) existing user id. Cannot be blank or non existing user id.
    	game_id - (Required) existing game id. Cannot be blank or non existing game id.
    	caption - The image caption. Can take in an array when uploading multiple images. Make sure indexes on caption[] match
            the indexes on photo[]
        private - whether this will be viewable by everyone using app (stadium) or not. Default to true, so either pass false,  
            or dont pass anything.
    	photo - (Required) This will either be a file or a string. String will be considered url and stored. This can be an
            an array as well (photo[])
    		File on the other hand will be uploaded to Amazon
    Returns: (str) ret - JSON object containing success and message
    Ex: {"success":true,"msg":"Record created or updated successfully. user_id field was saved successfully. game_id field was saved successfully. caption field DID NOT have a value. Photo file was successfully uploaded to AWS S3. The photo provided DID NOT have a value.","user":{"id":10,"user_id":"63","game_id":"62","caption":"","photo":"https:\/\/s3.amazonaws.com\/fanvaultapp\/usergames\/%7B687B5D84-6E6F-B9FE-520F-A050E148EE26%7D.jpg","created_at":"2016-05-18 20:12:40","updated_at":"2016-05-18 20:13:44"}}
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
        if(is_null($cur_game)){  //game not found
            $ret = array(
              "success"=>false,
              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $image_id = $request->input("image_id");
        if(isset($image_id) && $image_id !== ''){
            $cur = UserGameImage::where('id',$image_id)->first();
	        if(is_null($cur)){  //image not found
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
            $private = $request->input('private');
            $private = $private === 'false' ? false : true;
            $cur->private = $private;
            if(isset($photo_image)){
                if(!is_array($photo_image) || count($photo_image) < 1){
                    if(is_array($photo_image)){
                        $photo_image = $photo_image[0];
                    }
                    $valid_fields = ['user_id','game_id','caption'];
                    foreach($valid_fields as $field){
                        $field_value = $request->input($field);
                        if(isset($field_value)){
                            $cur->$field = $field_value;
                        }
                    };
                    $uploaded_image_url = $aws_controller->upload_image($photo_image,'usergames');
                    if($uploaded_image_url !== false){
                        $cur->photo = $uploaded_image_url;
                    }
                    $saved = $cur->save();
                    if($saved){
                        $ret = array(
                          "success"=>true,
                          "msg"=>'Record created or updated successfully.',
                          'image'=>$cur
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
                else{
                    if($cur->id !== null){
                        $ret = array(
                          "success"=>false,
                          "msg"=>"You cannot pass multiple images when editing one single existing image. Given image_id ({$image_id})."
                        );
                        die(json_encode($ret));
                    }
                    $created_row_ids = array();
                    foreach($photo_image as $index=>$one_photo){
                        $cur = new UserGameImage;
                        $valid_fields = ['user_id','game_id','caption'];
                        foreach($valid_fields as $field){
                            $field_value = $request->input($field);
                            if($field === 'caption'){
                                if(isset($field_value[$index]) && $field_value[$index] !== ''){
                                    $cur->caption = $field_value[$index];
                                }
                            }
                            else{
                                if(isset($field_value)){
                                    $cur->$field = $field_value;
                                } 
                            }
                        }
                        $uploaded_image_url = $aws_controller->upload_image($one_photo,'usergames');
                        if($uploaded_image_url !== false){
                            $cur->photo = $uploaded_image_url;
                        }
                        $saved = $cur->save();
                        if($saved){
                            $created_row_ids[] = $cur->id;
                        }
                    }
                    $ret = array(
                      "success"=>true,
                      "msg"=>'Record created or updated successfully.',
                      'image'=>UserGameImage::whereIn('id',$created_row_ids)->get()
                    );
                    die(json_encode($ret));
                }
            }
            if(isset($photo_url) && $photo_url !== ''){
                $cur->photo = $photo_url;
                $valid_fields = ['user_id','game_id','caption'];
                foreach($valid_fields as $field){
                    $field_value = $request->input($field);
                    if(isset($field_value)){
                        $cur->$field = $field_value;
                    }
                }
                $saved = $cur->save();
                if($saved){
                    $ret = array(
                      "success"=>true,
                      "msg"=>'Record created or updated successfully.',
                      'image'=>$cur
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
        else{
            $ret = array(
              "success"=>false,
              "msg"=>'Photo field is required and was not recieved correctly.'
            );
            die(json_encode($ret));
        }
    }
}
