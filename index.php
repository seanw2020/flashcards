<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en"  xmlns="http://www.w3.org/1999/xhtml">
   <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Evernote Flashcards App - EZFlashcards creates free flashcards from Evernote!</title>
    
    <!--  css -->
    <link rel="stylesheet" href="/style-common.css" />
    <link rel="stylesheet" href="/lib/jquery/jquery.mobile-1.2.0.min.css" />
    
    
    <!-- <link rel="stylesheet" href="/themes/ez1.min.css" /> -->
    
    <!-- load common (to mobile and pc) files -->
    <script type="text/javascript" src="/lib/jquery/jquery.1.8.2.min.js"></script>
    <script type="text/javascript" src="/lib/jquery/jquery.cookie.js"></script>
    <script type="text/javascript" src="/nav-common.js"></script>
    
    <!-- custom stuff before jquery mobile activates -->
	<script type="text/javascript" src="/select-notebook.js"></script>
	<script type="text/javascript" src="/learn.js"></script>
	<script type="text/javascript" src="/settings.js"></script>
    
    <!--  jquery mobile goes last -->
	<script type="text/javascript"  src="/lib/jquery/jquery.mobile-1.2.0.min.js"></script>   
    
  </head>
    
  
  <body>
  	<?php include_once("lib/tracking/analyticstracking.php"); ?>
  <!-- common -->
 
  <!--  mobile -->
		<div id="index" data-dom-cache="true" data-role="page" >
		  	<div data-role="header" data-position="fixed">
				<h1 id="heading-index">EZFlashCards</h1>
			</div>
			
			<div data-role="content">
				<!-- <a href="https://plus.google.com/109473417807064580713" rel="publisher">Google+</a> --> 
				<h1 style="text-align:center;">Turn Evernote into a free Flashcard App!</h1>
				<h4>This free Evernote flashcard service is provided by The <a href="http://www.paperupgrade.org" target="_blank">Paper Upgrade Project</a>, an
					educational public-charity and tax-exempt 501(c)(3) non-profit organization, based in Portland, Oregon, USA. </h4>

				<h4>Features: Offline flashcards, automatic flashcard flipping, skip memorized flashcards, multiple color themes, adjustable font-size, flashcard searching, flashcard browsing, attachment support, image thumbnail support for mobile devices (with links to full images), keyboard shortcuts, and flashcard editing </h4>

				<h4>Thanks to the <a href="http://www.jquerymobile.com" target="_blank">free, open-source Jquery Mobile software</a>, this application works on your PC, Mac, Linux, iPhone/iPod/iPad, or Android device (i.e. supports any HTML 5 capable browsers like Chrome, Firefox, or Safari)</h4>
					

				<h4 style="text-align:center;"><a data-ajax="false" href="/cookie-test" data-role="button" data-theme="b" data-inline="true">GET STARTED!</a></h4>

				<ul id="index-resources" data-inset="true" data-role="listview" data-divider-theme="d">
					<li data-ajax="false" data-role="list-divider" >Resources</li>
					<li><a data-ajax="false" href="http://www.paperupgrade.org/content/ezflashcards-free-evernote-flashcards" target="_blank">Frequently Asked Questions</a></li>
					<li><a data-ajax="false" href="http://www.paperupgrade.org/forum" target="_blank">Forum Page</a></li>
					<li><a data-ajax="false" href="http://www.facebook.org/paperupgrade" target="_blank">Facebook Page</a></li>
					<li><a data-ajax="false" href="https://twitter.com/paperupgrade" target="_blank">Twitter Page</a></li>
				</ul>
				
				<ul id="index-technical" data-inset="true" data-role="listview" data-divider-theme="d">
					<li data-ajax="false" data-role="list-divider" >Development</li>
					<li><a href="http://www.paperupgrade.org/contact" target="_blank">Help us improve this software!</a></li>
					<li>Lead developer: Sean W</li>
				</ul>
				<p align="center"><a href="https://www.positivessl.com" style="font-family: arial; font-size: 10px; color: #212121; text-decoration: none;"><img src="https://www.positivessl.com/images-new/PositiveSSL_tl_white.png" alt="SSL Certificate" title="SSL Certificate" border="0" /></a></p>
				<div data-role="footer" data-position="fixed">
					<h1>&#169; <?php print date('Y',strtotime('now')); ?> <a href="http://www.paperupgrade.org" target="_blank">Paper Upgrade Project</a></h1>
				</div>
			</div><!-- /content -->
			
		</div> <!-- /page -->
     <!-- not mobile -->
  
  <!-- common -->

  </body>
</html>
