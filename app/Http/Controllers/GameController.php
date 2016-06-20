<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Venue;
use App\Team;
use App\Game;
use App\Player;
use App\Official;
use App\GamesLineup;
use App\GamesScore;
use App\GamesOfficial;
use App\GamesPlayer;

use App\Http\Requests;

use DB;

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
    			$games = Game::with('home_team','away_team','venue','tailgates')->where($field, $value)->get()->toArray();
    			foreach($games as &$game){
    				$game['venue']['venue_image'] = $game['venue']['venue_image']['url'];
    			}
    			die(json_encode($games));
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
        $games = Game::with('home_team','away_team','venue','tailgates')->get()->toArray();
        foreach($games as &$game){
			$game['venue']['venue_image'] = $game['venue']['venue_image']['url'];
		}
        die(json_encode($games));
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
    	$games = Game::with('home_team','away_team','venue','tailgates')->where('scheduled','>=',$now)->where('scheduled','<',$until)->orderBy('scheduled', 'asc')->get()->toArray();	
		foreach($games as &$game){
			$game['venue']['venue_image'] = $game['venue']['venue_image']['url'];
		}	
        die(json_encode($games));
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
		DB::table('temp_log')->insert(
            ['value' => 'start 1am GameController@updateAll: '.gmdate('Y-m-d H:i:s',strtotime('now'))]
        );
		$srapi = env('SPORTS_RADAR_API_KEY');
		$return = file_get_contents("http://api.sportradar.us/mlb-p5/games/2016/REG/schedule.json?api_key={$srapi}");
		$return = json_decode($return);
		$league = $return->league;
		$season = $league->season;
		$games = $season->games;
		$rows_updated = 0;
		$rows_created = 0;
		$game_score_rows_updated = 0;
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
				$existing = Team::where('sr_team_id', $sr_home_team_id)->first();
				if(!is_null($existing)){
					$curGame->home_team_id = $existing->id;
				}
				else{
					continue;
				}
			}
			if($game->away_team){
				$sr_away_team_id = $game->away_team;
				$existing = Team::where('sr_team_id', $sr_away_team_id)->first();
				if(!is_null($existing)){
					$curGame->away_team_id = $existing->id;
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
				//now that we have saved the game, lets create a GamesScore record for this game too so that it
				//exists at 0s.
				$cur_games_home_score = GamesScore::where([
					'game_id'=>$curGame->id,
					'team_id'=>$curGame->home_team_id
				])->first();
				//only create new records, dont modify existing.
				if(is_null($cur_games_home_score)){
					//create nwe one
					$cur_games_home_score = new GamesScore;
					$cur_games_home_score->game_id = $curGame->id;
					$cur_games_home_score->team_id = $curGame->home_team_id;
					$saved = $cur_games_home_score->save();
					if($saved){
						$game_score_rows_updated++;
					}
				}
				$cur_games_away_score = GamesScore::where([
					'game_id'=>$curGame->id,
					'team_id'=>$curGame->away_team_id
				])->first();
				//only create new records, dont modify existing.
				if(is_null($cur_games_away_score)){
					//create nwe one
					$cur_games_away_score = new GamesScore;
					$cur_games_away_score->game_id = $curGame->id;
					$cur_games_away_score->team_id = $curGame->away_team_id;
					$saved = $cur_games_away_score->save();
					if($saved){
						$game_score_rows_updated++;
					}
				}
				$rows_updated ++;
			}
		}
		$ret = array(
			'rows_updated' => $rows_updated,
			'rows_created' => $rows_created,
			'game_score_rows_updated'=>$game_score_rows_updated
		);
		DB::table('temp_log')->insert(
            ['value' => 'end 1am GameController@updateAll: '.gmdate('Y-m-d H:i:s',strtotime('now')),'value_2'=>json_encode($ret)]
        );
		return json_encode($ret);
	}
	/*
    Name: updateAllScores
    Description: Updates the database game table with game scores.
    Parameters: N/A
    Returns: (str) ret - JSON object containing the number of rows updated.
    Ex: {"rows_updated":31}
    */
    /*no longer used we now use updateGames
	public function updateAllScores(){
		$srapi = env('SPORTS_RADAR_API_KEY');
		$rows_updated = 0;
		for($i = 8; $i>=0; $i--){
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
				$cur_game->status = $game->game->status;
				$saved = $cur_game->save();
				if($saved){
					$rows_updated++;
				}
			}
			sleep(1);
		}
		return json_encode(array('rows_updated' => $rows_updated)); 
		// $now = strtotime('now');
		// $year = gmdate('Y',$now);
		// $month = gmdate('m',$now);
		// $day = gmdate('d',$now);
		// $return = file_get_contents("http://api.sportradar.us/mlb-p5/games/{$year}/{$month}/{$day}/boxscore.json?api_key={$srapi}");
		// $return = json_decode($return);
		// $games = $return->league->games;
		// $rows_updated = 0;
		// foreach($games as $game){
		// 	$cur_game = Game::where('sr_game_id',$game->game->id)->first();
		// 	if(is_null($cur_game)){continue;}
		// 	$cur_game->home_team_runs = $game->game->home->runs;
		// 	$cur_game->away_team_runs = $game->game->away->runs;
		// 	$cur_game->status = $game->game->status;
		// 	$saved = $cur_game->save();
		// 	if($saved){
		// 		$rows_updated++;
		// 	}
		// }
		// die(json_encode(array('rows_updated' => $rows_updated))); 
	}
	
	/*
    Name: updateGames
    Description: Updates the lineups for the given +x days.. x=0 is today, x=1 is tomorrow.. x cannot exceed 3.
    Parameters: 
    	num - number of days in future to update.
    Returns: (str) ret - JSON object containing the number of new rows created and rows updated.
    Ex: {"rows_updated":31,"rows_created":2399}
    */
	public function updateGames(){
		DB::table('temp_log')->insert(
            ['value' => 'start 15min GameController@updateGames: '.gmdate('Y-m-d H:i:s',strtotime('now'))]
        );
		$srapi = env('SPORTS_RADAR_API_KEY');
		$positions_arr = array('P','C','1B','2B','3B','SS','LF','CF','RF','DH','PH','PR');
		$lineup_rows_updated = 0;
		$game_rows_updated = 0;
		$game_score_rows_updated = 0;
		$officials_created = 0;
		$game_officials_created = 0;
		$players_created = 0;
		$game_players_updated = 0;
		for($i = 1; $i>=0; $i--){
			$now = strtotime("-{$i} days");
			$year = gmdate('Y',$now);
			$month = gmdate('m',$now);
			$day = gmdate('d',$now);
			$return = file_get_contents("http://api.sportradar.us/mlb-p5/games/$year/$month/$day/summary.json?api_key={$srapi}");
			$return = json_decode($return);
			$games = $return->league->games;
			foreach($games as $game){
				$cur_game = Game::where('sr_game_id',$game->game->id)->first();
				if(is_null($cur_game)){continue;}
				$cur_game->home_team_runs = $game->game->home->runs;
				$cur_game->away_team_runs = $game->game->away->runs;
				$cur_game->status = $game->game->status;
				$saved = $cur_game->save();
				if($saved){
					$game_rows_updated++;
				}
				$home_and_away = array('home','away');
				foreach($home_and_away as $home_or_away){
					//lets get the team_id
					$cur_team = Team::where('sr_team_id',$game->game->$home_or_away->id)->first();
					if(is_null($cur_team)){continue;}
					//now we can update the lineups.
					if(isset($game->game->$home_or_away->lineup)){
						$lineups = $game->game->$home_or_away->lineup;
						foreach($lineups as $lineup){
							//lets get the player in this lineup.
							$cur_player = Player::where('sr_player_id',$lineup->id)->first();
							if(is_null($cur_player)){continue;}
							//check if GamesLineup record exists.
							$cur_lineup = GamesLineup::where([
								'game_id'=>$cur_game->id,
								'player_id'=>$cur_player->id,
								'team_id'=>$cur_team->id
							])->first();
							if(is_null($cur_lineup)){
								$cur_lineup = new GamesLineup;
								$cur_lineup->game_id = $cur_game->id;
								$cur_lineup->player_id = $cur_player->id;
								$cur_lineup->team_id = $cur_team->id;
							}
							$cur_lineup->position_num = $lineup->position;
							$cur_lineup->position = $positions_arr[$lineup->position - 1];
							$lineup_saved = $cur_lineup->save();
							if($lineup_saved){
								$lineup_rows_updated++;
							}
						}
					}
					//now lets also update game_scores
					//first lets get the GamesScore object for this particular team and game
					$cur_games_score = GamesScore::where([
						'game_id'=>$cur_game->id,
						'team_id'=>$cur_team->id
					])->first();
					if(is_null($cur_games_score)){
						//create nwe one
						$cur_games_score = new GamesScore;
						$cur_games_score->game_id = $cur_game->id;
						$cur_games_score->team_id = $cur_team->id;
					}
					if(isset($game->game->$home_or_away->hits)){
						$cur_games_score->hits = $game->game->$home_or_away->hits;
					}
					if(isset($game->game->$home_or_away->runs)){
						$cur_games_score->runs = $game->game->$home_or_away->runs;
					}	
					if(isset($game->game->$home_or_away->errors)){
						$cur_games_score->errors = $game->game->$home_or_away->errors;
					}
					if(isset($game->game->$home_or_away->scoring)){
						$cur_games_score->inning_scores = $game->game->$home_or_away->scoring;
					}
					foreach(array('team_lob','slg','obp','avg') as $one_stat){
						if(isset($game->game->$home_or_away->statistics->hitting->$one_stat)){
							$cur_games_score->$one_stat = $game->game->$home_or_away->statistics->hitting->$one_stat;
						}
					}
					if(isset($game->game->$home_or_away->statistics->fielding->fpct)){
						$cur_games_score->fpct = $game->game->$home_or_away->statistics->fielding->fpct;
					}
					$game_score_saved = $cur_games_score->save();
					if($game_score_saved){
						$game_score_rows_updated++;
					}
					//now lets update the games players.
					//if there are players
					if(isset($game->game->$home_or_away->players)){
						//loop through each player
						foreach($game->game->$home_or_away->players as $player){
							//first get the player_id in players table
							$existing_player = Player::where('sr_player_id',$player->id)->first();
							if(is_null($existing_player)){	//this should never really happen, but just in case.
								//create new if it didnt exist
								$existing_player = new Player;
								$existing_player->team_id = $cur_team->id;
								$existing_player->sr_player_id = $player->id;
								$existing_player->position = $player->position;
								$existing_player->primary_position = $player->primary_position;
								$existing_player->first_name = $player->first_name;
								$existing_player->last_name = $player->last_name;
								$existing_player->preferred_name = $player->preferred_name;
								$existing_player->jersey_number = $player->jersey_number;
								if($existing_player->save()){
									$players_created++;
								}
							}
							//now that we have our player lets see if there is already a GamesPlayer record that we need to update,
							//or if we need to create a new one.
							$existing_games_player = GamesPlayer::where(['game_id'=>$cur_game->id,'player_id'=>$existing_player->id])->first();
							if(is_null($existing_games_player)){
								//new record
								$existing_games_player = new GamesPlayer;
								$existing_games_player->player_id = $existing_player->id;
								$existing_games_player->game_id = $cur_game->id;
								$existing_games_player->team_id = $cur_team->id;
							}
							if($player->position === 'P'){
								$existing_games_player->is_pitcher = true;
							}
							if(isset($player->statistics->pitching)){	//player has pitching stats
								$existing_games_player->pitching_er = $player->statistics->pitching->runs->earned;
								$existing_games_player->pitching_era = $player->statistics->pitching->era;
								$existing_games_player->pitching_so = $player->statistics->pitching->outcome->ktotal;
								$existing_games_player->pitching_bb = $player->statistics->pitching->onbase->bb;
								$existing_games_player->pitching_h = $player->statistics->pitching->onbase->h;
								$existing_games_player->pitching_r = $player->statistics->pitching->runs->total;
								$existing_games_player->pitching_ip = $player->statistics->pitching->ip_1;
								$existing_games_player->pitching_bf = $player->statistics->pitching->bf;
								$existing_games_player->pitching_gofo = $player->statistics->pitching->gofo;
							}
							if(isset($player->statistics->hitting)){	//player has hitting stats
								$existing_games_player->hitting_h = $player->statistics->hitting->onbase->h;
								$existing_games_player->hitting_bb = $player->statistics->hitting->onbase->bb;
								$existing_games_player->hitting_so = $player->statistics->hitting->outcome->ktotal;
								$existing_games_player->hitting_avg = $player->statistics->hitting->avg;
								$existing_games_player->hitting_rbi = $player->statistics->hitting->rbi;
								$existing_games_player->hitting_r = $player->statistics->hitting->runs->total;
								$existing_games_player->hitting_ab = $player->statistics->hitting->ab;
								$existing_games_player->hitting_d = $player->statistics->hitting->onbase->d;
								$existing_games_player->hitting_hr = $player->statistics->hitting->onbase->hr;
								$existing_games_player->hitting_stolen = $player->statistics->hitting->steal->stolen;
							}
							//and finally lets save this games_player
							if($existing_games_player->save()){
								$game_players_updated++;
							}
						}
					}
				}
				// Now lets store the game officials
				if(isset($game->game->officials)){
					$officials = $game->game->officials;
					foreach($officials as $one_official){
						$existing_official = Official::where('sr_official_id',$one_official->id)->first();
						if(is_null($existing_official)){
							$existing_official = new Official;
							foreach(array('first_name','last_name','assignment','experience') as $sett){
								if(isset($one_official->$sett)){
									$existing_official->$sett = $one_official->$sett;
								}
							}
							if(isset($one_official->id)){
								$existing_official->sr_official_id = $one_official->id;
							}
							if($existing_official->save()){
								$officials_created++;
							}	
						}
						//now that we have the official in the db, we can create the GamesOfficial record.
						//lets see if there is already an existing record, in which case we dont need to do anything
						$existing_games_official = GamesOfficial::where(['game_id'=>$cur_game->id,'official_id'=>$existing_official->id])->first();
						if(is_null($existing_games_official)){
							$existing_games_official = new GamesOfficial;
							$existing_games_official->game_id = $cur_game->id;
							$existing_games_official->official_id = $existing_official->id;
							if($existing_games_official->save()){
								$game_officials_created++;
							}
						}
					}

				}
			}
			sleep(1);
		}
		$ret = json_encode(array(
			'game_rows_updated' => $game_rows_updated,
			'lineup_rows_updated' => $lineup_rows_updated,
			'game_score_rows_updated'=>$game_score_rows_updated,
			'officials_created'=>$officials_created,
			'game_officials_created'=>$game_officials_created,
			'players_created'=>$players_created,
			'game_players_updated'=>$game_players_updated
		));
		DB::table('temp_log')->insert(
            ['value' => 'end 15min GameController@updateGames: '.gmdate('Y-m-d H:i:s',strtotime('now')), 'value_2'=>$ret]
        );
		return $ret;
	}
}
