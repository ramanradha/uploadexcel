<?php
require_once './vendor/autoload.php';
require_once 'PHPExcellib/Classes/PHPExcel/Reader/Excel2007.php';
use Kreait\Firebase\Auth;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
include_once 'config.php';
include_once 'Electronics.php';
?> 
<html lang="en">
<head>
  <title>Product Uploads</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
  <h2>Product Uploads</h2>
   
  <table class="table" id="producttable">
    <thead>
      <tr>
        <th>Id</th>
        <th>Description</th>
        <th>Image</th>
		<th>IsDiscounted</th>
		<th>Name</th>
		<th>DiscountedPrie</th>
		<th>NumberOfItemsSelected</th>
		<th>Price</th>
		<th>PriceId</th>
		<th>ProdPriceId</th>
		<th>Quantity</th>
		<th>Seniority</th>
		<th>TotalAmount</th>
		<th>Weight</th>
		<th>Visible</th>
      </tr>

<?php


if(isset($_POST["submit"])) 
{
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
	$inputFileName = basename($_FILES["fileToUpload"]["name"]);
	try
	{
		$inputFileType  =   PHPExcel_IOFactory::identify($inputFileName);
		$objReader      =   PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel    =   $objReader->load($inputFileName);
	}catch(Exception $e)
	{
		die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
	}
	$objPHPExcel->setActiveSheetIndex(0);
	$worksheet = $objPHPExcel->getSheet(0);
	$drawings = $objPHPExcel->getActiveSheet()->getDrawingCollection(); //gets image collection
	$lastRow = $worksheet->getHighestRow();
	$row = 2;
	$i = 0;
	$Id = 0;
	
	$description = '';
	$Image = '';
	$isDiscounted = false;
	$name = '';
	$discountedPrice = '';
	$numberOfItemsSelected = 0;
	$price = 0;
	$priceId = 0;
	$prodPriceId = 1;
	$quantity = 0;
	$seniority = 0;
	$totalAmt = 0;
	$weight = '';
	$visible = '';
	//echo "<table class=\"table table-sm\">";
	for ($row = 2; $row <= $lastRow; $row++) 
	{
			echo "<tr><td scope=\"row\">";
			$Id = $worksheet->getCell('A'.$row)->getValue();
			echo $Id;
			echo "</td><td>";
			$description = $worksheet->getCell('B'.$row)->getValue();
			echo $description;
			echo "</td><td>";
			
			 //echo "</td><td>";
			 foreach($drawings as $drawing)	
			 {
				$cellID = $drawing->getCoordinates();
			
				if($cellID == PHPExcel_Cell::stringFromColumnIndex(2).$row)
				{	
					if ($drawing instanceof PHPExcel_Worksheet_MemoryDrawing)
					{
						ob_start();
						call_user_func(
						$drawing->getRenderingFunction(),
						$drawing->getImageResource()
						);
						$imageContents = ob_get_contents();
						ob_end_clean();
						switch ($drawing->getMimeType())
						{
							case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_PNG :
							$extension = 'png';
							break;
							case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_GIF:
							$extension = 'gif';
							break;
							case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_JPEG :
							$extension = 'jpg';
							break;
						}
					} 
					else 
					{
						$zipReader = fopen($drawing->getPath(),'r');
						$imageContents = '';
						while (!feof($zipReader)) 
						{
							$imageContents .= fread($zipReader,1024);
						}
						fclose($zipReader);
						$extension = $drawing->getExtension();
					}

					//Now give your image a name and write it to your folder
				$image_name = 'image_'.$i.'.'.$extension;
				$i = $i + 1;
				//$row = $row + 1;
				file_put_contents('images/'.$image_name,$imageContents);
				
				$target = 'uploads/'.$image_name;
				$source = './images/'.$image_name;
				$fileContent = file_get_contents($source);
				$bucketName = 'khadigram-50273.appspot.com';
				$isSucceed = uploadFile($bucketName, $fileContent, $target, $customTokenString);
				if ($isSucceed <> 'failed') 
				{
					$elect = new Electronics();
					$postData = [
					'description' => $description,
					'image' => $isSucceed ,
					'isDiscounted' => $isDiscounted,
					'name' => $name,
					'prices' => [
					'discountedPrice' => $discountedPrice,
					'numberOfItemsSelected' => $numberOfItemsSelected,
					'price' => $price,
					'priceId' => $priceId,
					'prodPriceId' => $prodPriceId,
					'quantity' => $quantity,
					'seniority' => $seniority,
					'totalAmt' => $totalAmt,
					'weight' => $weight
					],
					'visible' => $visible
					];
					
					$newPostKey = $Id;
					$updates = [
						$newPostKey => $postData,
					];
					$elect->insert($updates);
					
				} else 
				{
					$response['code'] = "201";
					$response['msg'] = 'FAILED: to upload ' . $target . PHP_EOL;
				}
				
				
				
				
				echo "<img src='images/$image_name'  height='100px' width='100px' >";
				echo "</td><td>";
				$isDiscounted = $worksheet->getCell('D'.$row)->getValue();
				if($isDiscounted <> FALSE)
				{
					echo "TRUE";
				}
				else
				{
					echo "FALSE";
				}
				echo "</td><td>";
				$name = $worksheet->getCell('E'.$row)->getValue();
				echo $name;
				echo "</td><td>";
				$discountedPrice = $worksheet->getCell('F'.$row)->getValue();
				echo $discountedPrice;
				echo "</td><td>";
				$numberOfItemsSelected = $worksheet->getCell('G'.$row)->getValue();
				echo $numberOfItemsSelected;
				echo "</td><td>";
				$price = $worksheet->getCell('H'.$row)->getValue();
				echo $price;
				echo "</td><td>";
				$priceId = $worksheet->getCell('I'.$row)->getValue();
				echo $priceId;
				echo "</td><td>";
				$prodPriceId  = $worksheet->getCell('J'.$row)->getValue();
				echo $prodPriceId;
				echo "</td><td>";
				$quantity = $worksheet->getCell('K'.$row)->getValue();
				echo $quantity;
				echo "</td><td>";
				$seniority = $worksheet->getCell('L'.$row)->getValue();
				echo $seniority;
				echo "</td><td>";
				$totalAmt = $worksheet->getCell('M'.$row)->getValue();
				echo $totalAmt;
				echo "</td><td>";
				$weight = $worksheet->getCell('N'.$row)->getValue();
				echo $weight;
				echo "</td><td>";
				$visible = $worksheet->getCell('O'.$row)->getValue();
				echo $visible;
			
	
				echo "</td><tr>";
	 
				}
			}
	}
	
}

?>

</table>
</div>

</body>
</html>