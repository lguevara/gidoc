<?php
// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')); /* '/../' regresa a la ruta padre  */

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        APPLICATION_PATH .'/configs',
        APPLICATION_PATH .'/modules',
        get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';  

/* Para detectar el dominio al que pertenece */
$direccion = $_SERVER['SERVER_NAME'];
define('MY_DOMINIO',$direccion);

$fileApplicationIni = 'application_'.$direccion.'.ini';    

/**/

// Create application, bootstrap, and run
try{
    $application = new Zend_Application(
            APPLICATION_ENV, $fileApplicationIni
    );
}catch(Exception $e){
    die('Error en arranque del sistema. <br>'.$e->getMessage());
}

$application->bootstrap()
        ->run();