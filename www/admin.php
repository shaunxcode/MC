<?php
session_start();

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

function delim() {
    return "\n~~######~~" . date('Y-m-d H:i:s', time()) . "~~######~~\n";
}

if(isset($_POST['newRoute']) && isset($_POST['template'])) {
    file_put_contents('../history/config.json', file_get_contents('../config.json') . ",\n", FILE_APPEND);
    $config = json_decode(file_get_contents('../config.json'));
    $route = strtolower($_POST['newRoute']); 
    $config->routes->$route = $_POST['template'];
    file_put_contents('../content/' . $route . '.json', '{}'); 
    file_put_contents('../config.json', json_encode($config)); 
    die('RELOAD');
}

if(isset($_POST['template'])) {
    $file = $_POST['template'] . '.inc.php';
    file_put_contents('../history/template/' . $file, file_get_contents('../template/' . $file) . delim(), FILE_APPEND);
    file_put_contents('../template/' . $file, $_POST['value']); 
    die('RELOAD');
}

if(isset($_POST['stylesheet'])) { 
    $file = $_POST['stylesheet'] . '.css';
    file_put_contents('../history/www/style/' . $file, file_get_contents('style/' . $file) . delim(), FILE_APPEND);
    file_put_contents('style/' . $file, $_POST['value']); 
    die('RELOAD');
}

if(isset($_POST['config'])) {
    file_put_contents('../history/config.json', file_get_contents('../config.json') . ",\n", FILE_APPEND);
    file_put_contents('../config.json', json_encode($_POST['config']));
    die('RELOAD');
}


if(isset($_POST['path']) && isset($_POST['section']) && isset($_POST['value'])) {
    $path = $_POST['path'];
    $section = $_POST['section'];
    
    $config = json_decode(file_get_contents('../config.json'));

    if(isset($config->routes->$path)) {
        if($section == 'header' || $section == 'footer') {
            $file = $section . '.inc.php';
            file_put_contents(
                '../history/' . $file, 
                file_get_contents('../' . $file) . delim(), 
                FILE_APPEND);

            file_put_contents('../' . $file, $_POST['value']);
            echo 'RELOAD';
        } else {
            $file = 'content/' . $path . '.json';
            $content = file_get_contents('../' . $file); 
            
            file_put_contents(
                '../history/' . $file, 
                $content . ",\n", 
                FILE_APPEND);

            $current = json_decode($content); 
            $current->$section = $_POST['value'];

            file_put_contents('../' . $file, json_encode($current)); 
        }
    }

    die();
}

