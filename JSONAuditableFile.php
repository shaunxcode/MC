<?php

class JSONAuditableFile extends AuditableFile {
	public $data; 
	
	public function __construct($fileName) {
		parent::__construct($fileName . '.json');
		$this->data = empty($this->content) ? (object)array() : json_decode($this->content);
	}

	public function update($data = array()) {
		return parent::update(json_encode($this->data));
	}

	public static function getData($fileName) {
		$file = new JSONAuditableFile($fileName);
		
		return $file->data;
	}
}