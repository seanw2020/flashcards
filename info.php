<?php
	global $vars;
	require_once('functionsEZ.php');
	bootstrap();
	
	//connect, filter, adjust, and store
	//$s = getNoteStore();
	//$n = getNoteBooks();
	$w = isset($_COOKIE['swatch']) ? $_COOKIE['swatch'] : DEFAULT_SWATCH; //swatch
	//$c = extract($vars); */
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About </title>
    
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
    <script src="/nav-common.js"></script>
    <script src="/lib/jquery/jquery.cookie.js"></script>
    
    <!-- custom stuff before jquery mobile activates -->
	<script src="/select-notebook.js"></script>
	<script src="/learn.js"></script>
	<script src="/settings.js"></script>
    
    <!--  jquery mobile goes last -->
	<script src="/lib/jquery/jquery.mobile-1.2.0.min.js"></script>   
    
  </head>
    
  
  <body>
  <!-- common -->
 
  <!--  mobile -->
		<div id="info" data-dom-cache="true" data-role="page" data-theme="<?php print $w; ?>">
    		 <script type="text/javascript">
	            $("#info").live('pageinit', function() {
	            	ezflashcards.vars['thme'] = "<?php print $w; ?>"
	            	$('.ui-dialog .ui-corner-top a').attr('href','/select-notebook');
	            });
	            
	            $("#info").live('pageshow', function(event, ui) {
	            	ezflashcards.settingsChangeTheme(ezflashcards.vars['thme']);
	            	$('.ui-dialog .ui-corner-top a').attr('href','/select-notebook'); //force X close to go here
            	});
	            $("#info").live('pagebeforeshow', function(event, data) {
	            });
	            
	        </script>
		    
		  	<div data-role="header" data-position="fixed">
				<h1 id="heading-info">About this app</h1>
			</div>
			
			<div data-role="content">
				<h2 style='text-align:center'>Welcome to this flashcards app!</h2>
				<h3> This is a free Evernote flashcard service</h3>
				<br/><br/>
	
				<ul id="info-technical" data-inset="true" data-role="listview">
					<li data-ajax="false" data-role="list-divider" >Development</li>
					<li>Lead developer: Sean W</li>
				</ul>
		</div><!-- /content -->
			
		</div> <!-- /page -->
			

  <!-- not mobile -->
  
  <!-- common -->

  </body>
</html>
