<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Friend;
use App\Game;
use App\Ticket;
use App\Venue;
use App\Tailgate;
use App\Team;
use App\GamesAttendee;
use App\UserNearbyStadium;
use App\UserGameImage;
use App\GamesLineup;
use App\GamesPlayer;
use App\GamesOfficial;
use App\Official;
use App\Player;

use App\Http\Controllers\UserDeviceController as UserDeviceController;
use App\Http\Controllers\AwsController as AwsController;

use DB;

class UserController extends Controller
{
    /*
    NAME: get_all_users
    DESCRIPTION: Gets all users from db.
    RETURNS: (str) ret - JSON object containing the user data.
    Ex: [{"id":1,"name":"","email":"","created_at":"2016-05-02 00:24:48","updated_at":"2016-05-02 00:24:48","first_name":"e","last_name":"sabitov","dob":"0000-00-00 00:00:00","gender":"male","fb_user_id":"1633910033509707","fb_auth_tok":"","photo":""},{"id":5,"name":"","email":"test@TEST.COM","created_at":"2016-05-02 00:29:43","updated_at":"2016-05-02 00:29:43","first_name":"mac","last_name":"d","dob":"0000-00-00 00:00:00","gender":"male","fb_user_id":"1633910033509123","fb_auth_tok":"","photo":""}]
    */
    public function get_all_users(){
        $users = User::all();
        die(json_encode($users->all()));
    }
    /* THIS IS OLD
    NAME: get_user
    DESCRIPTION: Gets the row by user id or fb_user_id depending on which parameter is passed. If both are passed, it will 
        look for a match for both.
    PARAMETERS: 
        id              - user table row id (editing existing user row)
        fb_user_id      - facebook user id. (if new, new record will be added to db, if existing it will update)
    RETURNS: (str) ret - JSON object containing the user data.
    Ex: {"id":5,"name":"","email":"","created_at":"2016-04-27 21:00:39","updated_at":"2016-04-27 21:00:39","first_name":"e","last_name":"sabitov","dob":"0000-00-00 00:00:00","gender":"male","fb_user_id":"1633910033509707","fb_auth_tok":"","photo":""}
    */
    public function get_user(Request $request){
        $id = $request->input("id");
        $fb_user_id = $request->input("fb_user_id");
        if((isset($id) && $id !== '') && (isset($fb_user_id) && $fb_user_id !== '')){
            $cur = User::where(['id'=>$id,'fb_user_id'=>$fb_user_id])->first();
        }
        elseif(isset($id) && $id !== ''){
            $cur = User::where('id',$id)->first();
        }
        elseif(isset($fb_user_id) && $fb_user_id !== ''){
            $cur = User::where('fb_user_id',$fb_user_id)->first();
        }
        else{
            $ret = array(
              "success"=>false,
              "msg"=>'You are required to pass fb_user_id or id. Neither was recieved.'
            );
            die(json_encode($ret));
        }
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>'The fb_user_id, id, or the combination of both, that you provided was not found in the database.'
            );
            die(json_encode($ret));
        }
        else{
            $ret = array(
              "success"=>true,
              "user"=>$cur
            );
            die(json_encode($ret));
        }
    }
    /*
    NAME: get
    DESCRIPTION: Gets the row using id or fb_user_id, if not found, then requires fb fields to create new user and return the newly created user.
    PARAMETERS: 
        id              - user table row id (editing existing user row)
        fb_user_id      - facebook user id. (if new, new record will be added to db, if existing it will update)
        first_name      - first_name string
        last_name       - last_name string
        email           - email string
        dob             - datetime format 'Y-m-d H:i:s'
        gender          - male or female string
        fb_auth_tok     - fb access token string
        photo           - photo can be a url string or a file upload object
        device_token    - iphone device token.
    RETURNS: (str) ret - JSON object containing the user data.
    Ex: {"id":5,"name":"","email":"","created_at":"2016-04-27 21:00:39","updated_at":"2016-04-27 21:00:39","first_name":"e","last_name":"sabitov","dob":"0000-00-00 00:00:00","gender":"male","fb_user_id":"1633910033509707","fb_auth_tok":"","photo":""}
    */
    public function get(Request $request){
        $id = $request->input("id");
        $fb_user_id = $request->input("fb_user_id");
        if((!isset($id) || $id === '') && (!isset($fb_user_id) || $fb_user_id === '')){
            $ret = array(
              "success"=>false,
              "msg"=>'The fb_user_id or the id parameter is required.'
            );
            die(json_encode($ret));
        }
        if((isset($id) && $id !== '')){
            $cur = User::where('id',$id)->first();
            if(is_null($cur)){
                $ret = array(
                  "success"=>false,
                  "msg"=>'The id that you provided was not found in the database.'
                );
                die(json_encode($ret));
            }
        }
        else if((isset($fb_user_id) && $fb_user_id !== '')){
            $cur = User::where('fb_user_id',$fb_user_id)->first();
            if(is_null($cur)){
                //create new user, make sure that all required fields are passed.
                $cur = new User;
                $cur->fb_user_id = $fb_user_id;
                $valid_fields = ['first_name','last_name','email','dob','gender','fb_auth_tok','photo','device_token'];
                $msg = array();
                foreach($valid_fields as $field){
                    $field_value = $request->input($field);
                    if($field == 'photo'){
                        $photo_image = $request->file('photo');
                        $photo_url = $request->input('photo');
                        if(isset($photo_image)){
                            $aws_controller = new AwsController;
                            $photo = $request->file('photo');
                            $uploaded_image_url = $aws_controller->upload_image($photo,'users');
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
                    else{
                        if($field == 'email'){
                            if(!isset($field_value) || $field_value == ''){
                                $ret = array(
                                  "success"=>false,
                                  "msg"=>'Email field must have a value when creating a new user'
                                );
                                die(json_encode($ret));
                            }
                            else{
                                $temp_email_check = User::where('email',$field_value)->first();
                                if(!is_null($temp_email_check)){
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
                $saved = $cur->save();
            }
        }
        $ret = array(
          "success"=>true,
          "user"=>$cur
        );
        die(json_encode($ret));
    }
    /*
    NAME: update_user
    DESCRIPTION: Updates the user table with data. This can be used to update as well as create new users. If id is
        found then it will update, otherwise it will create new.
    PARAMETERS: 
        id              - user table row id (editing existing user row)
        fb_user_id      - facebook user id. (if new, new record will be added to db, if existing it will update)
        name            - name
        first_name      - first_name string
        last_name       - last_name string
        email           - email string
        dob             - datetime format 'Y-m-d H:i:s'
        gender          - male or female string
        fb_auth_tok     - fb access token string
        photo           - photo can be a url string or a file upload object
        device_token    - iphone device token.
    IMPORTANT - user email must be uniqe. No 2 users can have the same email or blank email. We may want to change this if necessary. Device token can be passed in. Must be 64 characters. This function should register that device with aws by creating an endpoint and subscribing that endpoint to the main Fanvault app topic for general notifications.
    RETURNS: (str) ret - JSON object with status of whether success was true or false, as well as an explanation
        message explaining what was done.
    Ex: {"success":true,"msg":"Record created or updated successfully. first_name field was saved successfully. last_name field was saved successfully. email field was saved successfully.","user":{"id":63,"name":"Elliot Sabitov","email":"elliotsabitov@gmail.com","created_at":"2016-05-04 14:28:44","updated_at":"2016-05-10 18:51:20","first_name":"Elliot","last_name":"Sabitov","dob":"0000-00-00 00:00:00","gender":"male","fb_user_id":"770713659731171","fb_auth_tok":"","photo":"https:\/\/s3.amazonaws.com\/fanvaultapp\/users\/%7BAFD4879A-40E4-4F89-7FD4-26C0AFA9A7A6%7D.png"}}
    */
    public function update_user(Request $request){
        //users table has name, first_name, last_name, email, dob, gender, fb_user_id, fb_auth_tok.
        $fb_user_id = $request->input("fb_user_id");
        $id = $request->input("id");
        $new_user = false;
        if((isset($id) && $id !== '') || (isset($fb_user_id) && $fb_user_id !== '')){
            if(isset($id) && $id !== ''){   //this means we want to update the record because he just gave me my user row id.
                $cur = User::where('id',$id)->first();
                if(is_null($cur)){  //user not found
                    $ret = array(
                      "success"=>false,
                      "msg"=>'The id you provided does not exist'
                    );
                    die(json_encode($ret));
                }
            }
            elseif(isset($fb_user_id) && $fb_user_id !== ''){
                $cur = User::where('fb_user_id',$fb_user_id)->first();
                if(is_null($cur)){  //user does not exist. Add new.
                   $new_user = true;
                   $cur = new User;
                   $cur->fb_user_id = $fb_user_id;
                }
            }
            //now i have my cur which is the new or existing record.
            $valid_fields = ['name','first_name','last_name','email','dob','gender','fb_auth_tok','photo','device_token'];
            $msg = array();
            foreach($valid_fields as $field){
                $field_value = $request->input($field);
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
                                $aws_controller->delete_aws_image($aws_image_file_name,'users');   //nothing checking success for this
                                //lets just hope this deletes and works. Not sure what to do if doesnt.. maybe email someone? Maybe save in a different table..idk, im just going to ignore for now..
                            }
                        }
                    }
                    if(isset($photo_image)){
                        $photo = $request->file('photo');
                        $uploaded_image_url = $aws_controller->upload_image($photo,'users');
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
                else{
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
            $saved = $cur->save();
            if($saved){
                $user = User::where('id', $cur->id);
                $device_token = $request->input('device_token');
                if(isset($device_token) && strlen($device_token) == 64){
                    //add device token to the user devices table. And register with AWS.
                    $user_device_controller = new UserDeviceController;
                    $added_token = $user_device_controller->add_token($cur->id,$field_value);
                }
                $ret = array(
                  "success"=>true,
                  "msg"=>'Record created or updated successfully. ' . implode(' ',$msg),
                  'user'=>$user->first()
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
        else{
            $ret = array(
              "success"=>false,
              "msg"=>'Facebook user id or user row id was not recieved successfully.'
            );
            die(json_encode($ret));
        }
    }   
    /*
    NAME: delete_user
    DESCRIPTION: Deletes the row by user id or fb_user_id depending on which parameter is passed. If both are passed, it will 
        look for a match for both.
    PARAMETERS: 
        id              - user table row id (editing existing user row)
        fb_user_id      - facebook user id. (if new, new record will be added to db, if existing it will update)
    RETURNS: (str) ret - JSON object with status of whether success was true or false, as well as an explanation
        message explaining what was done.
    Ex: {"success":false,"msg":"User has been deleted."}
    */
    public function delete_user(Request $request){
        $id = $request->input("id");
        $fb_user_id = $request->input("fb_user_id");
        if((isset($id) && $id !== '') && (isset($fb_user_id) && $fb_user_id !== '')){
            $cur = User::where('id',$id)->where('fb_user_id',$fb_user_id)->first();
        }
        elseif(isset($id) && $id !== ''){
            $cur = User::where('id',$id)->first();
        }
        elseif(isset($fb_user_id) && $fb_user_id !== ''){
            $cur = User::where('fb_user_id',$fb_user_id)->first();
        }
        else{
            $ret = array(
              "success"=>false,
              "msg"=>'The fb_user_id or id is required.'
            );
            die(json_encode($ret));
        }
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>'The fb_user_id, id, or the combination of both, that you provided was not found in the database.'
            );
        }
        else{
            $deleted = $cur->delete();
            if($deleted){
                $ret = array(
                  "success"=>false,
                  "msg"=>'User has been deleted.'
                );
            }
            else{
                $ret = array(
                  "success"=>false,
                  "msg"=>'There was a problem deleting the record.'
                );
            }
        }
        die(json_encode($ret));
    }
    /*
    NAME: get_social_feed
    DESCRIPTION: Gets the social feed, which is a list of current upcoming games, and user(s) tied to the game, if there are any.
    PARAMETERS: 
        id              - (Required) user table row id
        lat             - user latitude, if not provided then defaults will be usedd for the user_id
        lng             - user longitude, if not given then it will use default that it can find for the user_id
        page            - (optional, defaults to 0) Pagination. The function will return 15 game objects at a time, to allow user to keep scrolling, we can get page 2, 3, etc, and it will get more and more objects until there is no more games in the future to show.
    RETURNS: (str) ret - JSON object containing an array of games, each game will have a user(s) object tied to if any are found.
    Ex: {"success":true,"games":[{"id":69,"sr_league_id":"2fa448bc-fc17-4d3d-be03-e60e080fdc26","league_name":"Major League Baseball","league_alias":"MLB","sr_season_id":"565de4be-dc80-4849-a7e1-54bc79156cc8","season_year":"2016","season_type":"REG","sr_game_id":"0867dee5-a250-410b-9b28-42218785ca5c","status":"scheduled","coverage":"full","game_number":"1","day_night":"N","scheduled":"2016-05-06 23:05:00","home_team_id":"5","away_team_id":"4","venue_id":"38","created_at":"2016-05-03 17:58:18","updated_at":"2016-05-03 17:58:18","home_team_runs":"0","away_team_runs":"0"}..]}
    */
    public function get_social_feed(Request $request){
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
        $page_num = 0;
        $page = $request->input("page");
        if(isset($page) || $page !== ''){
            if($page !== 'all'){
                $page_num = intval($page);
            }
        }
        //now that I have a user object, I would get that users app friends..
        $friends_one = Friend::where(['user_1'=>$id])->pluck('user_2')->all();
        $friends_two = Friend::where(['user_2'=>$id])->pluck('user_1')->all();
        $all_friend_ids = array_merge($friends_one,$friends_two);
        //$all_friend_objs = User::whereIn('id', $all_friend_ids)->get();
        $all_friend_ids[] = $id;
        //At this point lets get all upcoming games and for each game lets attach the home/away teams and venues
        //as well as the attendees.


        //now we need to find all stadiums that are in the users area.
        //we will periodically update the users_nearby_stadiums table every so often with updated stadiums
        //first lets check if the latest user_nearby_stadium is older then 1 hour.
        $nearby_stadiums = UserNearbyStadium::where('user_id',$id)->orderBy('updated_at','DESC')->get();
        $needs_update = false;
        if($nearby_stadiums->count() == 0){
            $needs_update = true;
        }
        else{   //needed to do this otherwise was getting cannot read property of null error when it was in same if statement
            if($nearby_stadiums->first()->updated_at < gmdate('Y-m-d h:i:s',strtotime('-1 hour'))){
                $needs_update = true;
            }
        }
        if($needs_update){
            //means either user has no nearby stadiums or they are outdated,
            //update db with nearby user stadiums
            $lat = $request->input("lat");
            $lng = $request->input("lng");
            if(isset($lat) &&  isset($lng)){
                $radius_in_miles = 50;
                $where_str = "acos(sin(venues.lat * 0.0175) * sin({$lat} * 0.0175) + cos(venues.lat * 0.0175) * cos({$lat} * 0.0175) * cos(({$lng} * 0.0175) - (venues.lng * 0.0175))) * 3959 <= {$radius_in_miles}";
                $venues = Venue::whereRaw($where_str)->pluck('id');
                $data = array();
                $timestamp = gmdate('Y-m-d h:i:s',strtotime('now'));
                foreach($venues as $venue_id){
                    $data[] = array(
                        'user_id'=>$id,
                        'venue_id'=>$venue_id,
                        'created_at'=>$timestamp,
                        'updated_at'=>$timestamp
                    );
                }
                //delete existing nearby stadiums for user
                $deleted = UserNearbyStadium::where('user_id',$id)->delete();
                //insert new ones for the new given location
                $nearby_stadiums = UserNearbyStadium::insert($data);
                $nearby_stadiums = UserNearbyStadium::where('user_id',$id)->orderBy('updated_at','DESC')->get();
            } 
        }
        $now = gmdate('Y-m-d H:i:s',strtotime('+4 hours'));
        $upcoming_games = 
            Game::with('home_team','away_team','venue');
        if($nearby_stadiums->count()){
            $upcoming_games = $upcoming_games->whereIn('venue_id',$nearby_stadiums->pluck('venue_id')->toArray());
        }
        $upcoming_games = $upcoming_games
            ->where('scheduled','>=',$now)
            ->take(5)
            ->skip($page_num*15)
            ->orderBy('scheduled', 'asc')
            ->get()
            ->toArray();
        foreach($upcoming_games as &$game){
            $game['venue']['venue_image'] = $game['venue']['venue_image']['url'];
        }
        //I have to get the new "attending the game" items and include them in.
        //all of the "attending" records as well as all of the photos uploaded records are all in the past.
        
        if($request->input("mytest") == 'mytest'){
            $users_attending_games = 
                GamesAttendee::with('game_object','user_object')
                ->whereIn('user_id',$all_friend_ids)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->skip($page_num*15)
                ->get()->toArray();
            foreach($users_attending_games as &$one_game){
                $one_game['game_object']['home_team'] = $one_game['game_object']['home_team_no_players'];
                $one_game['game_object']['away_team'] = $one_game['game_object']['away_team_no_players'];
                unset($one_game['game_object']['home_team_no_players']);
                unset($one_game['game_object']['away_team_no_players']);
            }            
            //now lets get the photos uploaded.
            $users_game_images = UserGameImage::whereIn('user_id',$all_friend_ids)
                ->groupBy('game_id','user_id')
                ->orderBy('created_at','desc')
                ->take(10)
                ->skip($page_num*15)
                ->get();
            //now lets get the rest of the photos for each user game combo and add them to photo array
            $users_game_images = $users_game_images->toArray();
            foreach($users_game_images as &$one_user_game_image){
                if(!is_array($one_user_game_image['photo'])){
                    $one_user_game_image['photo'] = array($one_user_game_image['photo']);
                }
                $additional_images = UserGameImage::where([
                    'user_id'=>$one_user_game_image['user_id'],
                    'game_id'=>$one_user_game_image['game_id']
                ])
                ->where('id','!=',$one_user_game_image['id'])
                ->orderBy('created_at','desc')
                ->pluck('photo')->toArray();
                foreach($additional_images as $one_additional_image){
                    $one_user_game_image['photo'][] = $one_additional_image;
                }
                unset($one_user_game_image['id']);
                unset($one_user_game_image['caption']);
                unset($one_user_game_image['private']);
            }
            $return_array = array();
            while(count($users_game_images) > 0 || count($users_attending_games) > 0){
                if(count($users_game_images) == 0){
                    $return_array[] = array('game_attendee'=>array_shift($users_attending_games));
                }
                else if(count($users_attending_games) == 0){
                    $return_array[] = array('game_photo'=>array_shift($users_game_images));
                }
                else if($users_game_images[0]['created_at'] >= $users_attending_games[0]['created_at']){
                    $return_array[] = array('game_photo'=>array_shift($users_game_images));
                }
                else{
                    $return_array[] = array('game_attendee'=>array_shift($users_attending_games));
                }
            }
            //now sprinke in nearby games every 4 items
            // foreach($upcoming_games as &$upcoming_game){
            //     $upcoming_game['home_team'] = $upcoming_game['home_team_no_players'];
            //     $upcoming_game['away_team'] = $upcoming_game['away_team_no_players'];
            //     unset($upcoming_game['home_team_no_players']);
            //     unset($upcoming_game['away_team_no_players']);
            // }
            $c = 0;
            $ret = array();
            if(count($return_array)){
                while(count($return_array)){
                    $ret[] = array_shift($return_array);
                    $c++;
                    if($c == 4){
                        $ret[] = array('game_upcoming'=>array_shift($upcoming_games));
                        $c = 0;
                    }
                }
            }
            else{
                $ret = $upcoming_games;
            }
            die(json_encode($ret));
        }
        




        $ret = array(
          "success"=>true,
          "games"=>$upcoming_games
        );
        die(json_encode($ret));

    }
    /*
    Name: get_game_for_user
    Description: Returns game object for given game_id and user_id. Will include ticket info and attendees info.
    Parameters: 
        user_id      - existing user id
        game_id      - existing game id
    Returns: (str) ret - JSON object containing the data.
    */
    public function get_game_for_user(Request $request){
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
        $friends_one = Friend::where(['user_1'=>$user_id])->pluck('user_2')->all();
        $friends_two = Friend::where(['user_2'=>$user_id])->pluck('user_1')->all();
        $all_friend_ids = array_merge($friends_one,$friends_two);
        //$all_friend_objs = User::whereIn('id', $all_friend_ids)->get();
        $all_friend_ids[] = $user_id;
        $game_obj = Game
        ::with('home_team','away_team','home_team_scores','away_team_scores')
        ->with(['home_team_lineup_pitchers'=>function($q) use ($game_id){
            return $q->where('game_id',$game_id);
        }])
        ->with(['home_team_lineup_hitters'=>function($q) use ($game_id){
            return $q->where('game_id',$game_id);
        }])
        ->with(['away_team_lineup_pitchers'=>function($q) use ($game_id){
            return $q->where('game_id',$game_id);
        }])
        ->with(['away_team_lineup_hitters'=>function($q) use ($game_id){
            return $q->where('game_id',$game_id);
        }])
        ->with(['home_team_game_stats'=>function($q) use ($game_id){
            return $q->where('game_id',$game_id);
        }])
        ->with(['away_team_game_stats'=>function($q) use ($game_id){
            return $q->where('game_id',$game_id);
        }])
        ->with('venue','officials','tailgates')
        ->with(['friend_attendees'=>function($q) use ($all_friend_ids){
            return $q->whereIn('user_id',$all_friend_ids);
        }])
        ->with(['attendees'=>function($q) use ($all_friend_ids){
            return $q->whereNotIn('user_id',$all_friend_ids);
        }])
        ->with(['ticket'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->with(['user_game_images'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->with(['user_game_crew_members'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->with(['invited_friends'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->where('id',$game_id)->first();
        $ret = array(
          "success"=>true,
          "game"=>$game_obj
        );
        die(json_encode($ret));       
    }
    /*
    Name: get_game_for_user
    Description: Returns game object for given game_id and user_id. Will include ticket info and attendees info.
    Parameters: 
        user_id      - existing user id
        game_id      - existing game id
    Returns: (str) ret - JSON object containing the data.
    */
    public function get_game_for_user_2(Request $request){
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
        $friends_one = Friend::where(['user_1'=>$user_id])->pluck('user_2')->all();
        $friends_two = Friend::where(['user_2'=>$user_id])->pluck('user_1')->all();
        $all_friend_ids = array_merge($friends_one,$friends_two);
        //$all_friend_objs = User::whereIn('id', $all_friend_ids)->get();
        $all_friend_ids[] = $user_id;
        $game_obj = Game::with('home_team','away_team','home_team_scores','away_team_scores')
        ->with('venue','tailgates')
        ->with(['friend_attendees'=>function($q) use ($all_friend_ids){
            return $q->whereIn('user_id',$all_friend_ids);
        }])
        ->with(['attendees'=>function($q) use ($all_friend_ids){
            return $q->whereNotIn('user_id',$all_friend_ids);
        }])
        ->with(['ticket'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->with(['user_game_images'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->with(['user_game_crew_members'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->with(['invited_friends'=>function($q) use ($user_id){
            return $q->where('user_id',$user_id);
        }])
        ->where('id',$game_id)->first();
        //now lets get the pitchers and hitters for this game for home_team and away_team
        $game_obj = $game_obj->toArray();
        $home_or_away_arr = array('home','away');
        foreach($home_or_away_arr as $home_or_away){
            $team[$home_or_away] = Team::where('id',$game_obj[$home_or_away.'_team_id'])->first();
            $team_lineup[$home_or_away] = GamesLineup::where([
                'game_id'=>$game_obj['id'],
                'team_id'=>$team[$home_or_away]->id
            ]);
            if($team_lineup[$home_or_away]->count() == 0){
                //use roster
                $team_pitchers[$home_or_away] = Player::with('headshot')->where('team_id',$team[$home_or_away]->id)->where('position','P')->get()->toArray();
                $team_hitters[$home_or_away] = Player::with('headshot')->where('team_id',$team[$home_or_away]->id)->where('position','!=','P')->get()->toArray();
                foreach($team_pitchers[$home_or_away] as &$one_player){
                    $one_player['headshot'] = $one_player['headshot']['url'];
                }
                foreach($team_hitters[$home_or_away] as &$one_player){
                    $one_player['headshot'] = $one_player['headshot']['url'];
                }
            }
            else{
                $team_players = Player::with('headshot')
                    ->with(['player_lineup_position'=>function($q) use ($game_obj){
                        return $q->where('game_id',$game_obj['id']);
                    }])
                    ->with(['player_score'=>function($q) use ($game_obj){
                        return $q->where('game_id',$game_obj['id']);
                    }])
                    ->whereIn('id',$team_lineup[$home_or_away]->pluck('player_id')->toArray())
                    ->get()
                    ->toArray();
                $team_pitchers[$home_or_away] = array();
                $team_hitters[$home_or_away] = array();
                foreach($team_players as &$one_player){
                    $one_player['position'] = $one_player['player_lineup_position']['position'];
                    unset($one_player['player_lineup_position']);
                    $one_player['headshot'] = $one_player['headshot']['url'];
                    if($one_player['position'] == 'P'){
                        $team_pitchers[$home_or_away][] = $one_player;
                    }
                    else{
                        $team_hitters[$home_or_away][] = $one_player;
                    }
                }
            }
            $game_obj[$home_or_away.'_team']['pitchers'] = $team_pitchers[$home_or_away];
            $game_obj[$home_or_away.'_team']['hitters'] = $team_hitters[$home_or_away];
        }
        //lets connect the officials as well
        $officials = Official::whereIn('id',GamesOfficial::where('game_id',$game_obj['id'])->pluck('official_id')->toArray())->select('first_name','last_name','experience','assignment')->get()->toArray();
        $game_obj['officials'] = $officials;
        //finally lets format venue image, and move home_team_scores and away_team_scores under home_team and away_team
        //if these scores exist
        $game_obj['venue']['venue_image'] = $game_obj['venue']['venue_image']['url'];
        $game_obj['home_team']['home_team_scores'] = $game_obj['home_team_scores'];
        unset($game_obj['home_team_scores']);
        $game_obj['away_team']['away_team_scores'] = $game_obj['away_team_scores'];
        unset($game_obj['away_team_scores']);
        $ret = array(
          "success"=>true,
          "game"=>$game_obj
        );
        die(json_encode($ret));       
    }
    /*
    Name: search
    Description: Takes in a search key and return a JSON object containing users, stadiums, and tailgates, that match the search.
    Parameters: 
        search      - search string must be at least 3 characters long
    Returns: (str) ret - JSON object containing the data.
    */
    public function search(Request $request){
        $search = $request->input("search");
        if(!isset($search) || $search === '' || strlen($search) < 3){
            $ret = array(
              "success"=>false,
              "msg"=>'The search field was not recieved or did not have enough characters (minimum 3).'
            );
            die(json_encode($ret));
        }
        //okay, we got the search, now lets get all users that match the search
        $users = User
                ::where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->get();
        //we want to also search matching teams, if any teams match then show venue for that team.
        $team_venue_ids = Team
                ::where('name','like', "%{$search}%")
                ->select('venue_id')
                ->get()->toArray();
        $venues = Venue::with('venue_image','upcoming_games_for_venue')
                ->where('name', 'like', "%{$search}%")
                ->orWhere('market', 'like', "%{$search}%")
                ->orWhere('surface', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhereIn('id',$team_venue_ids)
                ->get();
        $tailgates = Tailgate::with('tags')
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->get();
        $ret = array(
          "success"=>true,
          "users"=>$users,
          "stadiums"=>$venues,
          "tailgates"=>$tailgates
        );
        die(json_encode($ret));
    }

    /*
    Name: search_type_ahead
    Description: Takes in a search key and return a JSON array containing strings that match the search.
    Parameters: 
        search      - search string must be at least 2 characters long
    Returns: (str) ret - JSON object containing the data.
    */
     public function search_type_ahead(Request $request){
        $search = $request->input("search");
        if(!isset($search) || $search === '' || strlen($search) < 2){
            $ret = array(
              "success"=>false,
              "msg"=>'The search field was not recieved or did not have enough characters (minimum 2).'
            );
            die(json_encode($ret));
        }
        $users = User
                ::where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->select('id','first_name','last_name')
                ->get();
        $teams = Team
                ::where('name','like', "%{$search}%")
                ->select('id','venue_id','name')
                ->get();
        $venues = Venue
                ::where('name', 'like', "%{$search}%")
                ->orWhere('market', 'like', "%{$search}%")
                ->orWhere('surface', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->select('id','name','market','surface','address','city')
                ->get();
        $tailgates = Tailgate
                ::where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->select('id','title','description')
                ->get();
        $str_arr = array();
        foreach($users as $user){
            if(stripos($user->first_name, $search) !== false || stripos($user->last_name, $search) !== false){
                $str_arr[] = $user->first_name . ' ' . $user->last_name;
            }
        }
        foreach($venues as $venue){
            if(stripos($venue->name, $search) !== false){
                $str_arr[] = $venue->name;
            }
            if(stripos($venue->market, $search) !== false){
                $str_arr[] = $venue->market;
            }
            if(stripos($venue->surface, $search) !== false){
                $str_arr[] = $venue->surface;
            }
            if(stripos($venue->address, $search) !== false){
                $str_arr[] = $venue->address;
            }
            if(stripos($venue->city, $search) !== false){
                $str_arr[] = $venue->city;
            }
        }
        foreach($teams as $team){
            if(stripos($team->name, $search) !== false){
                $str_arr[] = $team->name;
            }
        }
        foreach($tailgates as $tailgate){
            if(stripos($tailgate->title, $search) !== false){
                $str_arr[] = $tailgate->title;
            }
            if(stripos($tailgate->description, $search) !== false){
                $str_pos = stripos($tailgate->description, $search);
                $start = strrpos($tailgate->description,' ',-strlen($tailgate->description) + $str_pos)+1;
                $end = strpos($tailgate->description,' ',$str_pos);
                $temp_str = substr($tailgate->description,$start,$end-$start);
                $str_arr[] = $temp_str;
            }
        }
        $ret = array(
          "success"=>true,
          "matches"=>array_slice(array_unique($str_arr),0,15)
        );
        die(json_encode($ret));
    }
}
