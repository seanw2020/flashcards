<?php

  /*
   * Copyright 2010-2012 Evernote Corporation.
   *
   * This file contains configuration information for Evernote's PHP OAuth samples.
   * Before running the sample code, you must change the client credentials below.
   */
   
  // Client credentials. Fill in these values with the consumer key and consumer secret 
  // that you obtained from Evernote. If you do not have an Evernote API key, you may
  // request one from http://dev.evernote.com/documentation/cloud/
  define('OAUTH_CONSUMER_KEY', 'paperupgrade');
  define('OAUTH_CONSUMER_SECRET', '3e8bf248b80d334e');
  
  // Replace this value with https://www.evernote.com to use Evernote's production server
  define('EVERNOTE_SERVER', 'https://www.evernote.com');
  //define('EVERNOTE_SERVER', 'https://sandbox.evernote.com');
  
  // Replace this value with www.evernote.com to use Evernote's production server
  define('NOTESTORE_HOST', 'www.evernote.com');
  //define('NOTESTORE_HOST', 'sandbox.evernote.com');
  
  define('NOTESTORE_PORT', '443');
  define('NOTESTORE_PROTOCOL', 'https');  
  
  // Evernote server URLs. You should not need to change these values.
  define('REQUEST_TOKEN_URL', EVERNOTE_SERVER . '/oauth');
  define('ACCESS_TOKEN_URL', EVERNOTE_SERVER . '/oauth');
  define('AUTHORIZATION_URL', EVERNOTE_SERVER . '/OAuth.action');
  
  // Sean's additions
  define('MYSQL_SERVER', 'localhost');
  define('MYSQL_USER','8Zuyz0F0tKjma39a');
  define('MYSQL_PASS','MAKckaqpJb0ME63cZGgVLA0dH1');
  define('MYSQL_DB','fc');
  
  define('MAX_EVERNOTE_DOWNLOAD', '50'); //max download this number
  define('DEFAULT_SWATCH', 'c'); //max download this number
  
  // Evernote errors -- http://dev.evernote.com/doc/reference/Errors.html#Enum_EDAMErrorCode
  define ("EV_ERROR_ENUMERATIONS", serialize (array (
		  	1 => 'Unknown',
		  	2 => 'BAD_DATA_FORMAT',
		  	3 => 'PERMISSION_DENIED',
			6 => 'LIMIT_REACHED',
		  	19 => 'RATE_LIMIT_REACHED',
  		)
 		));
?>
