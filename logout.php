<?php
	require_once('functionsEZ.php');
	require_once('config.php');
	session_start();
	//bootstrap();
	
	//try to remove old sessions
	if (isset($_COOKIE['requestToken'])) {
		//check db
		$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
		$s=$m->prepare("DELETE FROM sess WHERE requestToken=?");
		$s->bind_param('s',$rt);
		$rt=$_COOKIE['requestToken'];
		$s->execute();
		$s->store_result();
		$n=$s->num_rows;
	}
	
	setcookie ("requestToken", "", time() - 3600); //set cookie in past so browser deletes it
	session_destroy();
	header('Location: /');
	exit();
?>