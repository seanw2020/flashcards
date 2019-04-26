<?php
	
	global $lastError;
	require_once('functionsEZ.php');
	bootstrap();
	
	$w = isset($_COOKIE['swatch']) ? $_COOKIE['swatch'] : DEFAULT_SWATCH; //swatch
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en"  xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Congratulations - All Memorized!</title>
    
    
    <!-- load common (to mobile and pc) files -->
    <link rel="stylesheet" href="/style-common.css" />
    <script src="/lib/jquery/jquery.1.8.2.min.js"></script>
    <script src="/nav-common.js"></script>
    
    <!-- load specific files -->
    <?php if (isMobile()) : ?>
    	<link rel="stylesheet" href="/style-mobile.css" />
    	<script src="/nav-mobile.js"></script>
    	<meta name="viewport" content="width=device-width, initial-scale=1"> 
		<link rel="stylesheet" href="/lib/jquery/jquery.mobile-1.2.0.min.css" />
		<script src="/lib/jquery/jquery.mobile-1.2.0.min.js"></script>
    <?php else: ?>
    	<link rel="stylesheet" href="/style.css" />
    	<script src="/nav.js"></script>
		<link rel="stylesheet" href="/lib/jquery/jquery.mobile-1.2.0.min.css" />
		<script src="/lib/jquery/jquery.mobile-1.2.0.min.js"></script>
    <?php endif; ?>
  </head>
<body> 


<div data-role="page" data-dom-cache="true" data-theme="a" id="page-all-memorized">
 	<script type="text/javascript">
            $("#page-all-memorized").live('pagebeforeshow', function(event, data) {
	            //fix links
	            var p = '/' + data.prevPage.attr('id').replace('learn-','learn/');
	            $('.ui-dialog .ui-corner-top a, #all-memorized-close').attr('href',p); //close X -- force - after oath - iphone bug?
            });
        </script>


	<div data-role="header">
		<h1>Congratulations!</h1>
	</div><!-- /header -->

	<div data-role="content">	
		<p>Congratulations, you have learned all of these cards!</p>
		<p>Go forth and prosper.</p>		
		<a data-role="button" href="#" id="all-memorized-close">Close</a>
	</div><!-- /content -->

</div><!-- /page -->

</body>
</html>