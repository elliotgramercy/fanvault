<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Venue;
use App\Team;
use App\Game;

use App\Http\Requests;

class GameController extends Controller
{
    /*
    Name: getOneBy
    Description: Returns all game records in database that match the ID, the name, or the Sports Radar game id (sr_game_id).
    Parameters: (required url variable) field - can either be 'name','id' or 'sr_game_id'.
    			(required url variable) value - name or id value
    Returns: (str) ret - JSON object containing the data.
    Ex: [{"id":1,"sr_league_id":"2fa448bc-fc17-4d3d-be03-e60e080fdc26","league_name":"Major League Baseball","league_alias":"MLB","sr_season_id":"565de4be-dc80-4849-a7e1-54bc79156cc8","season_year":"2016","season_type":"REG","sr_game_id":"000f209b-7132-4020-a2b6-dec9196a1802","status":"scheduled","coverage":"full","game_number":"1","day_night":"N","scheduled":"2016-08-24 23:10:00","home_team_id":"16","away_team_id":"14","venue_id":"71","created_at":"2016-04-04 20:50:03","updated_at":"2016-04-04 21:04:09"}..]
	On Fail: If function fails, a message will be returned in JSON format:
		{"success":false,"msg":"This function requires 2 url parameters. The field or value url parameter was not recieved successfully"}
    */
    public function getOneBy(Request $request){
    	$field = $request->input("field");
    	$value = $request->input("value");
    	if(isset($field) && isset($value)){
    		if($field == 'id' || $field == 'name' || $field == 'sr_game_id'){
    			$games = Game::with('home_team','away_team','venue','tailgates')->where($field, $value);
    			die(json_encode($games->get()));
    		}
    		else{
    			$ret = array(
	    			'success'=>false,
	    			'msg'=>'The field parameters must be id, name, or sr_game_id'
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
    Description: Returns all game records in database.
    Parameters: N/A
    Returns: (str) ret - JSON object containing all of the rows in game table
    Ex: [{"id":1,"sr_league_id":"2fa448bc-fc17-4d3d-be03-e60e080fdc26","league_name":"Major League Baseball","league_alias":"MLB","sr_season_id":"565de4be-dc80-4849-a7e1-54bc79156cc8","season_year":"2016","season_type":"REG","sr_game_id":"000f209b-7132-4020-a2b6-dec9196a1802","status":"scheduled","coverage":"full","game_number":"1","day_night":"N","scheduled":"2016-08-24 23:10:00","home_team_id":"16","away_team_id":"14","venue_id":"71","created_at":"2016-04-04 20:50:03","updated_at":"2016-04-04 21:04:09"}..]
    */
    public function getAll()
    {
        $games = Game::with('home_team','away_team','venue','tailgates')->get();
        die(json_encode($games->all()));
    }

    /*
    Name: getAllUpcoming
    Description: Returns all game records in database that are scheduled for 4 hours from now or further in the future
    Parameters: 
    	date 	- 	optional (format - Y-m-d). Give a date so that it lists all games scheduled >= the date provided. Defaults to now.
    Returns: (str) ret - JSON object containing all of the rows in game table that are in the future.
    Ex: [{"id":1,"sr_league_id":"2fa448bc-fc17-4d3d-be03-e60e080fdc26","league_name":"Major League Baseball","league_alias":"MLB","sr_season_id":"565de4be-dc80-4849-a7e1-54bc79156cc8","season_year":"2016","season_type":"REG","sr_game_id":"000f209b-7132-4020-a2b6-dec9196a1802","status":"scheduled","coverage":"full","game_number":"1","day_night":"N","scheduled":"2016-08-24 23:10:00","home_team_id":"16","away_team_id":"14","venue_id":"71","created_at":"2016-04-04 20:50:03","updated_at":"2016-04-04 21:04:09"}..]
    */
    public function getAllUpcoming(Request $request){
    	$now = gmdate('Y-m-d H:i:s',strtotime('now'));
    	$date = $request->input("date");
    	if(isset($date) && $date !== ''){
            $now = gmdate('Y-m-d H:i:s',strtotime($date));
        }
        $until = gmdate('Y-m-d H:i:s',strtotime(gmdate('Y-m-d',strtotime($now.' +1 day'))));
    	$games = Game::with('home_team','away_team','venue','tailgates')->where('scheduled','>=',$now)->where('scheduled','<',$until)->orderBy('scheduled', 'asc')->get();		
        die(json_encode($games->all()));
    }
    
    /*
    Name: updateAll
    Description: Updates the database game table with sports radar info.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of new rows created and rows updated.
    Ex: {"rows_updated":31,"rows_created":2399}
    IMPORTANT: Had a mismatch between the sports radar team ids and venue ids not being linked correctly in my db.
    		It seems like it may be due to their API returning game records that have team_ids/venue_ids that were not
    		found in my database. Not sure why that would be because all venues and teams were imported.
    */
	public function updateAll(){
		$srapi = env('SPORTS_RADAR_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-p5/games/2016/REG/schedule.json?api_key={$srapi}");
		$return = json_decode($return);
		$league = $return->league;
		$season = $league->season;
		$games = $season->games;
		$rows_updated = 0;
		$rows_created = 0;
		foreach($games as $game){
			$game_sr_id = $game->id;
			$existing = Game::where('sr_game_id', $game_sr_id);
			if($existing->count() > 0){
				//get existing record
				$game_my_id = $existing->first();
				$game_my_id = $game_my_id->id;
				$curGame = Game::find($game_my_id);
				
			}
			else{
				//create new record
				$curGame = new Game;
			}
			if(isset($league->id)){$curGame->sr_league_id = $league->id;}
			if(isset($league->name)){$curGame->league_name = $league->name;}
			if(isset($league->alias)){$curGame->league_alias = $league->alias;}
			if(isset($season->id)){$curGame->sr_season_id = $season->id;}
			if(isset($season->year)){$curGame->season_year = $season->year;}
			if(isset($season->type)){$curGame->season_type = $season->type;}
			if(isset($game->id)){$curGame->sr_game_id = $game->id;}
			if(isset($game->status)){$curGame->status = $game->status;}
			if(isset($game->coverage)){$curGame->coverage = $game->coverage;}
			if(isset($game->game_number)){$curGame->game_number = intval($game->game_number);}
			if(isset($game->day_night)){$curGame->day_night = $game->day_night;}
			if(isset($game->scheduled)){$curGame->scheduled = $game->scheduled;}
			if($game->home_team){
				$sr_home_team_id = $game->home_team;
				$existing = Team::where('sr_team_id', $sr_home_team_id);
				if($existing->count() > 0){
					$existing = $existing->first();
					$existing_id = $existing->id;
					$curGame->home_team_id = $existing_id;
				}
				else{
					continue;
				}
			}
			if($game->away_team){
				$sr_away_team_id = $game->away_team;
				$existing = Team::where('sr_team_id', $sr_away_team_id);
				if($existing->count() > 0){
					$existing = $existing->first();
					$existing_id = $existing->id;
					$curGame->away_team_id = $existing_id;
				}
				else{
					continue;
				}
			}
			if($game->venue){
				$venue = $game->venue;
				$sr_venue_id = $venue->id;
				$existing = Venue::where('sr_venue_id', $sr_venue_id);
				if($existing->count() > 0){
					$existing = $existing->first();
					$existing_id = $existing->id;
					$curGame->venue_id = $existing_id;
				}
				else{
					continue;
				}
			} 
			$saved = $curGame->save();
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
	/*
    Name: updateAllScores
    Description: Updates the database game table with game scores.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of rows updated.
    Ex: {"rows_updated":31}
    */
	public function updateAllScores(){
		$srapi = env('SPORTS_RADAR_API_KEY');
		/*
		$rows_updated = 0;
		for($i = 50; $i>=0; $i--){
			$now = strtotime("-{$i} days");
			$year = gmdate('Y',$now);
			$month = gmdate('m',$now);
			$day = gmdate('d',$now);
			$return = file_get_contents("http://api.sportradar.us/mlb-p5/games/{$year}/{$month}/{$day}/boxscore.json?api_key={$srapi}");
			$return = json_decode($return);
			$games = $return->league->games;
			foreach($games as $game){
				$cur_game = Game::where('sr_game_id',$game->game->id)->first();
				if(is_null($cur_game)){continue;}
				$cur_game->home_team_runs = $game->game->home->runs;
				$cur_game->away_team_runs = $game->game->away->runs;
				$saved = $cur_game->save();
				if($saved){
					$rows_updated++;
				}
			}
			sleep(1);
		}
		die(json_encode(array('rows_updated' => $rows_updated))); 
		*/
		$now = strtotime('now');
		$year = gmdate('Y',$now);
		$month = gmdate('m',$now);
		$day = gmdate('d',$now);
		$return = file_get_contents("http://api.sportradar.us/mlb-p5/games/{$year}/{$month}/{$day}/boxscore.json?api_key={$srapi}");
		$return = json_decode($return);
		$games = $return->league->games;
		$rows_updated = 0;
		foreach($games as $game){
			$cur_game = Game::where('sr_game_id',$game->game->id)->first();
			if(is_null($cur_game)){continue;}
			$cur_game->home_team_runs = $game->game->home->runs;
			$cur_game->away_team_runs = $game->game->away->runs;
			$cur_game->status = $game->game->status;
			$saved = $cur_game->save();
			if($saved){
				$rows_updated++;
			}
		}
		die(json_encode(array('rows_updated' => $rows_updated))); 
	}
}
