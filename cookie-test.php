<?php 	
	//cookie check
	//http://www.developertutorials.com/tutorials/php/articlename-050526-1149/
	//error_reporting (E_ALL ^ E_WARNING ^ E_NOTICE);
		
	// Check if cookie has been set or not
	if ($_GET['set'] != 'yes') {
		// Set cookie
		setcookie ('test', 'test', time() + 60);
	
		// Reload page
		header ("Location: cookie-test.php?set=yes");
	} else {
		// Check if cookie exists
		if (!empty($_COOKIE['test'])) {
			header("Location: /login");
			exit();
		} else {
			header('Location: no-cookies.php');
			exit();
		}
	}



?>