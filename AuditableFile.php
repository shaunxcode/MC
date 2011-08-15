<?php

class AuditableFile {
	protected $content;
	protected $fileName;
	
	private function delim() {
		return "\n~~######~~" . date('Y-m-d H:i:s', time()) . "~~######~~\n";
	}
	
	public function __construct($fileName) {
		$this->fileName = $fileName;
		$file = MC::$dataDir . '/' . $fileName;
		if(!file_exists($file)) {
			touch($file);
		}
		$this->content = file_get_contents($file);
	}
	 
	public function update($content) {
		file_put_contents(
			MC::$dataDir . '/history/' . $this->fileName, 
			$this->content . $this->delim(), 
			FILE_APPEND);
		
		file_put_contents(
			MC::$dataDir . '/' . $this->fileName, 
			$content); 
		
		$this->content = $content;
		return $this;
	}
	
	public function getHistory() {
		
	}
	
	public static function save($fileName, $content) {
		$instance = new AuditableFile($fileName);
		return $instance->update($content);
	}
}