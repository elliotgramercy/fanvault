<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
Route::post('/', function () {
    return view('welcome');
});
*/


Route::group(['middleware' => ['web']], function () {
	// Route::post('/', 'WelcomeController@showWelcome');
	// Route::post('auth/fb', 'SocialAuthController@redirectToProvider');
	// Route::post('auth/fb/returnFromProvider', 'SocialAuthController@returnFromProvider');
	// Route::post('auth/fb/getnonappfriends', 'SocialAuthController@getNonAppFriends');
	// Route::post('auth/fb/getappfriends', 'SocialAuthController@getAppFriends');
	// Route::post('auth/fb/getuser', 'SocialAuthController@getUser');
	// Route::post('auth/fb/getuserphoto', 'SocialAuthController@getUserPhoto');
	// Route::post('auth/fb/getuserevents', 'SocialAuthController@getUserEvents');
	// Route::post('auth/fb/getfriendsevents', 'SocialAuthController@getFriendsEvents');
	//Venues
	Route::post('srapi/venues/getOneBy', 'VenueController@getOneBy');
	Route::post('srapi/venues/getAll', 'VenueController@getAll');
	Route::post('srapi/venues/updateAll', 'VenueController@updateAll');
	Route::post('srapi/venues/get_upcoming_games_for_venue', 'VenueController@get_upcoming_games_for_venue');
	//Teams
	Route::post('srapi/teams/getOneBy', 'TeamController@getOneBy');
	Route::post('srapi/teams/getAll', 'TeamController@getAll');
	Route::post('srapi/teams/updateAll', 'TeamController@updateAll');
	Route::post('srapi/teams/updateMLBcolors', 'TeamController@updateMLBcolors');
	Route::post('srapi/teams/updateAllWonLost', 'TeamController@updateAllWonLost');
	Route::post('srapi/teams/updateTeamPlayers', 'TeamController@updateTeamPlayers');
	Route::post('srapi/teams/updatePlayerHeadShots', 'TeamController@updatePlayerHeadShots');
	//Games
	Route::post('srapi/games/getOneBy', 'GameController@getOneBy');
	Route::post('srapi/games/getAll', 'GameController@getAll');
	Route::post('srapi/games/getAllUpcoming', 'GameController@getAllUpcoming');
	Route::post('srapi/games/updateAll', 'GameController@updateAll');
	Route::post('srapi/games/updateAllScores', 'GameController@updateAllScores');
	Route::post('srapi/games/updateGames', 'GameController@updateGames');

	//IMAGES
	Route::post('srapi/images/updateVenueImages', 'ImageController@updateVenueImages');
	Route::post('srapi/images/getVenueImages', 'ImageController@getVenueImages');
	Route::post('srapi/images/updateActionImages', 'ImageController@updateActionImages');

	//AWS
	Route::post('aws/sns', 'AwsController@init');

	//USERS
	Route::post('users/get_all', 'UserController@get_all_users');
	Route::post('users/get', 'UserController@get');
	Route::post('users/update', 'UserController@update_user');
	Route::post('users/delete', 'UserController@delete_user');
	Route::post('users/get_social_feed', 'UserController@get_social_feed');
	Route::post('users/add_game_attendee', 'GamesAttendeeController@add_game_attendee');
	Route::post('users/remove_game_attendee', 'GamesAttendeeController@remove_game_attendee');
	Route::post('users/get_user_games_attending', 'GamesAttendeeController@get_user_games_attending');
	Route::post('users/add_tailgate_attendee', 'TailgatesAttendeeController@add_tailgate_attendee');
	Route::post('users/get_game_for_user', 'UserController@get_game_for_user');
	Route::post('users/get_game_for_user_2', 'UserController@get_game_for_user_2');
	Route::post('users/search', 'UserController@search');
	Route::post('users/search_type_ahead', 'UserController@search_type_ahead');
	Route::post('users/user_game_images/update', 'UserGameImageController@update');
	Route::post('users/user_game_images/delete', 'UserGameImageController@delete');
	Route::post('users/user_game_images/get_users_images_for_game', 'UserGameImageController@get_users_images_for_game');
	Route::post('users/user_game_images/get_all_game_images', 'UserGameImageController@get_all_game_images');
	Route::post('users/user_game_crew/update', 'UserGameCrewController@update');
	Route::post('users/user_game_crew/get_user_crew_members_for_game', 'UserGameCrewController@get_user_crew_members_for_game');
	Route::post('users/user_game_crew/delete', 'UserGameCrewController@delete');
	Route::post('users/game_invites/invite_friend', 'GamesInviteController@invite_friend');
	Route::post('users/game_invites/get_friends_invited_to_game', 'GamesInviteController@get_friends_invited_to_game');
	Route::post('users/game_invites/accept_invite', 'GamesInviteController@accept_invite');
	Route::post('users/game_invites/decline_invite', 'GamesInviteController@decline_invite');

	//FRIENDS
	Route::post('users/make_friends', 'FriendController@make_friends');
	Route::post('users/are_friends', 'FriendController@are_friends');
	Route::post('users/get_all_friends', 'FriendController@get_all_friends');
	Route::post('users/unfriend', 'FriendController@unfriend');
	
	//FRIENDS INVITES
	Route::post('users/get_all_invited', 'FriendsInviteController@get_all_invited');
	Route::post('users/invite_friend', 'FriendsInviteController@invite_friend');
	Route::post('users/uninvite', 'FriendsInviteController@uninvite');

	//DEVICE TOKENS
	Route::post('devices/get_all_user_devices', 'UserDeviceController@get_all_user_devices');
	Route::post('devices/add_device_token', 'UserDeviceController@add_device_token');
	Route::post('devices/deactivate_device_token', 'UserDeviceController@deactivate_device_token');
	Route::post('devices/register_device_token', 'UserDeviceController@register_device_token');

	//TAGS
	Route::post('tags/getAll', 'TagController@get_all');

	//TAILGATES
	Route::post('tailgates/update', 'TailgateController@update_tailgate');
	Route::post('tailgates/delete', 'TailgateController@delete_tailgate');

	//TICKETS
	Route::post('tickets/update', 'TicketController@update');
	Route::post('tickets/get', 'TicketController@get');
	Route::post('tickets/delete', 'TicketController@delete');
});
