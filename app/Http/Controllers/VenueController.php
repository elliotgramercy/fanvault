<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Venue;
use App\Image;

class VenueController extends Controller
{
	/*
    Name: getOneBy
    Description: Returns all venue records in database that match the ID, the name, or the Sports Radar venue id (sr_venue_id). Now returns venue images as well.
    Parameters: (required url variable) field - can either be 'name','id' or 'sr_venue_id'.
    			(required url variable) value - name or id value
    Returns: (str) ret - JSON object containing the data.
    Ex: [{"id":1,"sr_venue_id":"0ab45d79-5475-4308-9f94-74b0c185ee6f","name":"PETCO Park","market":"San Diego","capacity":"42302","surface":"grass","address":"100 Park Blvd.","city":"San Diego","state":"CA","zip":"92101","country":"USA","distances":"{\"lf\":334,\"lcf\":367,\"cf\":396,\"rcf\":378,\"rf\":322,\"mlf\":351,\"mlcf\":402,\"mrcf\":403,\"mrf\":351}","created_at":"2016-05-03 17:58:08","updated_at":"2016-05-03 17:58:08","images":[{"id":7,"sr_image_id":"5d3d19d2-67a2-4233-b9c2-c97d7731d3e6","venue_id":"1","date_created":"2013-12-19 18:20:10","url":"http:\/\/api.sportradar.us\/mlb-images-p2\/usat\/venues\/5d3d19d2-67a2-4233-b9c2-c97d7731d3e6\/original.jpg?api_key=kxcghnkjdq7wcy4cw7tsvnm7","created_at":"2016-05-04 20:22:14","updated_at":"2016-05-04 20:22:14"}]}...]
	On Fail: If function fails, a message will be returned in JSON format:
		{"success":false,"msg":"This function requires 2 url parameters. The field or value url parameter was not recieved successfully"}
    */
    public function getOneBy(Request $request){
    	$field = $request->input("field");
    	$value = $request->input("value");
    	if(isset($field) && isset($value)){
    		if($field == 'id' || $field == 'name' || $field == 'sr_venue_id'){
    			$venue = Venue::where($field, $value)->with('venue_image')->get();
    			die(json_encode($venue));
    		}
    		else{
    			$ret = array(
	    			'success'=>false,
	    			'msg'=>'The field parameters must be id, name, or sr_venue_id'
    			);
    			die(json_encode($ret));
    		}
    	}
    	else{
    		$ret = array(
    			'success'=>false,
    			'msg'=>'This function requires 2 url parameters. The field or value url parameter was not recieved successfully'
			);
    		die(json_encode($ret));
    	}        
    }

    /*
    Name: getAll
    Description: Returns all venue records in database. Now returns venue images as well.
    Parameters: N/A
    Returns: (str) ret - JSON object containing all of the rows in venue table
    Ex: [{"id":1,"sr_venue_id":"0ab45d79-5475-4308-9f94-74b0c185ee6f","name":"PETCO Park","market":"San Diego","capacity":"42302","surface":"grass","address":"100 Park Blvd.","city":"San Diego","state":"CA","zip":"92101","country":"USA","distances":"{\"lf\":334,\"lcf\":367,\"cf\":396,\"rcf\":378,\"rf\":322,\"mlf\":351,\"mlcf\":402,\"mrcf\":403,\"mrf\":351}","created_at":"2016-05-03 17:58:08","updated_at":"2016-05-03 17:58:08","images":[{"id":7,"sr_image_id":"5d3d19d2-67a2-4233-b9c2-c97d7731d3e6","venue_id":"1","date_created":"2013-12-19 18:20:10","url":"http:\/\/api.sportradar.us\/mlb-images-p2\/usat\/venues\/5d3d19d2-67a2-4233-b9c2-c97d7731d3e6\/original.jpg?api_key=kxcghnkjdq7wcy4cw7tsvnm7","created_at":"2016-05-04 20:22:14","updated_at":"2016-05-04 20:22:14"}]}..]
    */
    public function getAll()
    {
        $venues = Venue::with('venue_image')->get();
        die(json_encode($venues));
    }
    
    /*
    Name: updateAll
    Description: Updates the database venue table with sports radar info.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of new rows created and rows updated.
    Ex: {"rows_updated":75,"rows_created":0}
    */
	public function updateAll(){
		$srapi = env('SPORTS_RADAR_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-p5/league/venues.json?api_key={$srapi}");
		$return = json_decode($return);
		$venues = $return->venues;
		$rows_updated = 0;
		$rows_created = 0;
		foreach($venues as $venue){
			$venue_sr_id = $venue->id;
			$existing = Venue::where('sr_venue_id', $venue_sr_id);
			if($existing->count() > 0){
				//get existing
				$venue_my_id = $existing->first();
				$venue_my_id = $venue_my_id->id;
				$curvenue = Venue::find($venue_my_id);
			}
			else{
				//create new
				$curvenue = new Venue;
			}
			if(isset($venue->id)){$curvenue->sr_venue_id = $venue->id;}
			if(isset($venue->name)){$curvenue->name = $venue->name;}
			if(isset($venue->market)){$curvenue->market = $venue->market;}
			if(isset($venue->capacity)){$curvenue->capacity = $venue->capacity;}
			if(isset($venue->surface)){$curvenue->surface = $venue->surface;}
			if(isset($venue->address)){$curvenue->address = $venue->address;}
			if(isset($venue->city)){$curvenue->city = $venue->city;}
			if(isset($venue->state)){$curvenue->state = $venue->state;}
			if(isset($venue->zip)){$curvenue->zip = $venue->zip;}
			if(isset($venue->country)){$curvenue->country = $venue->country;}
			if(isset($venue->distances)){$curvenue->distances = json_encode($venue->distances);}
			$saved = $curvenue->save();
			if($saved){
				$rows_updated ++;
			}
		}
		$ret = array(
			'rows_updated' => $rows_updated,
			'rows_created' => $rows_created
		);
		die(json_encode($ret));
	}
}
