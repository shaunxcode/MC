<?php
session_start();

require '../MC.php';

if(isset($_GET['magic']) && $_GET['magic'] == 'token') {
    $_SESSION['admin'] = true;
    header('location:/'); 
}

if(isset($_GET['logout'])) {
    unset($_SESSION['admin']);
}

if(!isset($_SESSION['admin'])) {
    header('location:/'); 
}


if(isset($_POST['newRoute']) && isset($_POST['template'])) {
	$route = strtolower($_POST['newRoute']);

	$config = new JSONAuditableFile('config');	
	$config->data->routes->$route = $_POST['template'];
	$config->update();

	$content = new JSONAuditableFile(DIR_CONTENT . $route);
	$content->update();
} 
else if(isset($_POST['template'])) {
	AuditableFile::save(DIR_TEMPLATE . $_POST['template'] . '.inc.php', $_POST['value']);
} 
else if(isset($_POST['stylesheet'])) { 
	AuditableFile::save(DIR_STYLE . $_POST['stylesheet'] . '.css', $_POST['value']);
} 
else if(isset($_POST['config'])) {
	$config = new JSONAuditableFile('config');
	$config->data = $_POST['config'];
	$config->update();
}
else if(isset($_POST['path']) && isset($_POST['section']) && isset($_POST['value'])) {
    $path = $_POST['path'];
    $section = $_POST['section'];
    $config = JSONAuditableFile::getData('config');

    if(isset($config->routes->$path)) {
        if($section == 'header' || $section == 'footer') {
			AuditableFile::save($section . '.inc.php', $_POST['value']);
        } else {
			$content = new JSONAuditableFile(DIR_CONTENT . $path);
			$content->data->$section = $_POST['value'];
			$content->update();
        }
    }
}