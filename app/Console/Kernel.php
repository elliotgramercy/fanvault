<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// use App\Http\Controllers\ImageController as ImageController;
// use App\Http\Controllers\TeamController as TeamController;
// use App\Http\Controllers\GameController as GameController;

// use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Update Action Images once a week on sunday
        /* Turned this off because our live image api trial expired.
        $schedule->call(function () {
            $image_controller = new ImageController;
            $return = $image_controller->updateActionImages();
        })->weekly()->mondays()->at('3:00');
        */
        //Update Team Wins and Losses every fifteen
        //Update game scores every 15 minutes
        //App\Http\Controllers
        // $schedule->call(function () {
        //     DB::table('temp_log')->insert(
        //         ['value' => 'started 15min call: '.gmdate('Y-m-d H:i:s',strtotime('now'))]
        //     );
        //     $team_controller = new TeamController;
        //     $game_controller = new GameController;
        //     $ret = $team_controller->updateAllWonLost();
        //     sleep(1);
        //     //updateGames updates scores and lineups.
        //     $ret2 = $game_controller->updateGames();
        //     DB::table('temp_log')->insert(
        //         ['value' => 'ended 15min call: '.gmdate('Y-m-d H:i:s',strtotime('now')), 'value_2'=>$ret, 'value_3'=>$ret2]
        //     );
        //     $return = true;
        // })->cron('*/15 * * * * *');
        $schedule->call('App\Http\Controllers\TeamController@updateAllWonLost')->cron('*/15 * * * * *');
        $schedule->call('App\Http\Controllers\GameController@updateGames')->cron('*/15 * * * * *');
        
        /*$schedule->call(function () {
            DB::table('temp_log')->insert(
                ['value' => 'started daily 1am call: '.gmdate('Y-m-d H:i:s',strtotime('now'))]
            );
            $game_controller = new GameController;
            $team_controller = new TeamController;
            $ret = $team_controller->updateTeamPlayers();
            sleep(1);
            $ret2 = $game_controller->updateAll();
            DB::table('temp_log')->insert(
                ['value' => 'ended daily 1am call: '.gmdate('Y-m-d H:i:s',strtotime('now')), 'value_2'=>$ret, 'value_3'=>$ret2]
            );
            $return = true;
        })->dailyAt('15:32');*/
        $schedule->call('App\Http\Controllers\TeamController@updateTeamPlayers')->dailyAt('5:00');
        $schedule->call('App\Http\Controllers\GameController@updateAll')->dailyAt('5:00');
    }
}
