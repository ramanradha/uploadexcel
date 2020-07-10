<?php
 
// load GCS library
require_once 'vendor/autoload.php';
 
use Google\Cloud\Storage\StorageClient;
 
// Please use your own private key (JSON file content) which was downloaded in step 3 and copy it here
// your private key JSON structure should be similar like dummy value below.
// WARNING: this is only for QUICK TESTING to verify whether private key is valid (working) or not.  
// NOTE: to create private key JSON file: https://console.cloud.google.com/apis/credentials  
$privateKeyFileContent = '{
  "type": "service_account",
  "project_id": "khadigram-50273",
  "private_key_id": "55ac6bf0862f761b9f9dee9ab9acd6eb307d5987",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCluhnjh00M5fbx\nzeXn3GeLAMekRH3XDKFie/YYjjjlKK8ADSnCr4WzBSOWcXLACu8eP0fy8X3C67CV\nCyvptZ5QO2xac1cysaf3BM0NdmtOmVoBvDxo3h4/lYS/jgzGbk4PCT8lqNYgjsdg\nyMbhhQVOyIQuLbtuzEsmSwbL7ib1ot5FiNee7Lwc7eP5nG1ly3JxACSru/6YCp+t\nBphsDQ0ROL3B/bLktJjUMtXn0tvP2Nm8INczahYVt3aVaCvZYEj29ENB0FsK9egz\n1xL9pFg3jspEAVVnbXxG1CMCRJqwDJ/jb2EuzObUkqSd7Am8XYBFj5u6TbBADalP\n03y++cJFAgMBAAECggEABq5T8No5fvWeWO0mAYzfF7l2Uub3871snB3HNAI3xPU4\n6NWfA1w0FZ11a9EzAQ7vyNZgPikCGuOJgNiscoWB1lYu7g6MS1ziGpE0+v/1H3wL\nJtrBzLB3o7SRX2+IkE9OEkkbJJeD2lgnLX2qltjX9+8ZdQse7T6LIQYRRhNZurk/\nhRqkX1hyztn80J5VyKqUujuVCaQCIvgy48Jkspv0iN5Rfwhwmny5SoNrc+YiwlPZ\ntvXrNJKjzBe3znQSG9wzRf3XBN2CgIsSWLDVF7OiRvs7CcbEzfo9O394GyueFUhI\nCvqgcJ+IO/+ygazyCDjbtQ+Fzc/qLVVs1l+n2ufhWQKBgQDPTWIeY50oju/NYPib\nOCMzhUMQCYWgBI+5g8wV+7IQGYM+LXvX5yVpfm79w1OenbGvHxNQUaBA9WQ/0eI7\nAKtqaWxJjo66zTLfvZk6g0KV6rm6/kSkp8hSc/C9+9NLCL+3XzG81uX89t64IZAu\nUUneXvQYTo9SuIjQms30D65HzwKBgQDMqHyf2JdDx6pm9iFcD8ceF0/aLv+MN2Vm\n1+e3P0M8EG6I8evlK/Dguql95reRpQNPjJ2xRmG65K8U1V0wnGg2IOZBJC+qHppA\nGnal2LA/PJnzdmXQpu6v9CcRDQ7v1TGmHgwWXbfuwZwJzylOOWEoA10HQ6fi7nYa\nCOLSqLtFqwKBgAZmJhw4CPbiiqZ1Utc/wV5qw6owEQ7idSlN1zPqhBGZKAL4VnVQ\nrkf2Xii5KdCgn4Z/WBJHosG+kWyi5u/ZUnFDddNDckZz5Rkg+iTjPl/wUfFive2z\nPCzHZWwH5PrQ91IKvzdMDudjG3blmlTDr5sMpU5GxofQKHEABlF9VmzjAoGAVzNj\nBmiu6v5kKMKurB885CFisMBdukzQM+XCoV2fNDR8JHWP4XG7jMV7+l0X0kLRClAL\n0MEAWzWdM+9FabT64jVaZl8YwA+SAcZEz51oW0li/01vTFwUT3xkOOurdZ0NLo6W\n7+C2wlBUQAm4u8/PCdGFDddR7WtVfvpSSxgB00kCgYBE26/Hob4ChKiIxMLRn9v4\nd+5g/6//f6/Gu+ns+lUTddsWWyZxzw1OTqjIzQhsYP4bK78/npyiIijbDxsT3cJk\nlFxrPCMZgx7hcyjU3hTMW5La8lwbzPrF5xqrBBfNSYKWRO6qYSj7SFmCqVZAlp8r\n4b4gl20OMbxAdVMDBN1tsw==\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-o0i08@khadigram-50273.iam.gserviceaccount.com",
  "client_id": "109210852620689644946",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-o0i08%40khadigram-50273.iam.gserviceaccount.com"
    }';
 
/*
 * NOTE: if the server is a shared hosting by third party company then private key should not be stored as a file,
 * may be better to encrypt the private key value then store the 'encrypted private key' value as string in database,
 * so every time before use the private key we can get a user-input (from UI) to get password to decrypt it.
 */
 
function uploadFile($bucketName, $fileContent, $cloudPath,$accesstoken) {
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
 
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
 
    // upload/replace file 
    $storageObject = $bucket->upload(
            $fileContent,
            ['name' => $cloudPath,
			'contentType' => 'image/jpeg',
			'firebaseStorageDownloadTokens' => $accesstoken,]
     );
	/*$storageObject = $bucket->object($cloudPath);
	$storageObject->update(
    ['acl' => []],
    ['predefinedAcl' => 'PUBLICREAD']
	);*/
	$expiresAt = new \DateTime('tomorrow');

	$surl = $storageObject->signedUrl($expiresAt);

	if($storageObject != null) {
		return $surl;
	} else {
		 return 'failed';
	}
 
    // is it succeed ?
   
}
 
function getFileInfo($bucketName, $cloudPath) {
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
 
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    return $object->info();
}
//this (listFiles) method not used in this example but you may use according to your need 
function listFiles($bucket, $directory = null) {
 
    if ($directory == null) {
        // list all files
        $objects = $bucket->objects();
    } else {
        // list all files within a directory (sub-directory)
        $options = array('prefix' => $directory);
        $objects = $bucket->objects($options);
    }
 
    foreach ($objects as $object) {
        print $object->name() . PHP_EOL;
        // NOTE: if $object->name() ends with '/' then it is a 'folder'
    }
}