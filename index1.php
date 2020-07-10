<?php

require_once './vendor/autoload.php';

use Kreait\Firebase\Auth;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;


include_once 'config.php';
include_once 'Electronics.php';


$serviceAccount = ServiceAccount::fromJsonFile('./secret/khadigram-50273-55ac6bf0862f.json');
$firebase = (new Factory())->withServiceAccount($serviceAccount)->create();
$database = $firebase->getDatabase();
$storage = $firebase->getStorage();
$auth = (new Factory())->withServiceAccount($serviceAccount)->createAuth();
$uid = 'some-uid';
$additionalClaims = [
    'premiumAccount' => true
];

$customToken = $auth->createCustomToken($uid, $additionalClaims);

$customTokenString = (string) $customToken;

//echo $customTokenString ;
//die(print_r($database));

/*$clientEmail = 'khadigram-50273@appspot.gserviceaccount.com';
$privateKey = file_get_contents('./secret/khadigram-50273-fc92d95cc888.json');
$generator = CustomTokenGenerator::withClientEmailAndPrivateKey($clientEmail, $privateKey);
$token = $generator->createCustomToken('uid', ['first_claim' => 'first_value' ]);*/



		$target = 'uploads/sample4.jpeg';
		$source = './images/photo1.jpg';
		$fileContent = file_get_contents($source);
		//echo $fileContent;
		$bucketName = 'khadigram-50273.appspot.com';
		/*$bucket = $storage->getBucket($bucketName);
		$isPublic = true;
		
		$uploadOptions = array_filter([
            'name' => $target,
            'predefinedAcl' => $isPublic ? 'publicRead' : null,
        ]);*/
		 $isSucceed = uploadFile($bucketName, $fileContent, $target, $customTokenString);
		  if ($isSucceed == true) {
            $response['msg'] = 'SUCCESS: to upload ' . $target . PHP_EOL;
            // TEST: get object detail (filesize, contentType, updated [date], etc.)
            $response['data'] = getFileInfo($bucketName, $target);
        } else {
            $response['code'] = "201";
            $response['msg'] = 'FAILED: to upload ' . $target . PHP_EOL;
        }
		
		header("Content-Type:application/json");
		echo json_encode($response);

/*$elect = new Electronics();
$postData = [
       'description' => '',
	   'image' => 'https://firebasestorage.googleapis.com/v0/b/mydoodhwalas-deb7a.appspot.com/o/image%2F1573623639666.png?alt=media&token=53d79637-3790-4695-b0c3-6483af9fce51',
	   'isDiscounted' => 'false',
	   'name' => 'abcd',
       'prices' => [
           'discountedPrice' => '8',
           'numberOfItemsSelected' => '0',
		   'price' => '8',
		   'priceId' => '0',
		   'prodPriceId' => '0',
		   'quantity' => '1N',
		   'seniority' => '0',
		   'totalAmt' => '0',
		   'weight' => '1N'
       ],
       'visible' => 'false',
      ];
var_dump($elect->insert($postData));*/

//var_dump($elect->get(4000));