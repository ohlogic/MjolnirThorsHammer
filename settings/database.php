<?php
	$database="findthat_db";
	$pg_user = "postgres";
	$pg_password = "tomcat"; 
	$pg_host = "localhost";
	
	
	
	$db = pg_connect("host=$pg_host port=5432 dbname=$database user=$pg_user password=$pg_password");
	if (!$db)
		die ("<b>Cannot connect to database, check if username, password and host are correct.</b>");
	else {
		; //echo 'postgres CONNECT SUCCESS' . '<br>';
	}
	

?>