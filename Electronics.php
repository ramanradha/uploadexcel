<?php

require_once './vendor/autoload.php';

use Kreait\Firebase\Auth;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Factory;

class Electronics {
	
	protected $database;
	protected $dbname = 'radha_products/Electronics/Electronics';
	
	public function __construct(){
		$acc = ServiceAccount::fromJsonFile('./secret/khadigram-50273-55ac6bf0862f.json');
		$firebase = (new Factory())
			->withServiceAccount($acc)
			->create();
		$this->database = $firebase->getDatabase();
		
		
	}
	
	public function get($pid = NULL){
		if(empty($pid) || !isset($pid)) { return FALSE; }
		if($this->database->getReference($this->dbname)->getSnapshot()->hasChild($pid)) {
			return $this->database->getReference($this->dbname)->getChild($pid)->getValue();
		} else {
			return FALSE;
		}
		
	}
	
	public function insert(array $data ) {
		if(empty($data) || !isset($data)) { return FALSE; }
		//$db->getReference('Products/Electronics')->set($data);
		$this->database->getReference($this->dbname)->update($data);
	}
	
	public function delete($pid = NULL){
		if(empty($pid) || !isset($pid)) { return FALSE; }
		if($this->database->getReference($this->dbname)->getSnapshot()->hasChild($pid)) {
			return $this->database->getReference($this->dbname)->getChild($pid)->remove();
		} else {
			return FALSE;
		}
		
	}
}

?>