<?php

	header("X-XSS-Protection: 1; mode=block");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    //ini_set("session.cookie_secure", 1);
    ini_set('display_errors', 0);
    ini_set("session.cookie_httponly", 1);
    session_start();
    ini_set('max_execution_time', 0);
    date_default_timezone_set('America/Bogota');

	include_once 'src/globvars.php'; // Variables globales
    include_once 'src/firewall.class.php'; // Firewall de la aplicación
	include_once 'src/cleanstr.class.php'; // Limpieza de cadenas para evitar XSS
	include_once 'src/crud.class.php'; // Operaciones CRUD
    include_once 'src/render.class.php'; // Render de tablas y otros elementos    
    include_once 'src/filemanager.class.php'; // Render de tablas y otros elementos    
    include_once 'src/phpmailer/class.phpmailer.php'; // PHPMailer Library
    include_once 'src/mailengine.class.php'; // Motor simplificado de correos
	include_once 'src/app.php'; // Render de tablas y otros elementos*/

    $res = array(
        'cleanstr'      =>  new Cleanstr(),
        'crud'          =>  new Crud(),
        'render'        =>  new Render(),
        'fileman'		=>	new Filemanager(),
        'mailengine'    =>  new mailEngine(),
        'models'        =>  array('inicio','fileloader','lstform','recopass'),
        'methods'       =>  array('index','logos','logout','prueba','toexcel','chng'),
        'error_config'  =>  array('display_errors'=>0,'log_errors'=>1,'file_log'=>'apperror.log'),
        'ini_model'     =>  'ini'
    );

    // Setup App & Run App
    $eva = new App($res);
    $eva->run();

?>