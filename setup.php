<?php

require 'MC.php';

mkdir('www/' . MC::$dataDir);
chmod('www/' . MC::$dataDir, 0777);
	
foreach(array(
	'history', 
	'content', 
	'template', 
	'style', 
	'script', 
	'history/content', 
	'history/template', 
	'history/style', 
	'history/script') as $dir) {
	mkdir('www/' . MC::$dataDir . '/' . $dir);
	chmod('www/' . MC::$dataDir, 0777);
}

file_put_contents('www/' . MC::$dataDir . '/config.json', json_encode(array(
	'vars' => array(
		'title' => $argv[1]),
	'routes' => array(
		'home' => 'default',
		'404' => 'default'),
	'defaultRoute' => 'home',
	'notFoundRoute' => '404')));

touch('www/' . MC::$dataDir . '/style/site.css');

file_put_contents('www/data/header.inc.php', '<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?></title> 
    <link rel="stylesheet" 
        href="' . MC::$dataDir . '/style/site.css" 
        type="text/css" media="all" />
</head>
<body>
<div class="container">
    <div id="header"><h1>Header</h1></div>');

file_put_contents('www/data/footer.inc.php', '    <div id="footer">Footer</div>
</div>
</body>
</html>');