<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Http\Controllers\ImageController as ImageController;
use App\Http\Controllers\TeamController as TeamController;
use App\Http\Controllers\GameController as GameController;

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
        //Update Team Wins and Losses daily at night.
        $schedule->call(function () {
            $team_controller = new TeamController;
            $return = $team_controller->updateAllWonLost();
        })->dailyAt('3:00');
        //Update game scores every 15 minutes
        $schedule->call(function () {
            $game_controller = new GameController;
            $return = $game_controller->updateAllScores();
        })->cron('*/15 * * * *');
    }
}
