<?php
	global $vars, $lastError;
	require_once('functionsEZ.php');
	bootstrap();
	
	//$g = htmlspecialchars($_SESSION['notebook']['guid']); //notebook guid
	//$t = $_SESSION['notebook']['total'];
	$g = htmlspecialchars($_GET['nbgd']);
	$d = htmlspecialchars($_GET['down']); //total downloaded cards
	//$s = getNoteStore();
	
	//get total cards available from vars or look it up -- TODO
	/* if (!isset($_GET['totl'])) {
		$t = getNoteBookCount($g);
	}else */
	$t = htmlspecialchars($_GET['totl']);

	$m = MAX_EVERNOTE_DOWNLOAD; //max requested download this number;
	
	$out = ""; //output
	
	//if downloaded the total already, stop
	if ($d==$t) {
		header('Content-Type: text/javascript; charset=utf-8'); //http://stackoverflow.com/questions/267546/correct-http-header-for-json-file
	    print json_encode(
   		array(
   				"out"=>"",
   				"down"=>$d,
   				"stop"=>TRUE, 
   			));
	    return;
	}else if ($t<$m) //if total cards is less than max requested, request the total
		$m=$t;
	//else if ($d<$m) //if first download hasn't run, re-request first page for simplicity (or run into offset problems)
		//$d = 0;
		
	try {
		//connect, filter, adjust, and store
		$s = getNoteStore($g);
		$n = getNotes($s,$g,$d,$m);
		$v = adjustNotes($n,$s);
		$c = extract($v); //count returned
	}catch (EDAMSystemException $e) {
		print_r($e);die();
  		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
  			$lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
  		} else {
  			$lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
  		}
	}    
  		
   foreach($cards as $card) {
   	$d++;
   	$out .= '<div class="collapse-me hidden card" id="card-'.$g.'-'.$d.'" data-role="collapsible" data-inset="true" >';
   	$out .=    "<h1 id='card-{$g}-{$d}Q'>{$card['Q']}</h1>";
   	$out .=    "<p class='answer' data-inset='true' id='card-{$g}-{$d}A'>{$card['A']}</p>";
   	$out .= "	<a class='hidden edit' id='card-{$g}-{$d}E' href='{$card['E']}'>Edit this</a>";
   	$out .= '</div>';
   	
   }
   
   header('Content-Type: text/javascript; charset=utf-8'); //http://stackoverflow.com/questions/267546/correct-http-header-for-json-file
   print json_encode(
   		array(
   				"out"=>$out,
   				"down"=>$d,
   				"stop"=>$t < $d, //if total is less than downloaded number
   			));
   
?>
			  
