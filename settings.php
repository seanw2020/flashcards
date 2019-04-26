<?php
	require_once('functionsEZ.php');
	bootstrap();
	$w = isset($_COOKIE['swatch']) ? $_COOKIE['swatch'] : DEFAULT_SWATCH; //swatch
?>

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings </title>
    
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
  <?php include_once("lib/tracking/analyticstracking.php"); ?>
  <!-- common -->
 
  <!--  mobile -->
		<div data-role="page" data-dom-cache="true" id="settings" data-theme="<?php print $w; ?>">
		 	<script type="text/javascript">
	            $("#settings").live('pageshow', function(event, ui) {
	            		ezflashcards.settingsSetup();
	            	});
	            $("#settings").live('pagebeforeshow', function(event, data) {
		            //fix links
		            var p = '/' + data.prevPage.attr('id').replace('learn-','learn/');
		            $('.ui-dialog .ui-corner-top a').attr('href',p); //close X -- force - after oath - iphone bug?
		            $('#settings-options-themes a,#settings-options-fonts a').attr('href',p);
	            });
	        </script>
		
		  	<div data-role="header">
				<h1 id="heading-settings">Options</h1>
			</div>
			<div data-role="content" >
		  
			   <!--  search -->
			   <div class="hidden">
			   <fieldset class="ui-grid-a">
			   	<div class="ui-block-a">
			   	  <input type="text" name="myname" id='find'>
			   	</div>
			    <div class="ui-block-b">
			      <input type="submit" data-type="button" id='submit' value="Submit">
			    </div>
			   </fieldset>
			   </div>
			   
			   <ul data-inset="true"  data-role="listview"  id="settings-options-cards">
					<li data-role="list-divider">This Card</li>
					<li> <a class="edit" id="edit-guid" href="#" accesskey="e" target="_blank">Edit</a></li> 
					<li> <a class="update" id="update-guid" href="#" accesskey="u">Update</a></li>
				</ul>
			   
			   
			   <ul data-inset="true"  data-role="listview"  id="settings-options-fonts">	
					<li data-role="list-divider" >Font Size</li>
					<li id="font-size-huge" ><a href="#">Huge</a></li>
					<li id="font-size-large" ><a href="#">Large</a></li>
					<li id="font-size-normal" ><a href="#">Medium</a></li>
					<li id="font-size-small" ><a href="#">Small</a></li>
				</ul>
				
				<ul data-inset="true"  data-role="listview"  id="settings-options-themes">
				    <li data-role="list-divider">Color Themes</li>
					<li><a id="swatch-a" href="/select-notebook">Dark</a></li>
					<li><a id="swatch-b" href="/select-notebook">Blue</a></li>
					<li><a id="swatch-c" href="/select-notebook">Gray</a></li>
					<li><a id="swatch-d" href="/select-notebook">White</a></li>
					<li><a id="swatch-e" href="/select-notebook">Yellow</a></li>
				</ul>
				
				<ul data-inset="true"  data-role="listview"  id="settings-options-timer">
				    <li data-role="list-divider">Auto Delay Speed</li>
					<li id="timer-1" ><a href="#">5 seconds</a></li>
					<li id="timer-2" ><a href="#">10 seconds</a></li>
					<li id="timer-3" ><a href="#">20 seconds</a></li>
					<li id="timer-4" ><a href="#">30 seconds</a></li>
				</ul>

				<ul data-inset="true"  data-role="listview"  id="settings-options-other">
					<li data-role="list-divider" >Other</li>
					<li id="logout" ><a href="/logout">Log out</a></li>
				</ul>
			   
			</div><!-- /content -->
			
			 
		</div> <!-- /page -->
			

  <!-- not mobile -->
  
  <!-- common -->
	
  	
  

  </body>
</html>
