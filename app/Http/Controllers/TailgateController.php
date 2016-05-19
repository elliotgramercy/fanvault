<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Game;
use App\User;
use App\Tailgate;
use App\TailgatesTag;

use App\Http\Controllers\AwsController as AwsController;

class TailgateController extends Controller
{
    /*
    NAME: delete_tailgate
    DESCRIPTION: Deletes the tailgate record if found.
    PARAMETERS: 
        tailgate_id             - Existing tailgate id. 
    RETURNS: (str) ret - JSON object containing success status.
    Ex: {"success":true,"msg":"Tailgate deleted"}
    */
    public function delete_tailgate(Request $request){
        $id = $request->input("tailgate_id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The tailgate_id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = Tailgate::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The tailgate_id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $deleted = $cur->delete();
        if($deleted){
            $ret = array(
              "success"=>true,
              "msg"=>'Tailgate deleted.'
            );
        }
        else{
            $ret = array(
              "success"=>false,
              "msg"=>'There was a problem deleting the record.'
            );
        }
        die(json_encode($ret));
    }
    /*
    NAME: update_tailgate
    DESCRIPTION: Gets the row by user id or fb_user_id depending on which parameter is passed. If both are passed, it will 
        look for a match for both.
    PARAMETERS: 
    	tailgate_id 			- Existing tailgate id. If left blank, then it will create new tailgate otherwise it will update.
        creator_id              - (Required when new tailgate) user id from users table
        title      				- facebook user id. (if new, new record will be added to db, if existing it will update)
		description 				- tailgate description submitted by user.
		cost 					- tailgate cost
		game_id 				- (Required when new tailgate) references game id in games table
		lat 					- latitude value for the location
		lng 					- longitude value for the location
		tags 					- tailgate tags (comma separated list of tag ids)
        photo                   - image file
        start_time              - start time Format('Y-m-d H:i:s')
        end_time                - end time Format('Y-m-d H:i:s')
    RETURNS: (str) ret - JSON object containing tailgate data.
    Ex: {"success":true,"msg":"The tailgate was created\/updated successfully.The creator_id did not have a value submitted. The title did not have a value submitted. The description did not have a value submitted. The cost did not have a value submitted. The game_id did not have a value submitted. The lat did not have a value submitted. The lng did not have a value submitted. The start_time did not have a value submitted. The end_time did not have a value submitted.","tailgate":{"id":3,"creator_id":"69","title":"First tailgate!","description":"","cost":"0.00","game_id":"1","lng":"0.0000000","lat":"0.0000000","created_at":"2016-05-03 20:10:29","updated_at":"2016-05-10 17:43:53","photo":"https:\/\/s3.amazonaws.com\/fanvaultapp\/tailgates\/%7B4A04494D-6F2A-1C60-1A88-57B002F88890%7D.png","start_time":"0000-00-00 00:00:00","end_time":"0000-00-00 00:00:00"},"tags":["1","3","5"]}
    */
    public function update_tailgate(Request $request){
    	$tailgate_id = $request->input("tailgate_id");
        if(isset($tailgate_id) && $tailgate_id !== ''){
            //update
            $cur = Tailgate::where('id',$tailgate_id)->first();
            if(is_null($cur)){  //user not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The tailgate_id ({$tailgate_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
        }
        else{
        	//new
        	$creator_id = $request->input("creator_id");
	        if(!isset($creator_id) || $creator_id === ''){
	            $ret = array(
	              "success"=>false,
	              "msg"=>'The creator_id was not recieved. It is required that you pass this when creating new tailgate.'
	            );
	            die(json_encode($ret));
	        }
	        $temp = User::where('id',$creator_id)->first();
	        if(is_null($temp)){  //user not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The creator_id ({$creator_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
	        $game_id = $request->input("game_id");
	        if(!isset($game_id) || $game_id === ''){
	            $ret = array(
	              "success"=>false,
	              "msg"=>'The game_id was not recieved. It is required that you pass this when creating new tailgate.'
	            );
	            die(json_encode($ret));
	        }
	        $temp = Game::where('id',$game_id)->first();
	        if(is_null($temp)){  //user not found
	            $ret = array(
	              "success"=>false,
	              "msg"=>"The game_id ({$game_id}) provided was not found in the database."
	            );
	            die(json_encode($ret));
	        }
	        $cur = new Tailgate;
        }
        $valid_fields = ['creator_id','title','description','cost','game_id','lat','lng','photo','start_time','end_time'];
        $msg = array();
        foreach($valid_fields as $field){
            if($field == 'photo'){
                $aws_controller = new AwsController;
                if($cur->id !== null){
                    //this means that the tailgate is not new which means get current photo and delete it.
                    $aws_image_file_name = $cur->photo;
                    $aws_image_file_name = basename($aws_image_file_name);
                    $aws_controller->delete_aws_image($aws_image_file_name,'tailgates');   //nothing checking success for this
                    //lets just hope this deletes and works. Not sure what to do if doesnt.. maybe email someone? Maybe save in a different table..idk, im just going to ignore for now..
                }
                $photo = $request->file('photo');
                if(isset($photo)){
                    $uploaded_image_url = $aws_controller->upload_image($photo,'tailgates');
                    if($uploaded_image_url !== false){
                        $cur->photo = $uploaded_image_url;
                    }
                    else{
                        $msg[] = "The photo provided was not uploaded successfully.";
                        continue;
                    }
                }
            }
            else{
                $field_value =  $request->input($field);
                if(isset($field_value) && $field_value !== ''){
                    //value defined
                    if($field == 'creator_id'){
                        $temp = User::where('id',$field_value)->first();
                        if(is_null($temp)){  //user not found
                            $msg[] = "The creator_id ({$field_value}) provided was not found in the database.";
                            continue;
                        }
                        else{
                            $cur->$field = $field_value;
                        }
                    }
                    elseif($field == 'game_id'){
                        $temp = Game::where('id',$field_value)->first();
                        if(is_null($temp)){  //user not found
                            $msg[] = "The game_id ({$field_value}) provided was not found in the database.";
                            continue;
                        }
                        else{
                            $cur->$field = $field_value;
                            $msg[] = "The {$field} field was set to {$field_value} value.";
                        }
                    }
                    else{
                        $cur->$field = $field_value;
                        $msg[] = "The {$field} field was set to {$field_value} value.";
                    }
                }
                else{
                    $msg[] = "The {$field} did not have a value submitted.";
                }
            }
        }
        $saved = $cur->save();
        $tags =  $request->input('tags');
        if(isset($tags) && $tags !== ''){
        	$tags = explode(',',$tags);
        	$insert_tag_data = array();
        	foreach($tags as $tag){
        		//insert if not exists.
        		$temp = TailgatesTag::where(['tailgate_id'=>$cur->id,'tag_id'=>$tag])->first();
        		if(is_null($temp)){
        			$insert = new TailgatesTag;
	        		$insert->tailgate_id = $cur->id;
	        		$insert->tag_id = $tag;
	        		$insert->save();
        		}
        	}
        } 
        $ret = array(
          "success"=>$saved,
          "msg"=>"The tailgate was created/updated successfully." . implode(' ',$msg),
          "tailgate"=>$cur
        );
    	$get_tags = TailgatesTag::where('tailgate_id',$cur->id)->get()->pluck('tag_id');
    	$ret['tags'] = $get_tags;
        die(json_encode($ret));  	
    }
}
