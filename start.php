<?php
	global $vars;
	require_once('functionsEZ.php');
	bootstrap();
	listNotebooks();
	extract($vars);
?>

<html>
  <head>
    <title>EZ Flashcards</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <!-- load common (to mobile and pc) files -->
    <link rel="stylesheet" href="style-common.css" />
    <script src="lib/jquery/jquery.1.8.2.min.js"></script>
    <script src="nav-common.js"></script>
    
    <!-- load specific files -->
    <?php if (isMobile()) : ?>
    	<link rel="stylesheet" href="style-mobile.css" />
    	<script src="nav-mobile.js"></script>
    	<meta name="viewport" content="width=device-width, initial-scale=1"> 
		<link rel="stylesheet" href="lib/jquery/jquery.mobile-1.2.0.min.css" />
		<script src="lib/jquery/jquery.mobile-1.2.0.min.js"></script>
    <?php else: ?>
    	<link rel="stylesheet" href="style.css" />
    	<script src="nav.js"></script>
		<link rel="stylesheet" href="lib/jquery/jquery.mobile-1.2.0.min.css" />
		<script src="lib/jquery/jquery.mobile-1.2.0.min.js"></script>
    <?php endif; ?>
  </head>
  
  <body>
  <!-- common -->
 
  <!--  mobile -->
		<div data-role="page">
		  	<div data-role="header">
				<h1 id="heading"><?php print $book; ?></h1>
			</div>
			<div data-role="content">
			  <div class='hidden' id="total-notes"><?php print $totalNotes; ?></div>

			   <fieldset class="ui-grid-a">
			   	<div class="ui-block-a">
			   	  <input type="text" name="myname" id='find'>
			   	</div>
			    <div class="ui-block-b">
			      <input type="submit" data-type="button" id='submit' value="Submit">
			    </div>
			   </fieldset>
			   
			   
			   <div id='cards'>
			  
			  
			  <?php
			  	$i=0;
			  	foreach($cards as $card) {
			  		$i++;
			  		print ("<div class='hidden question' id='card{$i}Q'>{$card['Q']}</div>");
			  		print ("<div class='hidden answer' id='card{$i}A'>{$card['A']}</div>");
			  		print ("<div class='hidden edit' href='{$card['E']}' id='card{$i}E'>{$card['E']}</div>");
			  	} 
			  ?>
			  </div> <!-- cards -->
			  <div id="vert-spacer">&nbsp;</div>
				<div id="nav">
					<p>
					<a data-role="button" data-inline="true" id='prev' href="" accesskey="p"><</a> 
					<a data-role="button" data-inline="true" id="flip" href="" accesskey="f">↻</a> 
					<a data-role="button" data-inline="true" id="next" href="" accesskey="n">></a>
					<a data-role="button" data-inline="true" id="edit" href="" accesskey="e" target="_blank">e</a>
				 	<!-- <a data-role="button" data-inline="true" id="bigr" href="" accesskey="+">+</a>  -->
				 	<!-- <a data-role="button" data-inline="true" id="smlr" href="" accesskey="-">-</a> -->
					</p>
				</div>
			</div><!-- /content -->
		</div> <!-- /page -->
			

  <!-- not mobile -->
  
  <!-- common -->
	
  	
  

  </body>
</html>
