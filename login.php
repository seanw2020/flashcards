<?php

  /* This file is a modified version of the Evernote sample application. Its headers disclaimers follow:
   * 
   * Copyright 2010-2012 Evernote Corporation.
   *
   * This sample web application demonstrates the process of using OAuth to authenticate to
   * the Evernote web service. More information can be found in the Evernote API Overview
   * at http://dev.evernote.com/documentation/cloud/.
   *
   * This application uses the PHP OAuth Extension to implement an OAuth client.
   * To use the application, you must install the PHP OAuth Extension as described
   * in the extension's documentation: http://www.php.net/manual/en/book.oauth.php
   */

  // Include our configuration settings
  require_once('config.php');
  
  // Include our OAuth functions
  require_once('functionsEV.php');
  require_once('functionsEZ.php');
  
  // Use a session to keep track of temporary credentials, etc
  session_start();
  
  // Status variables
  $lastError = null;
  $currentStatus = null;
  $action = null;

  // Leave this page under 2 conditions
  if (!empty($_SESSION['login'])) //already logged in
  		header('Location: /select-notebook');
  elseif (isset($_COOKIE['requestToken']) && loginCheck()) //logged out with valid cookie (auto-login)
		header('Location: /select-notebook');

  if (isMobile())
  	$l = "http://www.evernote.com/m/";
  else 
  	$l = "https://www.evernote.com/Home.action";
  
  // Request dispatching. If a function fails, $lastError will be updated.
  if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action == 'callback') {
      if (handleCallback()) {
        if (getTokenCredentials()) {
          header('Location: /select-notebook');
        }
      }
    } else if ($action == 'authorize') {
      if (getTemporaryCredentials()) {
        // We obtained temporary credentials, now redirect the user to evernote.com to authorize access
        header('Location: ' . getAuthorizationUrl());
      }
    } else if ($action == 'reset') {
      resetSession();
    }
  }
?>

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Select a notebook</title>
    
    <!--  css -->
    <link rel="stylesheet" href="/style-common.css" />
    <link rel="stylesheet" href="/lib/jquery/jquery.mobile-1.2.0.min.css" />
    <!-- <link rel="stylesheet" href="/themes/ez1.min.css" /> -->
    
    <!-- load common (to mobile and pc) files -->
    <script src="/lib/jquery/jquery.1.8.2.min.js"></script>
    <script src="/lib/jquery/jquery.cookie.js"></script>
    <script src="/nav-common.js"></script>
    
    <!-- custom stuff before jquery mobile activates -->
    <script src="/select-notebook.js"></script>
    <script src="/learn.js"></script>
    
    <!--  jquery mobile goes last -->
	<script src="/lib/jquery/jquery.mobile-1.2.0.min.js"></script>    
    
  </head>
  <body>
  <?php include_once("lib/tracking/analyticstracking.php"); ?>

  <div data-role="page" >
	  <div data-role="header">
	    <h1>EZ Flashcards</h1>
	  </div>
	  
  	<div data-role="content" >
  	<h1 style="text-align: center;">Connect to Evernote</h1>
  	<!-- <h3>After you click the button below, you'll be logged in for roughly one week. Afterwards, if you don't see images in EZFlashcards, just log into Evernote with your web browser. For now, just click the button below.</h3> -->
  	<!-- <h3>Grant access to EzFlashcards</h3>  -->
  	

<?php if (isset($lastError)) { ?>
    <p style="color:red">An error occurred: <?php echo $lastError;  ?></p>
<?php } else if ($action != 'callback') { ?>

   <h4 style="text-align:center;">
      <a href="/login.php?action=authorize" data-ajax="false" data-role="button" data-theme="b" data-inline="true">Authorize</a>
   </h4>
   
   <div data-role="footer" data-position="fixed">
		<h1>&#169; <?php print date('Y',strtotime('now')); ?> </h1>
   </div>

	<?php } else { ?>
	
    <?php doCookie(); ?>
    <?php exit(); ?>

  <?php //} // if (isset($_SESSION['notebooks'])) ?>
<?php } // if (isset($lastError)) ?>
    
    
    <p>
      <!--  <a href="login.php?action=reset">Click here</a> to start over. -->
    </p>
    
    </div> <!--  content -->
    </div> <!-- page -->
    
  </body>
</html>

