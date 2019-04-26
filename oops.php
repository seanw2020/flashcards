<?php
	
	global $lastError;
	require_once('functionsEZ.php');
	bootstrap();
	
	$_SESSION['lastError'] ? $err=$_SESSION['lastError'] : $err="Sorry, an unknown error occurred. Please contact us to get this fixed, letting us known exactly the steps that triggered it. ";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en"  xmlns="http://www.w3.org/1999/xhtml">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Oops</title>
    
    
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

<div data-role="page" data-theme="a">

	<div data-role="header">
		<h1>Oops!</h1>
	</div><!-- /header -->

	<div data-role="content">	
		<p><?php print $err; ?></p>		
		Really stuck? You may also <a href='/logout'>Start Over</a>.
	</div><!-- /content -->

</div><!-- /page -->

</body>
</html>