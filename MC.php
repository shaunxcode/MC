<?php

define('DIR_CONTENT', 'content/');
define('DIR_TEMPLATE', 'template/');
define('DIR_STYLE', 'style/');
define('DIR_SCRIPT', 'script/');

require 'AuditableFile.php';
require 'JSONAuditableFile.php';

class MC { 
	public static $dataDir = 'data';
	public static $config;
	public static $path;
    public static $content;
	public static $siteConfig;
	public static $pageConfig;
	
	public static function section($name) {
		if(isset(self::$content->$name)) {
			echo self::$content->$name;
		} else {
			self::$content->$name = '';
			echo '[no content yet]';
		}	
	}
	
	public static function val($name) {
		
	}
	
	public static function widget($name, $options = array()) {
		
	}
	
	public static function renderTemplate($template) {
		extract((array)self::$config->vars);
		$config = self::$config;
		extract((array)self::$content);
		$path = self::$path; 
		
		$fileName = self::$dataDir . '/' . $template;
		
		if(!file_exists($fileName)) {
			touch($fileName);
		}
		
		include $fileName;
	}

	public static function partyPeopleInThePlaceToBe() { 
		self::$config = JSONAuditableFile::getData('config');

		self::$path = strtolower(isset($_GET['path']) ? $_GET['path'] : self::$config->defaultRoute); 

		if(!isset(self::$config->routes->{self::$path})) {
		    self::$path = self::$config->notFoundRoute;
		}

		self::$content = JSONAuditableFile::getData(DIR_CONTENT . self::$path);

		self::renderTemplate('header.inc.php');

		self::renderTemplate(DIR_TEMPLATE . self::$config->routes->{self::$path} . '.inc.php');

		if(isset($_SESSION['admin'])) {
		    include '../admin.inc.php';
		}

		self::renderTemplate('footer.inc.php');	
	}
	
	public static function getTemplates() {
		$templates = array();
	    foreach(glob(MC::$dataDir . '/' . DIR_TEMPLATE . '*.inc.php') as $template) {
	        $last = explode('/', $template);
	        $templates[] = current(explode('.', end($last))); 
	    }
		return $templates;
	}
}

function section($name) {
	return MC::section($name);
}