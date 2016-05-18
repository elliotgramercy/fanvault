<?php

//IMPORTANT STILL DID NOT MAKE LOG OUT FUNCTIONALITY.

namespace App\Http\Controllers;

require base_path("vendor/autoload.php");
//require base_path("vendor/facebook/php-sdk-v4/src/Facebook/PersistentData/FacebookPersistentDataHandler.php");

use Illuminate\Http\Request;

use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\GraphObject;
//use Facebook\PersistentData;

use App\User;

function platformSlashes($path) {
    if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}

class SocialAuthController extends Controller
{
    public $session = null;
    public $user = null;

    /*
    Name: redirectToProvider
    Description: Prints out a link that takes you to fb login.
    Parameters: N/A
    Returns: N/A. Forwards you to facebook, and facebook redirects to the callback
    */
    public function redirectToProvider(){
      ?>
      <!DOCTYPE html>
      <html>
          <head>
              <title>Laravel</title>       
          </head>
          <body>
              <div class="container">
                  <?php
                  //return Socialite::driver("facebook")->redirect();
                  $fb = new Facebook([/* . . . */]);

                  $helper = $fb->getRedirectLoginHelper();
                  $permissions = ["public_profile", "email","user_birthday","user_friends","user_events","user_photos","user_videos"]; // optional
                  $loginUrl = $helper->getLoginUrl("http://fanvault.gramercy.tech/auth/fb/returnFromProvider", $permissions);
                  echo "<a href='{$loginUrl}'>Log in with Facebook!</a>";
                  ?>
              </div>
          </body>
      </html>
      <?php
    }

    /*
    Name: returnFromProvider
    Description: User is redirected back from facebook. We store facebook_access_token 
         in laravel and php sessions.
    Parameters: N/A
    Returns: N/A. Stores fb token in session.
    */
    public function returnFromProvider(Request $request){
      $fb = new Facebook([/* . . . */]);
      $helper = $fb->getRedirectLoginHelper();
      try {
        $accessToken = $helper->getAccessToken();
      } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo "Graph returned an error: " . $e->getMessage();
        exit;
      } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo "Facebook SDK returned an error: " . $e->getMessage();
        exit;
      }
      if (isset($accessToken)) {
        //now we store the access token in session.
        $request->session()->set("facebook_access_token",$accessToken);  //store in laravel session
        $_SESSION["facebook_access_token"] = (string) $accessToken; //store in php session
        //check if this user is already 
        //if user does not exist, then create new user in database, and store the facebook user id that was returned.
        $response = $this->handleRequest($accessToken,"/me?fields=id,name,first_name,last_name,birthday,gender,email");
        $graphObject = $response->getGraphUser();
        //now that we have curuser info from facebook, we can check the facebook_user_id against the database to see if user exists
        $cur_user_fb_id = $graphObject->getId();
        $cur_user = User::where('fb_user_id',$cur_user_fb_id);
        if($cur_user->count() > 0){  //user exists. Update.
          $cur_id = $cur_user->first();
          $cur_id = $cur_id->id;
          $cur = User::find($cur_id);
          $cur->fb_auth_tok = $accessToken;
          $saved = $cur->save();
        }
        else{   //user does not exist. Add new.
          $cur = new User;
          $cur->fb_user_id = $cur_user_fb_id;
          $cur->name = $graphObject->getName();
          $cur->first_name = $graphObject->getFirstName();
          $cur->last_name = $graphObject->getLastName();
          $cur->dob = $graphObject->getBirthday()->format('Y-m-d H:i:s');
          $cur->gender = $graphObject->getProperty('gender');
          $cur->email = $graphObject->getEmail();
          $cur->fb_auth_tok = $accessToken;
          $saved = $cur->save();
        }
        //if($saved){
        //}   
      }
      echo "great your logged in";
    }
    
    /*
    Name: handleRequest
    Description: Helper function. Handles the executions of the facebook requests.
    Parameters: (str) accessToken
                (str) endpoint - string for the facebook call [ex: me/friends]
    Returns: (Facebook/FacebookResponce) response - Response object from FB.
    */
    public function handleRequest($accessToken,$endpoint){
      try {
        $fb = new Facebook([/* . . . */]);
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get($endpoint, $accessToken);
      } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo "Graph returned an error: " . $e->getMessage();
        exit;
      } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo "Facebook SDK returned an error: " . $e->getMessage();
        exit;
      }
      return $response ?: null;
    }

    /*
    Name: getUser
    Description: Get user info
    Parameters: (url variable) user_id - can be passed in for non session users
    Returns: (str) ret - JSON object containing user info.
        Ex: {"id":"770713659731171","first_name":"Elliot","last_name":"Sabitov","dob":"1989-11-10 00:00:00","gender":"male","email":"elliotsabitov@gmail.com"}
    */
    public function getUser(Request $request){
      $endpoint_str = "me";
      $user_id = $request->input("user_id");
      if(isset($user_id)){
        $endpoint_str = $request->input("user_id");
      }
      $accessToken = $request->session()->get("facebook_access_token");
      if (isset($accessToken)) {
        // Now you can redirect to another page and use the
        // access token from $_SESSION["facebook_access_token"]
        $response = $this->handleRequest($accessToken,"/{$endpoint_str}?fields=id,name,first_name,last_name,birthday,gender,email");
        $graphObject = $response->getGraphUser();
        $ret = array(
          "id"=>$graphObject->getId(),
          "first_name"=>$graphObject->getFirstName(),
          "last_name"=>$graphObject->getLastName(),
          "dob"=>$graphObject->getBirthday()->format('Y-m-d H:i:s'),
          "gender"=>$graphObject->getProperty('gender'),
          'email'=>$graphObject->getEmail()
        );
        die(json_encode($ret));
      }
    }

    /*
    Name: getUserPhoto
    Description: Get user Photo
    Parameters: (url variable) user_id - can be passed in for non session users
    Returns: (str) ret - string url link for image location. 
        Ex: http://scontent.xx.fbcdn.net/hprofile-xfa1/....
    */
    public function getUserPhoto(Request $request){
      $user_id = $request->input("user_id");
      $endpoint_str = "me";
      if(isset($user_id)){
        $endpoint_str = $request->input("user_id");
      }
      $accessToken = $request->session()->get("facebook_access_token");
      if (isset($accessToken)) {
        // Now you can redirect to another page and use the
        // access token from $_SESSION["facebook_access_token"]
        $response = $this->handleRequest($accessToken,"/".$endpoint_str."/picture?redirect=false");
        $graphObject = $response->getDecodedBody();
        $picture = $graphObject["data"]["url"];
        die(json_encode($picture,JSON_UNESCAPED_SLASHES));
      }
    }

    /*
    Name: getNonAppFriends
    Description: Get user"s friends who DO NOT have the app.
    Parameters: (url variable) user_id - can be passed in for non session users
    Returns: (str) ret - sJSON object containing array of friends with id, name and img
        for each friend.
        Ex: [{"id":"UNIQUEID","name":"Users Name","src":"http:\/\/scontent...."},{"id":"UNIQUEID","name":"User Name","src":"http:\/\/scontent.xx.fbcdn.net...."}]
    */
    public function getNonAppFriends(Request $request){
      $user_id = $request->input("user_id");
      $endpoint_str = "me";
      if(isset($user_id)){
        $endpoint_str = $request->input("user_id");
      }
      $accessToken = $request->session()->get("facebook_access_token");
      if (isset($accessToken)) {
        $response = $this->handleRequest($accessToken,"/".$endpoint_str."/invitable_friends");
        $graphObject = $response->getGraphEdge();
        $items = $graphObject->all();
        $ret = array();
        foreach($items as $item){
          $ret[] = array(
            "id"=>$item->getField("id"),
            "name"=>$item->getField("name"),
            "src"=>$item->getField("picture")->getField("url")
          );
        }
        die(json_encode($ret));
      }   
    }

    /*
    Name: getAppFriends
    Description: Get user"s friends who DO have the app. Similar to getNonAppFriends
    Parameters: (url variable) user_id - can be passed in for non session users
    Returns: (str) ret - JSON object containing array of friends with id, name and img
        for each friend.
        Ex: [{"id":"UNIQUEID","name":"Users Name","src":"http:\/\/scontent...."},{"id":"UNIQUEID","name":"User Name","src":"http:\/\/scontent.xx.fbcdn.net...."}]
    */
    public function getAppFriends(Request $request){
      $user_id = $request->input("user_id");
      $endpoint_str = "me";
      if(isset($user_id)){
        $endpoint_str = $request->input("user_id");
      }
      $accessToken = $request->session()->get("facebook_access_token");
      if (isset($accessToken)) {
        // Logged in!
        $response = $this->handleRequest($accessToken,"/".$endpoint_str."/friends");
        $graphObject = $response->getGraphEdge();
        $items = $graphObject->all();
        $ret = array();
        foreach($items as $item){
          $ret[] = array(
            "id"=>$item->getField("id"),
            "name"=>$item->getField("name")
          );
        }
        die(json_encode($ret));
      }   
    }

    /*
    Name: getUserEvents
    Description: Gets all events that the current user is tied to.
    Parameters: (url variable) user_id - can be passed in for non session users
    Returns: (str) ret - JSON object containing array of events with info for each event.
        Ex: [
              {
              "event_id":"id",
              "name":"name",
              "start_time":"start_time",
              "end_time":"end_time",
              "rsvp_status":"rsvp_status",
              "place":{
                  "place_id":"id",
                  "name":"name",
                  "street":"street",
                  "city":"city",
                  "state":"state",
                  "zip":"zip",
                  "lat":"lat",
                  "lng":"lng",
                }
              }...
            ]
    */
    public function getUserEvents(Request $request){
      $user_id = $request->input("user_id");
      $endpoint_str = "me";
      if(isset($user_id)){
        $endpoint_str = $request->input("user_id");
      }
      $accessToken = $request->session()->get("facebook_access_token");
      if (isset($accessToken)) {
        // Now you can redirect to another page and use the
        // access token from $_SESSION["facebook_access_token"]
        
        $response = $this->handleRequest($accessToken,"/".$endpoint_str."/events");
        $graphObject = $response->getGraphEdge();
        $items = $graphObject->all();
        $ret = array();
        foreach($items as $item){
          $place = $item->getField("place");
          $place_location = $place->getField("location");
          $temp_event_place = array();
          if(null !== $place->getField("id")){$temp_event_place["place_id"] = $place->getField("id");}
          if(null !== $place->getField("name")){$temp_event_place["name"] = $place->getField("name");}
          if(null !== $place->getField("street")){$temp_event_place["street"] = $place->getField("street");}
          if(null !== $place->getField("city")){$temp_event_place["city"] = $place->getField("city");}
          if(null !== $place->getField("state")){$temp_event_place["state"] = $place->getField("state");}
          if(null !== $place->getField("zip")){$temp_event_place["zip"] = $place->getField("zip");}
          if(null !== $place->getField("lat")){$temp_event_place["lat"] = $place->getField("lat");}
          if(null !== $place->getField("lng")){$temp_event_place["lng"] = $place->getField("lng");}
          $ret[] = array(
            "event_id"=>$item->getField("id"),
            "name"=>$item->getField("name"),
            "start_time"=>$item->getField("start_time"),
            "end_time"=>$item->getField("end_time"),
            "rsvp_status"=>$item->getField("rsvp_status"),
            "place"=>$temp_event_place
          );
        }
        die(json_encode($ret));
      }
    }    

    /*
    Name: getFriendsEvents
    Description: Goes through all friends who have the app, and puts together one big JSON
        array of all aggregated events.
    Parameters: (url variable) user_id - can be passed in for non session users
    Returns: (str) ret - JSON object containing array of events with info for each event.
        Ex: [
              {
                "friend_id"=>"id",
                "events"=>[
                  {
                  "event_id":"id",
                  "name":"name",
                  "start_time":"start_time",
                  "end_time":"end_time",
                  "rsvp_status":"rsvp_status",
                  "place":{
                      "place_id":"id",
                      "name":"name",
                      "street":"street",
                      "city":"city",
                      "state":"state",
                      "zip":"zip",
                      "lat":"lat",
                      "lng":"lng",
                    }
                  }...
                ]
              }...
            ]
            
    */
    public function getFriendsEvents(Request $request){
      $user_id = $request->input("user_id");
      $endpoint_str = "me";
      if(isset($user_id)){
        $endpoint_str = $request->input("user_id");
      }
      $accessToken = $request->session()->get("facebook_access_token");
      if (isset($accessToken)) {
        
        $response = $this->handleRequest($accessToken,"me?fields=friends.fields(events)");
        $graphObject = $response->getDecodedBody();
        $events_per_friends = $graphObject["friends"]["data"];
        $ret = array();
        foreach($events_per_friends as $events_one_friend){
          $temp[] = array(
              "friend_id"=>$events_one_friend["id"]
          );
          $temp_events = array();
          foreach($events_one_friend["events"]["data"] as $one_event){
            $location = array();
            if(isset($one_event["place"])){
              $location = array("location"=>$one_event["place"]["location"]);
            }
            $temp_events[] = array(
              "event_id"=>$one_event["id"],
              "name"=>$one_event["name"],
              "start_time"=>isset($one_event["start_time"]) ? $one_event["start_time"] : "",
              "end_time"=>isset($one_event["end_time"]) ? $one_event["end_time"] : "",
              "rsvp_status"=>$one_event["rsvp_status"],
              $location
            );            
          }
          $temp["events"] = $temp_events;
          $ret[$events_one_friend["id"]] = $temp;
        }
        die(json_encode($ret));
      }
    }
}