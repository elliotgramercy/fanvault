<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Image;
use App\Venue;
use App\Team;
use App\ImagesPlayer;

use App\Http\Controllers\AwsController as AwsController;

class ImageController extends Controller
{
	/*
    Name: getVenueImages
    Description: Returns all images pertaining to the given venue id. Should always return just one, right now there is only 1
    	venue image per venue.
    Parameters: 
    	id 			- 	id of the venue.
    Returns: (str) ret - JSON with success true or false, and message. If success true, then URL will be provided.
    Ex: {"success":true,"url":"http:\/\/api.sportradar.us\/mlb-images-p2\/usat\/venues\/5d3d19d2-67a2-4233-b9c2-c97d7731d3e6\/original.jpg?api_key=kxcghnkjdq7wcy4cw7tsvnm7"}
    */
    public function getVenueImages(Request $request){
    	$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = Image::where(['venue_id'=>$id,'type'=>'venue'])->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $ret = array(
          "success"=>true,
          "url"=>$cur->url
        );
        die(json_encode($ret));
    }
    /*
    Name: updateVenueImages
    Description: Updates the database images table with the new venue images. Basically looks at what sports radar gives us,
    	if any new sports radar image asset ids are found, then it will insert in database. It does not overwrite existing ids.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of new rows created.
    IMPORTANT: In most cases this will return 0. I ran it the first time, and it got all 30, but from this point on, if the image 
    	already exists, and is not replaced with a new image in Sports Radar, this will just return 0.
    Ex: {"rows_created":30}
    */
	public function updateVenueImages(){
		$srapi = env('SPORTS_RADAR_IMAGES_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-images-p2/usat/venues/manifests/all_assets.xml?api_key={$srapi}");
		$return = json_decode(json_encode(simplexml_load_string($return, 'SimpleXMLElement', LIBXML_NOCDATA)),true);
		$assets = $return['asset'];
		$c = 0;
        $max_width = 1000;
        $max_height = 1000;
		foreach($assets as $asset){
			$id = $asset['@attributes']['id'];
			$date = gmdate('Y-m-d H:i:s',strtotime($asset['@attributes']['created']));
			$title = $asset['title'];
			if($title == 'O.co Coliseum'){
				$title = 'Oakland Coliseum';
			}
			elseif($title == 'Rangers Ballpark in Arlington'){
				$title = 'Globe Life Park in Arlington';
			}
			$venue = Venue::where('name',$title)->pluck('id')->first();
			$url = 'http://api.sportradar.us/mlb-images-p2/usat'.$asset['links']['link']['@attributes']['href'].'?api_key='.$srapi;
            //the sportsradar images are wayy too big so I am going to download them, resize them, then upload them
            //to our aws s3 account and then save that in the database.
            $aws_controller = new AwsController;
            /* Taking this out so that perhaps we will get a nice collection of images going.
            $existing = Image::where('sr_image_id',$id)->first();
            if(!is_null($existing)){
                if(isset($existing->url) && $existing->url !== ''){
                    $aws_controller->delete_aws_image($existing->url,'venues');   //nothing checking success
                }
            }
            */
            $uploaded_image_url = $aws_controller->upload_venue_image($url);
			//now that we have all the data we insert.
			$cur = new Image;
			$cur->sr_image_id = $id;
			$cur->venue_id = $venue;
			$cur->date_created = $date;
			$cur->url = $uploaded_image_url;
            $cur->type = 'venue';
			$saved = $cur->save();
			if($saved){
				$c++;
			}
		}
		$ret = array('rows_created'=>$c);
		die(json_encode($ret));
	}
    /*
    Name: updateActionImages
    Description: Updates the action images. Will query sports radar once a week and go through each day and query sports radar
        for all images for that day. It will loop through all images, if image has home team that has not been put in db yet,
        then it will update the db with the new home team image. This way every week, we should have new one image for each
        team in their home stadium. 
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of new rows created.
    Ex: {"rows_created":30}
    */
    public function updateActionImages(){
        $srapi = env('SPORTS_RADAR_LIVE_IMAGES_TRIAL_KEY');
        $c = 0;
        for($i=7;$i>=0;$i--){
            $date = gmdate('Y-m-d',strtotime("-{$i} days"));
            try{
                $return = file_get_contents("http://api.sportsdatallc.org/mlb-liveimages-t1/usat/news/{$date}/manifests/all_assets.xml?api_key={$srapi}");
            }catch(Exception $ex){
                continue;
            }
            $return = json_decode(json_encode(simplexml_load_string($return, 'SimpleXMLElement', LIBXML_NOCDATA)),true);
            $assets = $return['asset'];
            $num_of_teams = Team::count();
            foreach($assets as $asset){
                $id = $asset['@attributes']['id'];
                //right off the bat lets check that this asset is new and not already in database
                $existing_check = Image::where('sr_image_id',$id)->first();
                if(!is_null($existing_check)){
                    //if image already in db then just move on.
                    continue;
                }
                $created = gmdate('Y-m-d H:i:s',strtotime($asset['@attributes']['created']));
                $url = 'http://api.sportsdatallc.org/mlb-liveimages-t1/usat'.$asset['links']['link']['@attributes']['href'].'?api_key='.$srapi;
                $tags = $asset['tags']['tag'];
                $person_arr = array();
                $team_arr = array();
                $location = '';
                foreach($tags as $tag){
                    $tag_type = $tag['@attributes']['type'];
                    $tag_value = $tag['@attributes']['value'];
                    if($tag_type == 'location'){
                        $location = $tag_value;
                    }
                    elseif($tag_type == 'team'){
                        $team_arr[] = $tag_value;
                    }
                    elseif($tag_type == 'person'){
                        $person_arr[] = $tag_value;
                    }
                }
                //now I have my location so lets figure out which of the teams is the home team so that we can attach THAT
                //particular team to the image.
                if($location == 'O.co Coliseum'){
                    $location = 'Oakland Coliseum';
                }
                elseif($location == 'Rangers Ballpark in Arlington'){
                    $location = 'Globe Life Park in Arlington';
                }
                if($location == ''){
                    continue;
                }
                $venue = Venue::where('name',$location)->orWhere('name', 'like', '%'.$location.'%')->first();  //now we have the venue_id
                if(is_null($venue)){
                    //if stadium match not found then move on.
                    continue;
                }
                $venue_id = $venue->id;
                $start_date = gmdate('Y-m-d H:i:s',strtotime("-7 days"));
                $end_date = gmdate('Y-m-d H:i:s',strtotime("now"));
                $existing_image = Image::where('venue_id',$venue_id)->whereBetween('date_created',[$start_date,$end_date])->first();
                if(!is_null($existing_image)){
                    continue;
                }
                //if we get to this point that means its new image so put it into the database.
                $cur = new Image;
                $cur->sr_image_id = $id;
                $cur->venue_id = $venue_id;
                $cur->date_created = $created;
                $cur->url = $url;
                $cur->type = 'action';
                $saved = $cur->save();
                if($saved){
                    foreach($person_arr as $person){
                        $cur2 = new ImagesPlayer;
                        $cur2->image_id = $cur->id;
                        $cur2->player = $person;
                        $cur2->save();
                    }
                    $c++;
                }
                if($c == $num_of_teams){
                    $ret = array(
                      "success"=>true,
                      "rows_created"=>$c
                    );
                    die(json_encode($ret));
                }
            }
            sleep(1);
        } 
        $ret = array(
          "success"=>true,
          "rows_created"=>$c
        );
        die(json_encode($ret));       
    }
}
