<?php
//error_reporting(E_ALL | E_STRICT );
//@ini_set('display_errors', 'on');

/*
 * arquivo para chamar os metodos da classe RoboTeste
 */
require_once('RoboTestesFunctions.php');

crawling('urlHere', 4, 'checkLink');

