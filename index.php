<?php
/**
 * Created by PhpStorm.
 * User: Lena
 * Date: 17.7.2017.
 * Time: 13:50
 */
@session_start();
define("ROOT", $_SERVER["DOCUMENT_ROOT"] . "/AiR/MyGuideWebServices");
//var_dump($_GET["path"]);
date_default_timezone_set('Europe/Zagreb');

$pathParts=explode("/", $_GET["path"]);

$controller=$pathParts[sizeof($pathParts)-2];
$action=$pathParts[sizeof($pathParts)-1];


require ROOT."/core/Controller.php";
require ROOT."/core/ClassDatabase.php";
require ROOT."/classes/class.phpmailer.php";
require ROOT."/classes/class.pop3.php";
require ROOT."/classes/class.smtp.php";
require ROOT."/classes/".$controller.".php";

$instance=new $controller;
/* @var $instance Controller */

//$instance->getUserByAuth();


$instance->$action();
$instance->outputResponse();
