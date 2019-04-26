<?php
	global $vars;
	require_once('functionsEZ.php');
	bootstrap();
	
	//connect, filter, adjust, and store
	$s = getNoteStore();
	$n = getNoteBooks();
	$w = isset($_COOKIE['swatch']) ? $_COOKIE['swatch'] : DEFAULT_SWATCH; //swatch
	//$c = extract($vars);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en"  xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Select a notebook</title>
    
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
		<div id="select-notebook" data-dom-cache="true" data-role="page" data-theme="<?php print $w; ?>">
    		 <script type="text/javascript">
	            $("#select-notebook").live('pageinit', function() {
	            	//ezflashcards.selectNotebookSetup();
	            	ezflashcards.vars['thme'] = "<?php print $w; ?>"
	            	ezflashcards.learnUpdateSize();
	            });
	            
	            $("#select-notebook").live('pageshow', function(event, ui) {
			ezflashcards.selectNotebookSetup();
	            	ezflashcards.settingsChangeTheme(ezflashcards.vars['thme']);
            	});
	            
	        </script>
		    
		  	<div data-role="header" data-position="fixed" data-tap-toggle="false">
		  		<a href="/info" data-prefetch data-rel="dialog" data-icon="info" data-iconpos="left" >About</a>
				<h1 id="heading-select-page">Notebooks</h1>
			</div>
			<div data-role="content">
			   <!-- notebook selection -->
			   <div id="notebooks">
				<ul id="notebooks-ul" data-filter="true" data-inset="false" data-role="listview">
					<li data-ajax="false" data-role="list-divider" >Notebooks</li>
					<?php foreach ((array) $n as $guid=>$name): ?>
							<li> <a id="<?php print $guid; ?>" href="/learn/<?php print $guid?>"> <?php print($name); ?> 
								 <span id="cb<?php print $guid; ?>" class="ui-li-count">...</span></a></li>
					<?php endforeach ?>
				</ul>
				</div> <!-- notebook selection -->
			</div><!-- /content -->
		</div> <!-- /page -->
			

  <!-- not mobile -->
  
  <!-- common -->

  </body>
</html>
