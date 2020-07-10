
<?php

require_once './PhpSpreadsheetmaster/src/PhpSpreadsheet/Cell/Coordinate.php';
require_once './PhpSpreadsheetmaster/src/PhpSpreadsheet/IOFactory.php';

$imageFilePath = './images/'; //Path of picture local storage
if (!file_exists($imageFilePath)) { //Recursively create if directory does not exist
    mkdir($imageFilePath, 0777, true);
}

try {
    $inputFileName = 'customer.xlsx';  //Excel file containing pictures
    $objRead = IOFactory::createReader('Xlsx');
    $objSpreadsheet = $objRead->load($inputFileName);
    $objWorksheet = $objSpreadsheet->getSheet(0);
    $data = $objWorksheet->toArray();

    foreach ($objWorksheet->getDrawingCollection() as $drawing) {
        list($startColumn, $startRow) = Coordinate::coordinateFromString($drawing->getCoordinates());
        $imageFileName = $drawing->getCoordinates() . mt_rand(1000, 9999);

        switch ($drawing->getExtension()) {
            case 'jpg':
            case 'jpeg':
                $imageFileName .= '.jpg';
                $source = imagecreatefromjpeg($drawing->getPath());
                imagejpeg($source, $imageFilePath . $imageFileName);
                break;
            case 'gif':
                $imageFileName .= '.gif';
                $source = imagecreatefromgif($drawing->getPath());
                imagegif($source, $imageFilePath . $imageFileName);
                break;
            case 'png':
                $imageFileName .= '.png';
                $source = imagecreatefrompng($drawing->getPath());
                imagepng($source, $imageFilePath, $imageFileName);
                break;
        }
        $startColumn = ABC2decimal($startColumn);
        $data[$startRow-1][$startColumn] = $imageFilePath . $imageFileName;
    }
    dump($data);die();
} catch (\Exception $e) {
    throw $e;
}

function ABC2decimal($abc)
{
    $ten = 0;
    $len = strlen($abc);
    for($i=1;$i<=$len;$i++){
        $char = substr($abc,0-$i,1);//Get single character in reverse

        $int = ord($char);
        $ten += ($int-65)*pow(26,$i-1);
    }
    return $ten;
}