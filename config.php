<?php
	$adminUid = 2;
	$host        = "host = 127.0.0.1";
	$dbname      = "dbname = BUSY";
	$credentials = "user = postgres password=secret";

	$db = pg_connect( "$host $dbname $credentials"  );
	if(!$db){
		echo  'Critical Error: Unable to access database';
		exit();
	}
?>