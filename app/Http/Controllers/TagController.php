<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use App\Tag;

class TagController extends Controller
{
	/*
    Name: get_all
    Description: Returns all tag records in database.
    Parameters: N/A
    Returns: (str) ret - JSON object containing all of the rows in tags table
    Ex: [{"id":"1","value":"Beer"},{"id":"2","value":"Hot Dogs"},{"id":"3","value":"Steaks"},{"id":"4","value":"Burgers"},{"id":"5","value":"BBQ"}..]
    */
    public function get_all()
    {
        $tags = DB::table('tags')->select(array('id', 'value'))->get();
        die(json_encode($tags));
    }
}
