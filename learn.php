<?php
	require_once('functionsEZ.php');
	bootstrap();
	$g = $_GET['notebook']; //notebook guid
	$m = 5; // max: load this initially for speed - see bgloader for ajax loading after 5 
	
	//connect, filter, adjust, and store
	$s = getNoteStore($g);
	$n = getNotes($s,$g,0,$m);
	$v = adjustNotes($n,$s);
	$c = extract($v); //count returned
	$d = count($cards); //downloaded # of cards
	//$d = ($totalNotes < MAX_EVERNOTE_DOWNLOAD) ? $totalNotes : $m; //update d
	$p = ($totalNotes > 1) ? 'cards' : 'card'; //plural
	$w = isset($_COOKIE['swatch']) ? $_COOKIE['swatch'] : DEFAULT_SWATCH; //swatch
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en"  xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Learn </title>
    
    <!--  css -->
    <link rel="stylesheet" href="/style-common.css" />
    <link rel="stylesheet" href="/lib/jquery/jquery.mobile-1.2.0.min.css" />
    <?php if (isMobile()): ?>
    	<link rel="stylesheet" href="/style-mobile.css"/>
    <?php else: ?>
    	<link rel="stylesheet" href="/style.css"/>
    <?php endif; ?>
    
    <!-- <link rel="stylesheet" href="/themes/ez1.min.css" /> -->
    
    <!-- load common (to mobile and pc) files -->
    <script src="/lib/jquery/jquery.1.8.2.min.js"></script>
    <script src="/lib/jquery/jquery.cookie.js"></script>
    <script src="/nav-common.js"></script>
    
    
    <!-- custom stuff before jquery mobile activates -->
	<script src="/select-notebook.js"></script>
	<script src="/learn.js"></script>
	<script src="/settings.js"></script>
    
    <!--  jquery mobile goes last -->
	<script src="/lib/jquery/jquery.mobile-1.2.0.min.js"></script>    
    
  </head>
    
  
  <body>
  <?php include_once("lib/tracking/analyticstracking.php"); ?>
  <!-- common -->
 
  <!--  mobile -->
		<div data-role="page" data-dom-cache="true" id="learn-<?php print $g; ?>" data-theme="<?php print $w; ?>">
		 	<script type="text/javascript">
	            $("#learn-<?php print $g; ?>").live('pageinit', function() {
		            	ezflashcards.vars['nbgd'] = "<?php print $g; ?>" //set the notebook guid for javascript
	            		ezflashcards.learnSetup();
		            	$('#learn-<?php print $g;?>').find('.ui-input-text').focus(function() {
		            		  $('#learn-<?php print $g; ?>').find('#cardset-<?php print $g; ?>').children().removeClass("hidden");
		            		});
		            	ezflashcards.vars['thme'] = "<?php print $w; ?>"
		            	ezflashcards.learnUpdateSize();
	            	});
	            $("#learn-<?php print $g; ?>").live('pageshow', function(event, ui) {
	            	ezflashcards.vars['nbgd'] = "<?php print $g; ?>" //set the notebook guid for javascript
	            	ezflashcards.learnLoadMore( "<?php print $g; ?>");
	            	ezflashcards.settingsChangeTheme(ezflashcards.vars['thme']);
	            	$(this).focus();
            	});
	        </script>

		  	<div data-role="header" data-position="fixed" data-tap-toggle="false">
				<a href="/select-notebook" data-icon="home" data-iconpos="left" >Home</a>
				
				<h1>
					<span id="header-learn-<?php print $g; ?>"><?php print '1/' . $m; ?></span>
					<span id="notebook-<?php print $g; ?>"><?php print trim($_SESSION['notebook']['name'] . ':' . $totalNotes .' '. $p); ?></span>
				</h1>
				<a href="/settings" data-prefetch data-iconpos="left" data-icon="gear" data-rel="dialog">Options</a>
			</div>
			
			<div data-role="content">
			  <div id="cardset-wrapper">
				  <ul class="cardset" id="cardset-<?php print $g; ?>" data-role="listview" data-filter="true" data-tap-toggle="false">
					  <?php
					  	$i=0;
					  	foreach($cards as $card) {
					  		$i++;
					  		print ('<div class="hidden card" id="card-'.$g.'-'.$i.'" data-inset="true" data-role="collapsible" >');
					  		print ("  <h1 id='card-{$g}-{$i}Q'>{$card['Q']}</h1>");
					  		print ("    <p class='answer' data-inset='true' id='card-{$g}-{$i}A'>{$card['A']}</p>");
					  		print ("	<a class='hidden edit' id='card-{$g}-{$i}E' href='{$card['E']}' >Edit this</a>");
					  		print ('</div>');
					  	}
					  ?>
				</ul>
			  </div> <!-- cardset wrapper -->
			  
			  <!-- hidden divs -->
			  <div class='hidden' id="total-notes-<?php print $g; ?>"><?php print $totalNotes; ?></div>
			  <div class='hidden' id="downloaded-<?php print $g; ?>"><?php print $d; ?></div>
			  <div class='hidden' id="mobl"><?php print isMobile() ? true : false; ?></div>
			  <div class='hidden' id="all-memorized"><a data-rel="dialog" data-prefetch href="/all-memorized">All Memorized</a></div>
			  
			  <!-- navigation -->
			  
			  <div data-role="footer" data-tap-toggle="false" data-position="fixed" class="nav-glyphish" data-dom-cache="true">
				<div data-role="navbar" class="nav-glyphish">
					<ul>
					<li> <a data-icon="custom" data-iconpos="top" class="prev" id='prev-<?php print $g; ?>' href="#" accesskey="b">Back</a> </li> 
					<li> <a data-icon="custom" data-iconpos="top" class="flip" id="flip-<?php print $g; ?>" href="#" accesskey="s">Show</a> </li>
					<li> <a data-icon="custom" data-iconpos="top" class="next" id="next-<?php print $g; ?>" href="#" accesskey="n">Next</a> </li>
					<li> <a data-icon="custom" data-iconpos="top" class="play" id="play-<?php print $g; ?>" href="#" accesskey="a">Auto</a></li>
					<li> <a data-icon="custom" data-iconpos="top" class="skip" id="skip-<?php print $g; ?>" href="#" accesskey="w">Wrong</a></li>
					</ul>
				</div> <!-- nav -->
			</div>
			</div><!-- /content -->
		</div> <!-- /page -->
		
		
			

  <!-- not mobile -->
  
  <!-- common -->
	
  	
  

  </body>
</html>
