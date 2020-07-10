<?php

require_once 'PHPExcellib/Classes/PHPExcel/Reader/Excel2007.php';
if(isset($_POST["submit"])) {
	//$inputFileName = 'customer.xlsx';
	$inputFileName = basename($_FILES["fileToUpload"]["name"]);

	try{
		$inputFileType  =   PHPExcel_IOFactory::identify($inputFileName);
		$objReader      =   PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel    =   $objReader->load($inputFileName);
	}catch(Exception $e){
		die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
	}
	$objPHPExcel->setActiveSheetIndex(0);
	
	$worksheet = $objPHPExcel->getSheet(0);

	$drawings = $objPHPExcel->getActiveSheet()->getDrawingCollection(); //gets image collection
	
	$lastRow = $worksheet->getHighestRow();
	$row = 2;
	$i = 0;
	
		
			
			
		
	

	echo "<table class=\"table table-sm\">";
	for ($row = 2; $row <= $lastRow; $row++) {
			echo "<tr><td scope=\"row\">";
			 echo $worksheet->getCell('A'.$row)->getValue();
			 echo "</td><td>";
			 echo $worksheet->getCell('B'.$row)->getValue();
			 echo "</td><td>";
			 echo $worksheet->getCell('C'.$row)->getValue();
			 echo "</td><td>";
			//echo PHPExcel_Cell::stringFromColumnIndex(4).$row;	
			foreach($drawings as $drawing)	{
		
		
			  
			$cellID = $drawing->getCoordinates();
			
			if($cellID == PHPExcel_Cell::stringFromColumnIndex(3).$row){	
			if ($drawing instanceof PHPExcel_Worksheet_MemoryDrawing){
			
			ob_start();
			call_user_func(
			$drawing->getRenderingFunction(),
			$drawing->getImageResource()
			);
			$imageContents = ob_get_contents();
			ob_end_clean();
			switch ($drawing->getMimeType()){
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
		} else {
			$zipReader = fopen($drawing->getPath(),'r');
			$imageContents = '';
			while (!feof($zipReader)) {
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
	echo "<img src='images/$image_name'  height='100px' width='100px' >";
	
	 echo "</td><tr>";
	 
	}
	}
	}
		echo "</table>";	
}