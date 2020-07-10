<?php

require_once 'PHPExcellib/Classes/PHPExcel/Reader/Excel5.php';

$objReader = new PHPExcel_Reader_Excel5();

$objPHPExcel = $objReader->load('customer.xls');
$objPHPExcel->setActiveSheetIndex(0);

$drawings = $objPHPExcel->getActiveSheet()->getDrawingCollection(); //gets image collection

$i = 0;

foreach($drawings as $drawing){

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

file_put_contents('images/'.$image_name,$imageContents);
}