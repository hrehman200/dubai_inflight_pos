<?php

error_reporting( E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR );


/* Database config */
$db_host		= 'localhost';
$db_user		= 'root';
$db_pass		= 'haris_786';
$db_database	= 'sales'; 

/* End config */

$db = new PDO('mysql:host='.$db_host.';dbname='.$db_database, $db_user, $db_pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>