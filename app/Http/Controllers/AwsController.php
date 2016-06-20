<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Aws\Sns\SnsClient;
use Aws\S3\S3Client;

//######################DOCUMENTED AT http://docs.aws.amazon.com/aws-sdk-php/v3/api/ for SNS#####################

class AwsController extends Controller
{
	public function init(){
		$client = $this->getClient();
		//var_dump($this->listSubscriptionsByTopic($client,'arn:aws:sns:us-east-1:442701386251:Fanvault'));
		//var_dump($this->unsubscribe($client, 'arn:aws:sns:us-east-1:442701386251:Fanvault:76f9d2ea-69b1-4495-a8c8-2896fc3882c3'));
		//var_dump($this->subscribe($client,'arn:aws:sns:us-east-1:442701386251:Fanvault','arn:aws:sns:us-east-1:442701386251:endpoint/APNS_SANDBOX/Fanvault/fe48660e-f892-3248-8d26-784f86cb4668'));
		//topic arn - arn:aws:sns:us-east-1:442701386251:Fanvault
		//endpoint arn - arn:aws:sns:us-east-1:442701386251:endpoint/APNS_SANDBOX/Fanvault/fe48660e-f892-3248-8d26-784f86cb4668
		//phone token - 121604b65b75ac33e990efc873b647982c4f7c1f9faad2cc34b81a1cf03e33e3;
		//subscription arn - arn:aws:sns:us-east-1:442701386251:Fanvault:4100188f-c7b9-491c-a64c-bb5a74d7ba3d
		var_dump($this->publish($client,'Fanvault Test Message','arn:aws:sns:us-east-1:442701386251:Fanvault'));
	}
	//#############HELPER(S)##########################
	public $platformApplicationArn = 'arn:aws:sns:us-east-1:442701386251:app/APNS_SANDBOX/Fanvault';
	public function getClient(){
		$sharedConfig = [
		    'region'  => 'us-east-1',
		    'version' => 'latest'
		];
		return SnsClient::factory($sharedConfig);
	}
	//#############TOPICS############################
	public function listTopics($client = false, $nextToken = false){
		if($client === false){$client = $this->getClient();}
		$result = $client->listTopics([
		    'NextToken' => $nextToken !== false ? $nextToken : ''
		]);
		return $result->toArray();
	}
	public function getTopicAttributes($client = false,$topicArn = false){
		if($topicArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->getTopicAttributes([
		    'TopicArn' => $topicArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function setTopicAttributes($client = false, $topicArn = false, $attributeName = false, $attributeValue = false){
		if($topicArn === false || $attributeName === false || $attributeValue === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->setTopicAttributes([
		    'AttributeName' => $attributeName, // REQUIRED (Policy | DisplayName | DeliveryPolicy)
		    'AttributeValue' => $attributeValue,
		    'TopicArn' => $topicArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function createTopic($client = false, $name = false){
		if($name === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->createTopic([
		    'Name' => $name, // REQUIRED
		]);
		return $result->toArray();
	}
	public function deleteTopic($client = false, $topicArn = false){
		if($topicArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->deleteTopic([
		    'TopicArn' => $topicArn, // REQUIRED
		]);
		return $result->toArray();
	}
	//#############PLATFORMS############################
	/* this will always just be the one for Fanvault so none of this will ever need to be used.
	public function listPlatformApplications($client = false, $nextToken = false){	
		if($client === false){$client = $this->getClient();}
		$client = $this->getClient();
		$result = $client->listPlatformApplications([
		    'NextToken' => $nextToken !== false ? $nextToken : '',
		]);
		return $result->toArray();
	}
	public function getPlatformApplicationAttributes(){
		$result = $client->getPlatformApplicationAttributes([
		    'PlatformApplicationArn' => '<string>', // REQUIRED
		]);
	}
	public function setPlatformApplicationAttributes(){
		$result = $client->setPlatformApplicationAttributes([
		    'Attributes' => ['<string>', ''], // REQUIRED
		    'PlatformApplicationArn' => '<string>', // REQUIRED
		]);
	}
	public function deletePlatformApplication(){ 
		$result = $client->deletePlatformApplication([
		    'PlatformApplicationArn' => '<string>', // REQUIRED
		]);
	}
	*/
	//#############ENDPOINTS############################
	public function listEndpointsByPlatformApplication($client = false, $platformApplicationArn = false, $nextToken = false){
		if($client === false){$client = $this->getClient();}
		$result = $client->listEndpointsByPlatformApplication([
		    'NextToken' => $nextToken !== false ? $nextToken : '',
		    'PlatformApplicationArn' => $platformApplicationArn !== false ? $platformApplicationArn : $this->platformApplicationArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function createPlatformEndpoint($client = false, $token = false, $platformApplicationArn = false){
		if($token === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->createPlatformEndpoint([
		    /*'Attributes' => ['<string>', ...],	//( CustomUserData | Enabled | Token )
		    'CustomUserData' => '',*/
		    'PlatformApplicationArn' => $platformApplicationArn !== false ? $platformApplicationArn : $this->platformApplicationArn, // REQUIRED
		    'Token' => $token, // REQUIRED
		]);
		return $result->toArray();
	}
	public function getEndpointAttributes($client = false, $endpointArn = false){
		if($endpointArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->getEndpointAttributes([
		    'EndpointArn' => $endpointArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function setEndpointAttributes($client = false, $endpointArn = false, $enabled = false, $newToken = false, $customData = false){
		if($endpointArn === false || ($enabled === false && $newToken === false && $customData === false)){return false;}
		if($client === false){$client = $this->getClient();}
		$attr = array();
		if($enabled !== false){$attr['Enabled'] = $enabled;}
		if($newToken !== false){$attr['Token'] = $newToken;}
		if($customData !== false){$attr['CustomUserData'] = $customData;}
		$result = $client->setEndpointAttributes([
		    'Attributes' => $attr, // REQUIRED ( CustomUserData | Enabled | Token )
		    'EndpointArn' => $endpointArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function deleteEndpoint($client = false, $endpointArn = false){
		if($endpointArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->deleteEndpoint([
		    'EndpointArn' => $endpointArn, // REQUIRED
		]);
		return $result->toArray();
	}
	//#############SUBSCRIPTIONS############################
	public function listSubscriptions($client = false, $nextToken = false){
		if($client === false){$client = $this->getClient();}
		$result = $client->listSubscriptions([
		    'NextToken' => $nextToken !== false ? $nextToken : ''
		]);
		return $result->toArray();
	}
	public function listSubscriptionsByTopic($client = false, $topicArn = false, $nextToken = false){
		if($topicArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->listSubscriptionsByTopic([
		    'NextToken' => $nextToken !== false ? $nextToken : '',
		    'TopicArn' => $topicArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function getSubscriptionAttributes($client = false, $subscriptionArn = false){
		if($subscriptionArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->getSubscriptionAttributes([
		    'SubscriptionArn' => $subscriptionArn, // REQUIRED
		]);
		return $result->toArray();
	}
	//wont really need to set subscription attributes but its here...
	//I never tested this below because the delivery policy and raw message 
	//delivery is just not something that we need right now
	public function setSubscriptionAttributes($client = false, $subscriptionArn = false, $attributeName = false, $attributeValue = false){
		if($subscriptionArn === false || $attributeName === false || $attributeValue === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->setSubscriptionAttributes([
		    'AttributeName' => $attributeName, // REQUIRED (DeliveryPolicy | RawMessageDelivery)
		    'AttributeValue' => $attributeValue,
		    'SubscriptionArn' => $subscriptionArn, // REQUIRED
		]);
		return $result->toArray();
	}
	//I am not going to need the subscribe function. The subscribe function is going to run on the front end. Then once the 
	//user accepts to recieve notifications, then the front end will call the ConfirmSubscription endpoint.
	public function subscribe($client = false, $topicArn = false, $endpointArn = false){
		if($topicArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->subscribe([
		    'Endpoint' => $endpointArn !== false ? $endpointArn : '',
		    'Protocol' => 'application', // REQUIRED
		    'TopicArn' => $topicArn, // REQUIRED
		]);
		return $result->toArray();
	}
	//I think the function below will need to have request URI parameters and not php arguments. Will probably change this in future.
	public function confirmSubscription($client = false, $topicArn = false, $token = false){
		if($topicArn === false || $token === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->confirmSubscription([
		    //'AuthenticateOnUnsubscribe' => '<string>',
		    'Token' => $token, // REQUIRED
		    'TopicArn' => $topicArn, // REQUIRED
		]);
		return $result->toArray();
	}
	public function unsubscribe($client = false, $subscriptionArn = false){
		if($subscriptionArn === false){return false;}
		if($client === false){$client = $this->getClient();}
		$result = $client->unsubscribe([
		    'SubscriptionArn' => $subscriptionArn, // REQUIRED
		]);
		return $result->toArray();
	}
	//###################NOTIFICATIONS#########################
	public function publish($client = false, $message = false, $targetArn = false){
		if($message === false){return false;}
		if($client === false){$client = $this->getClient();}
		/*$result = $client->publish([
		    'Message' => $message, // REQUIRED
		    'MessageAttributes' => [
		        '<String>' => [
		            'BinaryValue' => '<string || resource || Psr\Http\Message\StreamInterface>',
		            'DataType' => '<string>', // REQUIRED
		            'StringValue' => '<string>',
		        ],
		        // ...
		    ],
		    //'MessageStructure' => 'json',
		    //'Subject' => '', subject is only for emails i guess
		    'TargetArn' => $targetArn !== false ? $targetArn : '',
		    'TopicArn' => $topicArn !== false ? $topicArn : '',
		]);
		*/
		//decided to just use target ARN and ignore topicARN param alltogehter.
		//targetArn allows you to pass topicARN or endpointArn so ther is no reason to use the last parameters
		//I thought you could pass an endpointARN into the targetARN and then a topicARN as well, but then it threw error to
		//use one or the other.
		$result = $client->publish([
		    'Message' => $message, // REQUIRED
		    'TargetArn' => $targetArn !== false ? $targetArn : ''	
		]);
		return $result->toArray();
	}
	//####################S3####################################
	/*
    Name: getGUID
    Description: Generates a unique name for the images that will be uploaded to amazon
    */
    function getGUID(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            /*$uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"*/
            $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
            return $uuid;
        }
    }
    /*
    Name: upload_image
    Description: uploads tailgate image to amazon s3
    */
	public function upload_image($image=false,$folder=false){
		if($image!==false && $folder!==false){
			$sharedConfig = [
			    'region'  => 'us-east-1',
			    'version' => 'latest'
			];
			$client = S3Client::factory($sharedConfig);
			$imageFileName = $this->getGUID() . '.' . $image->getClientOriginalExtension();
			$imageFilePath = $image->getPathName();
			$contentType = $image->getMimeType();
			try {
				$result = $client->putObject(array(
				    'Bucket'     => 'fanvaultapp',
				    'Key'        => $folder.'/'.$imageFileName,
				    'SourceFile' => $imageFilePath,
				    'ContentType'=>$contentType
				));
				return $result['ObjectURL'];
			}catch (S3Exception $e) {
				return false;
			}	
		}
		else{
			return false;
		}
	}
	/*
    Name: delete_aws_image
    Description: deletes image from amazon s3
    */
	public function delete_aws_image($url=false,$folder=false){
		if($url!==false && $folder!==false){
			try {
				$sharedConfig = [
				    'region'  => 'us-east-1',
				    'version' => 'latest'
				];
				$client = S3Client::factory($sharedConfig);
				$result = $client->deleteObject(array(
				    'Bucket'     => 'fanvaultapp',
				    'Key'        => $folder.'/'.urldecode($url),
				));
				return true;
			}catch (S3Exception $e) {
				return false;
			}
		}	
		else{
			return false;
		}
	}

	//i definitely could have combined this with upload_image, to just have one function do both
	//but I dont feel good today and am just being lazy for once.
	public function upload_venue_image($url = false){
		if($url!==false && $url!==''){
			$sharedConfig = [
			    'region'  => 'us-east-1',
			    'version' => 'latest'
			];
			$max_width = 750;
			$max_height = 750;
			$img = imagecreatefromstring(file_get_contents($url));
		    $width = imagesx( $img );
		    $height = imagesy( $img );
		    if ($width > $height) {
		        $newwidth = $max_width;
		        $divisor = $width / $max_width;
		        $newheight = floor( $height / $divisor);
		    }
		    else {
		        $newheight = $max_height;
		        $divisor = $height / $max_height;
		        $newwidth = floor( $width / $divisor );
		    }
		    // Create a new temporary image.
		    $tmpimg = imagecreatetruecolor( $newwidth, $newheight );
		    // Copy and resize old image into new image.
		    imagecopyresampled( $tmpimg, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );
		    // Save thumbnail into a file.
		    ob_start();
		    imagejpeg($tmpimg);
		    $jpeg_file_contents = ob_get_contents();
		    ob_end_clean();
		    // release the memory
		    imagedestroy($tmpimg);
		    imagedestroy($img);
		    $imageFileName = $this->getGUID() . '.jpg';
		    try {
				$sharedConfig = [
				    'region'  => 'us-east-1',
				    'version' => 'latest'
				];
				$client = S3Client::factory($sharedConfig);
				$result = $client->putObject(array(
			        'Bucket'    => 'fanvaultapp',
			        'Key'       => 'venues/'.$imageFileName,
			        'Body'      => $jpeg_file_contents,
			        'ContentType'=>'image/jpeg'
			    ));
				return $result['ObjectURL'];
			}catch (S3Exception $e) {
				return false;
			}
		}
		else{
			return false;
		}
	}

	public function upload_headshot_image($url = false, $name = false, $max_size = false){
		if($url!==false && $url!=='' && $name !== false && $name !== ''){
			$sharedConfig = [
			    'region'  => 'us-east-1',
			    'version' => 'latest'
			];
			$img = file_get_contents($url);
			if($max_size !== false){
				$img = imagecreatefromstring($img);
				$max_width = $max_size;
				$max_height = $max_size;
				$width = imagesx( $img );
			    $height = imagesy( $img );
			    if ($width > $height) {
			        $newwidth = $max_width;
			        $divisor = $width / $max_width;
			        $newheight = floor( $height / $divisor);
			    }
			    else {
			        $newheight = $max_height;
			        $divisor = $height / $max_height;
			        $newwidth = floor( $width / $divisor );
			    }
			    // Create a new temporary image.
			    $tmpimg = imagecreatetruecolor( $newwidth, $newheight );
			    // Copy and resize old image into new image.
			    imagecopyresampled( $tmpimg, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );
			    // Save thumbnail into a file.
			    ob_start();
			    imagejpeg($tmpimg);
			    $jpeg_file_contents = ob_get_contents();
			    ob_end_clean();
			    // release the memory
			    imagedestroy($tmpimg);
			    imagedestroy($img);
			    $img = $jpeg_file_contents;
			}
		    $imageFileName = $this->getGUID() . '_' . $name . '.jpg';
		    try {
				$sharedConfig = [
				    'region'  => 'us-east-1',
				    'version' => 'latest'
				];
				$client = S3Client::factory($sharedConfig);
				$result = $client->putObject(array(
			        'Bucket'    => 'fanvaultapp',
			        'Key'       => 'playerheadshots/'.$imageFileName,
			        'Body'      => $img,
			        'ContentType'=>'image/jpeg'
			    ));
				return $result['ObjectURL'];
			}catch (S3Exception $e) {
				return false;
			}
		}
		else{
			return false;
		}
	}
}
