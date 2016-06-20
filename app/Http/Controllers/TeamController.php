<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;
use App\Venue;
use App\Image;
use App\Player;
use App\GamesLineup;
use App\PlayersHeadshot;
use App\Http\Requests;

use DB;

set_time_limit(3000);

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
		return json_encode($ret);
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
        return json_encode(array('rows_updated' => $rows_updated));
    }
    /*
    Name: updateAllWonLost
    Description: Updates the database team table with the won lost numbers
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of rows updated.
        Ex: {"rows_updated":30}
    */
	public function updateAllWonLost(){
		DB::table('temp_log')->insert(
            ['value' => 'start 15min TeamController@updateAllWonLost: '.gmdate('Y-m-d H:i:s',strtotime('now'))]
        );
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
		$ret = json_encode(array('rows_updated' => $rows_updated));
		DB::table('temp_log')->insert(
            ['value' => 'end 15min TeamController@updateAllWonLost: '.gmdate('Y-m-d H:i:s',strtotime('now')),'value_2'=>$ret]
        );
		return $ret;
	}
	/*
    Name: updateTeamPlayers
    Description: Updates the teams player info and stats
    Parameters: n/a
    Returns: (str) ret - JSON object containing the number of new rows created and rows updated.
    Ex: {"rows_created":7,"rows_updated":983}
    */
	public function updateTeamPlayers(){
		DB::table('temp_log')->insert(
            ['value' => 'start 1am TeamController@updateTeamPlayers: '.gmdate('Y-m-d H:i:s',strtotime('now'))]
        );
		$num = 1;
		$srapi = env('SPORTS_RADAR_API_KEY');
		$now = strtotime('now');
		$year = gmdate('Y',$now);
		//the first time I run this I am going to get ALL teams and update all players for all teams.
		//however in the future I am only going to get teams that player today and update the player
		//stats for only those teams since this will run daily.
		$teams = Team::all();
		//since this updates pretty fast (within 30 seconds) I am just going to let it update all team player stats daily.
		$rows_updated = 0;
		$rows_created = 0;
		foreach($teams as $team){
			$return = file_get_contents("http://api.sportradar.us/mlb-p5/seasontd/2016/REG/teams/{$team->sr_team_id}/statistics.json?api_key={$srapi}");
			$return = json_decode($return);
			$players = $return->players;
			$player_ids = array();	//i am going to make an array of sr_player_ids to match against my database to remove old inactive players.
			foreach($players as $player){
				$new_player = false;
				//lets check if player is already in the players table in the db, if he is then we will update if not then we will add one.
				$cur = Player::where('sr_player_id',$player->id)->first();
				if(is_null($cur)){
					$cur = new Player;
					$new_player = true;
				}
				$cur->team_id = $team->id;
				$cur->sr_player_id = $player->id;
				$cur->position = $player->position;
				$cur->primary_position = $player->primary_position;
				$cur->first_name = $player->first_name;
				$cur->last_name = $player->last_name;
				$cur->preferred_name = $player->preferred_name;
				$cur->jersey_number = $player->jersey_number;
				if(isset($player->statistics->pitching)){	//player has pitching stats
					$cur->pitching_er = $player->statistics->pitching->runs->earned;
					$cur->pitching_era = $player->statistics->pitching->era;
					$cur->pitching_so = $player->statistics->pitching->outcome->ktotal;
					$cur->pitching_bb = $player->statistics->pitching->onbase->bb;
					$cur->pitching_h = $player->statistics->pitching->onbase->h;
					//also created win and loss
					$cur->pitching_win = $player->statistics->pitching->games->win;
					$cur->pitching_loss = $player->statistics->pitching->games->loss;
				}
				if(isset($player->statistics->hitting)){	//player has hitting stats
					$cur->hitting_h = $player->statistics->hitting->onbase->h;
					$cur->hitting_bb = $player->statistics->hitting->onbase->bb;
					$cur->hitting_so = $player->statistics->hitting->outcome->ktotal;
					$cur->hitting_avg = $player->statistics->hitting->avg;
					$cur->hitting_rbi = $player->statistics->hitting->rbi;
				}
				$saved = $cur->save();
				$player_ids[] = $cur->sr_player_id;
				if($saved){
					if($new_player){
						$rows_created++;
					}
					else{
						$rows_updated++;
					}
				}
			}
			//now that we have all of the sr_player_ids for the team, so we update old records to inactive
			$old_player_ids = Player::whereNotIn('sr_player_id',$player_ids)->where('team_id',$team->id)->update(['status' => 'I']);
			sleep(1);
		}
		$ret = json_encode(array(
			'rows_created' => $rows_created,
			'rows_updated' => $rows_updated
		));
		DB::table('temp_log')->insert(
            ['value' => 'end 1am TeamController@updateTeamPlayers: '.gmdate('Y-m-d H:i:s',strtotime('now')),'value_2'=>$ret]
        );
		return $ret;
	}
	/*
    Name: updatePlayerHeadShots
    Description: Updates the headshots for the players. Saves all the images in AWS S3
    Parameters: n/a
    Returns: (str) ret - JSON object containing the number of new rows created and old rows that were removed.
    Ex: {"rows_created":7,"rows_updated":983}
    */
	public function updatePlayerHeadShots(){
		$srapi = env('SPORTS_RADAR_IMAGES_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-images-p2/usat/players/headshots/manifests/all_assets.xml?api_key={$srapi}");
		$return = json_decode(json_encode(simplexml_load_string($return, 'SimpleXMLElement', LIBXML_NOCDATA)),true);
		$assets = $return['asset'];
		$rows_created = 0;
		$rows_removed = 0;
		$sr_player_id_count = array();
		$player_id_count = array();
		$aws_controller = new AwsController;
		foreach($assets as $asset){
			if(!isset($asset['@attributes']['id']) || !isset($asset['@attributes']['player_id']) || !isset($asset['links']['link'])){
				continue;
			}
			if(!in_array($asset['@attributes']['player_id'],$sr_player_id_count)){
				$sr_player_id_count[] = $asset['@attributes']['player_id'];
			}
			$sr_image_id = $asset['@attributes']['id'];
			$sr_player_id = $asset['@attributes']['player_id'];
			$headshots = array(
				'org' => $asset['links']['link'][0]['@attributes']['href'],
				'250'=>$asset['links']['link'][1]['@attributes']['href'],
				'190'=>$asset['links']['link'][2]['@attributes']['href']
			);
			//lets get the player
			$existing_player = Player::where('sr_player_id',$sr_player_id)->first();
			if(is_null($existing_player)){	//if player doesnt exist then skip.
				continue;
			}
			if(!in_array($existing_player->id,$player_id_count)){
				$player_id_count[] = $existing_player->id;
			}
			//now lets get existing player images from db
			$existing_headshots = PlayersHeadshot::where('player_id',$existing_player->id)->get();
			//we need to delete all three from the database and remove them from AWS S3
			if($existing_headshots->count()){
				foreach($existing_headshots as $existing_headshot){
	                if(isset($existing_headshot->url) && $existing_headshot->url !== ''){
	                    $aws_controller->delete_aws_image(basename($existing_headshot->url),'playerheadshots');   //nothing checking success
	                }
	                //now delete from db as well
	                if($existing_headshot->delete()){
	                	$rows_removed++;
	                }
				}
			}
			//or now the old headshots for this player should have been removed so we can now add new images in.
			foreach($headshots as $name=>$value){
				$full_url = 'http://api.sportradar.us/mlb-images-p2/usat'.$value."?api_key={$srapi}";
				$uploaded_image_url = $aws_controller->upload_headshot_image($full_url,$name);
				$cur = new PlayersHeadshot;
				$cur->sr_image_id = $sr_image_id;
				$cur->player_id = $existing_player->id;
				$cur->url = $uploaded_image_url;
	            $cur->size = $name;
				if($cur->save()){
					$rows_created++;
				}
			}
			//now that we have all of the originals uploaded, I want to add some new sizes as well.
			/*$sizes = array(1000,500);
			foreach($sizes as $size){
				$full_url = 'http://api.sportradar.us/mlb-images-p2/usat'.$headshots['org']."?api_key={$srapi}";
				$uploaded_image_url = $aws_controller->upload_headshot_image($full_url,$size,$size);
				$cur = new PlayersHeadshot;
				$cur->sr_image_id = $sr_image_id;
				$cur->player_id = $existing_player->id;
				$cur->url = $uploaded_image_url;
	            $cur->size = $size;
				if($cur->save()){
					$rows_created++;
				}
			}*/
		}
		$ret = array(
			"rows_created"=>$rows_created,
			"rows_removed"=>$rows_removed,
			'sr_player_id_count'=>count($sr_player_id_count),
			'player_id_count'=>count($player_id_count)
        );
		DB::table('temp_log')->insert(
            ['value' => 'Update player headshots finished at: '.gmdate('Y-m-d H:i:s',strtotime('now')),'value_2'=>$ret]
        );
        die(json_encode($ret));    
	}
}
