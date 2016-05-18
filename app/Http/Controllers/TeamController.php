<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;
use App\Venue;
use App\Image;

use App\Http\Requests;

class TeamController extends Controller
{
	/*
    Name: getOneBy
    Description: Returns all team records in database that match the ID, the name, or the Sports Radar team id (sr_team_id).
    Parameters: (required url variable) field - can either be 'name','id' or 'sr_team_id'.
    			(required url variable) value - name or id value
    Returns: (str) ret - JSON object containing the data.
    Ex: [{"id":2,"sr_league_id":"2ea6efe7-2e21-4f29-80a2-0a24ad1f5f85","league_name":"American League","league_alias":"AL","sr_division_id":"1d74e8e9-7faf-4cdb-b613-3944fa5aa739","division_name":"East","division_alias":"E","name":"Blue Jays","market":"Toronto","abbr":"TOR","sr_team_id":"1d678440-b4b1-4954-9b39-70afb3ebbcfa","venue_id":"46","created_at":"2016-04-04 19:46:12","updated_at":"2016-04-04 19:46:12"}]
	On Fail: If function fails, a message will be returned in JSON format:
		{"success":false,"msg":"This function requires 2 url parameters. The field or value url parameter was not recieved successfully"}
    */
    public function getOneBy(Request $request){
    	$field = $request->input("field");
    	$value = $request->input("value");
    	if(isset($field) && isset($value)){
    		if($field == 'id' || $field == 'name' || $field == 'sr_team_id'){
    			$teams = Team::with('action_image')->where($field, $value)->get();
    			die(json_encode($teams));
    		}
    		else{
    			$ret = array(
	    			'success'=>false,
	    			'msg'=>'The field parameters must be id, name, or sr_team_id'
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
    Description: Returns all team records in database.
    Parameters: N/A
    Returns: (str) ret - JSON object containing all of the rows in team table
    Ex: [{"id":2,"sr_league_id":"2ea6efe7-2e21-4f29-80a2-0a24ad1f5f85","league_name":"American League","league_alias":"AL","sr_division_id":"1d74e8e9-7faf-4cdb-b613-3944fa5aa739","division_name":"East","division_alias":"E","name":"Blue Jays","market":"Toronto","abbr":"TOR","sr_team_id":"1d678440-b4b1-4954-9b39-70afb3ebbcfa","venue_id":"46","created_at":"2016-04-04 19:46:12","updated_at":"2016-04-04 19:46:12"},{"id":3,"sr_league_id":"2ea6efe7-2e21-4f29-80a2-0a24ad1f5f85","league_name":"American League","league_alias":"AL","sr_division_id":"1d74e8e9-7faf-4cdb-b613-3944fa5aa739","division_name":"East","division_alias":"E","name":"Rays","market":"Tampa Bay","abbr":"TB","sr_team_id":"bdc11650-6f74-49c4-875e-778aeb7632d9","venue_id":"19","created_at":"2016-04-04 19:46:12","updated_at":"2016-04-04 19:46:12"}...]
    */
    public function getAll()
    {
        $teams = Team::with('action_image')->get();
        die(json_encode($teams->all()));
    }
    
    /*
    Name: updateAll
    Description: Updates the database team table with sports radar info.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of new rows created and rows updated.
        Ex: {"rows_updated":0,"rows_created":50}
    */
	public function updateAll(){
		$srapi = env('SPORTS_RADAR_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-p5/league/hierarchy.json?api_key={$srapi}");
		$return = json_decode($return);
		$leagues = $return->leagues;
		//supper unoptimized right now, but will do for now. This process should not run much and will probably change in 
		//future.
		$rows_updated = 0;
		$rows_created = 0;
		foreach($leagues as $league){
			$divisions = $league->divisions;
			foreach($divisions as $division){
				$division_id = $division->id;
				$division_name = $division->name;
				$division_alias = $division->alias;
				$teams = $division->teams;
				foreach($teams as $team){
					$team_sr_id = $team->id;
					$existing = Team::where('sr_team_id', $team_sr_id);
					if($existing->count() > 0){
						//get existing
						$team_my_id = $existing->first();
						$team_my_id = $team_my_id->id;
						$curteam = Team::find($team_my_id);
					}
					else{
						//create new
						$curteam = new Team;
					}
					if(isset($league->id)){$curteam->sr_league_id = $league->id;}
					if(isset($league->name)){$curteam->league_name = $league->name;}
					if(isset($league->alias)){$curteam->league_alias = $league->alias;}
					if(isset($division->id)){$curteam->sr_division_id = $division->id;}
					if(isset($division->name)){$curteam->division_name = $division->name;}
					if(isset($division->alias)){$curteam->division_alias = $division->alias;}
					if(isset($team->id)){$curteam->sr_team_id = $team->id;}
					if(isset($team->name)){$curteam->name = $team->name;}
					if(isset($team->market)){$curteam->market = $team->market;}
					if(isset($team->abbr)){$curteam->abbr = $team->abbr;}
					//get my venue primary id
					if(isset($team->venue->id)){
						$curvenue = Venue::where('sr_venue_id', $team->venue->id);
						$curvenue = $curvenue->first();
						$curvenue_id = $curvenue->id;
						if(isset($curvenue_id)){$curteam->venue_id = $curvenue_id;}
					}						
					$saved = $curteam->save();
					if($saved){
						$rows_updated ++;
					}
				}
			}
		}
		$ret = array(
			'rows_updated' => $rows_updated,
			'rows_created' => $rows_created
		);
		die(json_encode($ret));
	}

	/*
    Name: updateMLBcolors
    Description: Updates the database team table with all team colors. Gets data from http://raw.githubusercontent.com/teamcolors/teamcolors.github.io/master/src/scripts/data/leagues/mlb.json. As long as this is up and getting updated, this will work for updating all MLB teams.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of new rows updated.
        Ex: {"rows_updated":30}
    */
    public function updateMLBcolors(){
    	$teams = Team::all();
        $teams = $teams->all();
        $color_data = file_get_contents('http://raw.githubusercontent.com/teamcolors/teamcolors.github.io/master/src/scripts/data/leagues/mlb.json');
        $color_data = json_decode($color_data);
        $rows_updated = 0;
        foreach($teams as $team){
        	$team_name = "$team->market $team->name";
        	$team_name = preg_replace("/[^A-Za-z0-9 ]/", '', $team_name);
        	foreach($color_data as $color_team){
        		$color_team_name = $color_team->name;
        		if($color_team_name == $team_name || strpos($color_team_name,$team_name) !== false){	//match
        			$color_team_colors = $color_team->colors->hex;
        			$primary_color = '';
        			$secondary_color = '';
        			if(isset($color_team_colors[0])){
	        			$primary_color = '#'.$color_team_colors[0];
	        		}
	        		if(isset($color_team_colors[1])){
	        			$secondary_color = '#'.$color_team_colors[1];
	        		}
	        		$wrong_color_teams = array('New York Yankees','Los Angeles Dodgers','Houston Astros','Philadelphia Phillies','Chicago Cubs');
	        		if(in_array($color_team_name,$wrong_color_teams)){
	        			$tmp=$primary_color;
					    $primary_color=$secondary_color;
					    $secondary_color=$tmp;
	        		}
	        		$curteam = Team::find($team->id);
					$curteam->primary_color = $primary_color;
					$curteam->secondary_color = $secondary_color;
					$saved = $curteam->save();
					if($saved){
						$rows_updated ++;
					}
        		}
        	}
        }
        die(json_encode(array('rows_updated' => $rows_updated)));
    }
    /*
    Name: updateAllWonLost
    Description: Updates the database team table with the won lost numbers
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of rows updated.
        Ex: {"rows_updated":30}
    */
	public function updateAllWonLost(){
		$srapi = env('SPORTS_RADAR_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-p5/seasontd/2016/REG/standings.json?api_key={$srapi}");
		$return = json_decode($return);
		$leagues = $return->league->season->leagues;
		$rows_updated = 0;
		foreach($leagues as $league){
			$divisions = $league->divisions;
			foreach($divisions as $division){
				$teams = $division->teams;
				foreach($teams as $team){
					$cur_team = Team::where('sr_team_id',$team->id)->first();
					if(is_null($cur_team)){continue;}
					$cur_team->won = $team->win;
					$cur_team->lost = $team->loss;
					$saved = $cur_team->save();
					if($saved){
						$rows_updated++;
					}
				}
			}
		}
		die(json_encode(array('rows_updated' => $rows_updated)));
	}
}
