<?php
session_start();

$config = json_decode(file_get_contents('../config.json')); 

$path = strtolower(isset($_GET['path']) ? $_GET['path'] : $config->defaultRoute); 

if(!isset($config->routes->$path)) {
    $path = $config->notFoundRoute;
}

extract((array)$config->vars);

class MC { 
    public static $content;
}

$contentFile = '../content/' . $path . '.json'; 
if(!file_exists($contentFile)) {
    file_put_Contents($contentFile, '{}');
}

MC::$content = json_decode(file_get_contents($contentFile)); 

function section($name) {
    if(isset(MC::$content->$name)) {
        echo MC::$content->$name;
    } else {
        MC::$content->$name = '';
        echo '[no content yet]';
    }
}

extract((array)MC::$content);

include '../header.inc.php';

$templateFile = '../template/' . $config->routes->$path . '.inc.php';

if(!file_exists($templateFile)) {
    touch($templateFile);
}

include $templateFile; 

if(isset($_SESSION['admin'])) {
    include '../admin.inc.php';
}

include '../footer.inc.php';
