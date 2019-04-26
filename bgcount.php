<?php
	/*
	 * count number of notes per notebook as a callback
	 */
	
	global $vars, $lastError;
	require_once('functionsEZ.php');
	bootstrap();
	
	//leave if caching
	if ($_SESSION['caching'])
		return;
	
	//gracefully fail
	if (!isset($_GET['guid'])) {
		header('Content-Type: text/javascript; charset=utf-8'); //http://stackoverflow.com/questions/267546/correct-http-header-for-json-file
		print json_encode(
				array(
						"guid"=>0,
						"count"=>0,
				));
		exit();
	}

	$g = $_GET['guid'];
	$s = getNoteStore($g);
	$c = getNoteBookCount($g);
	$out = ""; //output

	header('Content-Type: text/javascript; charset=utf-8'); //http://stackoverflow.com/questions/267546/correct-http-header-for-json-file
	print json_encode(
			array(
					"guid"=>$g,
					"count"=>$c,
			));
?>
			  
