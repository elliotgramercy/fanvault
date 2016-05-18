<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserDevice;

use App\User;
use App\Http\Controllers\AwsController as AwsController;

class UserDeviceController extends Controller
{
	/*
    NAME: get_all_user_devices
    DESCRIPTION: gets all devices for given user id
    PARAMETERS: 
    	id 				- id from user table.
    RETURNS: returns all rows in the user devices table for particular user id
    */
	public function get_all_user_devices(Request $request){
		$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $devices = UserDevice::all();
        die(json_encode($devices->all()));
	}
	/*
	NAME: register_device_token
    DESCRIPTION: the url endpoint for reregistering the device token after it has been activated and aws arns cleared.
    PARAMETERS: 
    	id 				- id from user table.
        token              - device token
    RETURNS: updates database with the endpoint and subscription arns.
	*/
	public function register_device_token(Request $request){
		$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $token = $request->input("token");
        if(!isset($token) || $token === '' || strlen($token) !== 64){
            $ret = array(
              "success"=>false,
              "msg"=>'The token was not recieved. Token must be 64 alphanumeric characters.'
            );
            die(json_encode($ret));
        }
        $device = UserDevice::where(['user_id'=>$id,'device_token'=>$token])->first();
        if(!is_null($device)){
        	$ret = array(
              "success"=>false,
              "msg"=>"The device token ({$token}) already exists for that user ({$id}). No changes were made."
            );
            die(json_encode($ret));
        }
		$from_aws = $this->register_token($token);
		$endpoint_arn = $from_aws['endpoint_arn'] ?: '';
		$subscription_arn = $from_aws['subscription_arn'] ?: '';
		$cur = UserDevice::where(['user_id'=>$id,'device_token'=>$token])->first();
		$cur->aws_endpoint_arn = $endpoint_arn;
		$cur->aws_subscription_arn = $subscription_arn;
		$saved = $cur->save();
		$ret = array(
          "success"=>$saved,
        );
        die(json_encode($ret));
	}
	/*
    NAME: register_token
    DESCRIPTION: Creates endpointarn and subscription arn with aws. Returns array with both values.
    PARAMETERS: 
        token              - device token
    RETURNS: array with both values.
    */
	public function register_token($token){
		$aws_controller = new AwsController;
		$create_aws_endpoint = $aws_controller->createPlatformEndpoint(false,$token);
		$endpoint_arn = $create_aws_endpoint['EndpointArn'];
		if(isset($endpoint_arn) && $endpoint_arn !== ''){
			//the following line should subscribe them to the main Fanvault topic for general alerts.
    		$create_aws_subscription = $aws_controller->subscribe(false,'arn:aws:sns:us-east-1:442701386251:Fanvault',$endpoint_arn);
    		$subscription_arn = $create_aws_subscription['SubscriptionArn'];
		}
		$ret = array(
			'endpoint_arn' => $endpoint_arn,
			'subscription_arn' => $subscription_arn
		);
		return $ret;
	}
	/*
    NAME: add_token
    DESCRIPTION: Checks if token exists then adds new token if it doesnt exist.
    PARAMETERS: 
    	id 				- id from user table.
        token              - device token
    RETURNS: boolean - whether or not token was successfully subscribed.
    */
    public function add_token($id=null,$token=null){
    	$existing = UserDevice::where(['user_id'=>$id, 'device_token'=>$token])->first();
    	if(is_null($existing)){
    		return $this->add_new_token($id,$token);
    	}
    	else{
    		return false;
    	}
	}
	/*
    NAME: add_new_token
    DESCRIPTION: Adds the device token to the database and calls the subscribe aws method. This function already presumes that
    	the id and tokens provided are valid and just inserts into db.
    PARAMETERS: 
    	id 				- id from user table.
        token              - device token
    RETURNS: boolean - whether or not token was successfully subscribed.
    */
    public function add_new_token($id=null,$token=null){
		//insert record into user devices table.
		$cur = new UserDevice;
		$cur->user_id = $id;
		$cur->device_token = $token;
		$from_aws = $this->register_token($token);
		$endpoint_arn = $from_aws['endpoint_arn'] ?: '';
		$subscription_arn = $from_aws['subscription_arn'] ?: '';
		$cur->aws_endpoint_arn = $endpoint_arn;
		$cur->aws_subscription_arn = $subscription_arn;
		$saved = $cur->save();
		if($saved){
			return true;
		}
		else{
			return false;
		}
    }
	/*
    NAME: add_device_token
    DESCRIPTION: the endpoint for adding a new token. This will check to ensure that the values are valid and then call the
    	add_device_token function that will actually handle the adding and registering with AWS.
    PARAMETERS: 
    	id 				- id from user table.
        token              - device token
    RETURNS: boolean - whether or not token was successfully subscribed.
    */
    public function add_device_token(Request $request){
    	$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $token = $request->input("token");
        if(!isset($token) || $token === '' || strlen($token) !== 64){
            $ret = array(
              "success"=>false,
              "msg"=>'The token was not recieved. Token must be 64 alphanumeric characters.'
            );
            die(json_encode($ret));
        }
        $device = UserDevice::where(['user_id'=>$id,'device_token'=>$token])->first();
        if(!is_null($device)){
        	$ret = array(
              "success"=>false,
              "msg"=>"The device token ({$token}) already exists for that user ({$id}). No changes were made."
            );
            die(json_encode($ret));
        }
        $success = $this->add_new_token($id,$token);
        $ret = array(
          "success"=>$success
        );
        die(json_encode($ret));
    }
 	/*
    NAME: deactivate_device_token
    DESCRIPTION: removes the device token from the database and calls methods to unsubscribe and remove endpointarns. 
    	the id and tokens provided are valid and just inserts into db.
    PARAMETERS: 
    	id 				- id from user table.
        token              - device token
    RETURNS: boolean - whether or not token endpoint and subscribtion arns were successfully removed.
    */
    public function deactivate_device_token(Request $request){
    	$id = $request->input("id");
        if(!isset($id) || $id === ''){
            $ret = array(
              "success"=>false,
              "msg"=>'The id was not recieved.'
            );
            die(json_encode($ret));
        }
        $cur = User::where('id',$id)->first();
        if(is_null($cur)){  //user not found
            $ret = array(
              "success"=>false,
              "msg"=>"The id ({$id}) provided was not found in the database."
            );
            die(json_encode($ret));
        }
        $token = $request->input("token");
        if(!isset($token) || $token === '' || strlen($token) !== 64){
            $ret = array(
              "success"=>false,
              "msg"=>'The token was not recieved. Token must be 64 alphanumeric characters.'
            );
            die(json_encode($ret));
        }
        $device = UserDevice::where(['user_id'=>$id,'device_token'=>$token])->first();
        if(is_null($device)){
        	$ret = array(
              "success"=>false,
              "msg"=>"The given token ({$token}) was not found in the database for user ({$id}). No changes were made."
            );
            die(json_encode($ret));
        }
        else{
        	$success = $this->deactivate_token($id,$token);
        	$ret = array(
	          "success"=>$success
	        );
	        die(json_encode($ret));
        }
    }

    public function deactivate_token($id,$token){
    	$device = UserDevice::where(['user_id'=>$id,'device_token'=>$token])->first();
    	$endpoint_arn = $device->aws_endpoint_arn;
    	$subscription_arn = $device->aws_subscription_arn;
    	$aws_controller = new AwsController;
    	$remove_subscription = $aws_controller->unsubscribe(false,$subscription_arn);
    	$remove_subscription_status = $remove_subscription['@metadata']['statusCode'];
    	//if($remove_subscription_status === 200){
    		//successfully removes subscription
    	//}
    	$remove_endpoint = $aws_controller->deleteEndpoint(false,$endpoint_arn);
    	$remove_endpoint_status = $remove_endpoint['@metadata']['statusCode'];
    	//if($remove_endpoint_status === 200){
    		//successfully removed endpoint.
    	//}
    	$device->aws_endpoint_arn = '';
    	$device->aws_subscription_arn = '';
    	$saved = $device->save();
    	return (($remove_endpoint_status === 200) && ($remove_subscription_status === 200) && ($saved));
    }
}
