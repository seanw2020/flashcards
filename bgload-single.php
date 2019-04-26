<?php
	global $vars, $lastError;
	require_once('functionsEZ.php');
	bootstrap();
	
	//setup
	$b = htmlspecialchars($_GET['nbgd']); //notebook guid
	$g = htmlspecialchars($_GET['guid']); //guid
	$d = htmlspecialchars($_GET['ccnm']); //current card number
	$out = ""; //output
	
	//connect, filter, adjust, and store
	$s = getNoteStore();
	$n = FALSE;
	$note = FALSE;
	$vars = array();
	$v = _adjustNotes($s, $n, $vars, $note, $g);
	$c = extract($v); //count returned
    
   foreach($cards as $card) {
   	$out .= '<div class="card" id="card-'.$b.'-'.$d.'" data-role="collapsible" data-inset="true" >';
   	$out .=    "<h1 id='card-{$g}-{$d}Q'>{$card['Q']}</h1>";
   	$out .=    "<p class='answer' data-inset='true' id='card-{$b}-{$d}A'>{$card['A']}</p>";
   	$out .= "	<a class='hidden edit' id='card-{$b}-{$d}E' href='{$card['E']}'>Edit this</a>";
   	$out .= '</div>';
   }
   
   header('Content-Type: text/javascript; charset=utf-8'); //http://stackoverflow.com/questions/267546/correct-http-header-for-json-file
   print json_encode(
   		array(
   				"out"=>$out,
   			));
   
?>
			  
