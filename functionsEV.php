<?php

  use EDAM\NoteStore\NotesMetadataResultSpec;

/*
   * Copyright 2011-2012 Evernote Corporation.
   *
   * This file contains functions used by Evernote's PHP OAuth samples.
   */

  // Include the Evernote API from the lib subdirectory. 
  // lib simply contains the contents of /php/lib from the Evernote API SDK
  use EDAM\NoteStore\NoteFilter;
  use EDAM\Types\Note;
  use EDAM\UserStore\UserStoreClient;
  use EDAM\Types\NoteSortOrder; //sean's addition - http://discussion.evernote.com/topic/29071-have-the-way-to-chagne-notes-sort-by/

define("EVERNOTE_LIBS", dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib");
  ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . EVERNOTE_LIBS);

  require_once("Thrift.php");
  require_once("transport/TTransport.php");
  require_once("transport/THttpClient.php");
  require_once("protocol/TProtocol.php");
  require_once("protocol/TBinaryProtocol.php");
  require_once("packages/Types/Types_types.php");
  require_once("packages/UserStore/UserStore.php");
  require_once("packages/NoteStore/NoteStore.php");

  // Import the classes that we're going to be using
  use EDAM\NoteStore\NoteStoreClient;
  use EDAM\Error\EDAMSystemException, EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;

  // Verify that you successfully installed the PHP OAuth Extension
  if (!class_exists('OAuth')) {
    die("<span style=\"color:red\">The PHP OAuth Extension is not installed</span>");
  }

  // Verify that you have configured your API key
  if (strlen(OAUTH_CONSUMER_KEY) == 0 || strlen(OAUTH_CONSUMER_SECRET) == 0) {
    $configFile = dirname(__FILE__) . '/config.php';
    die("<span style=\"color:red\">Before using this sample code you must edit the file $configFile " .
        "and fill in OAUTH_CONSUMER_KEY and OAUTH_CONSUMER_SECRET with the values that you received from Evernote. " .
        "If you do not have an API key, you can request one from " .
        "<a href=\"http://dev.evernote.com/documentation/cloud/\">http://dev.evernote.com/documentation/cloud/</a></span>");
  }

  /*
   * The first step of OAuth authentication: the client (this application) 
   * obtains temporary credentials from the server (Evernote). 
   *
   * After successfully completing this step, the client has obtained the
   * temporary credentials identifier, an opaque string that is only meaningful 
   * to the server, and the temporary credentials secret, which is used in 
   * signing the token credentials request in step 3.
   *
   * This step is defined in RFC 5849 section 2.1:
   * http://tools.ietf.org/html/rfc5849#section-2.1
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  function getTemporaryCredentials() {
    global $lastError, $currentStatus;
    try {
      $oauth = new OAuth(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET);
      $requestTokenInfo = $oauth->getRequestToken(REQUEST_TOKEN_URL, getCallbackUrl());
      if ($requestTokenInfo) {
        $_SESSION['requestToken'] = $requestTokenInfo['oauth_token'];
        $_SESSION['requestTokenSecret'] = $requestTokenInfo['oauth_token_secret'];
        $currentStatus = 'Obtained temporary credentials';
        return TRUE;
      } else {
        $lastError = 'Failed to obtain temporary credentials: ' . $oauth->getLastResponse();
      }
    } catch (OAuthException $e) {
      $lastError = 'Error obtaining temporary credentials: ' . $e->getMessage();
    }
    return false;
  }

  /*
   * The completion of the second step in OAuth authentication: the resource owner 
   * authorizes access to their account and the server (Evernote) redirects them 
   * back to the client (this application).
   * 
   * After successfully completing this step, the client has obtained the
   * verification code that is passed to the server in step 3.
   *
   * This step is defined in RFC 5849 section 2.2:
   * http://tools.ietf.org/html/rfc5849#section-2.2
   *
   * @return boolean TRUE if the user authorized access, FALSE if they declined access.
   */
  function handleCallback() {
    global $lastError, $currentStatus;
    if (isset($_GET['oauth_verifier'])) {
      $_SESSION['oauthVerifier'] = $_GET['oauth_verifier'];
      $currentStatus = 'Content owner authorized the temporary credentials';
      return TRUE;
    } else {
      // If the User clicks "decline" instead of "authorize", no verification code is sent
      $lastError = 'Content owner did not authorize the temporary credentials';
      return FALSE;
    }
  }

  /*
   * The third and final step in OAuth authentication: the client (this application)
   * exchanges the authorized temporary credentials for token credentials.
   *
   * After successfully completing this step, the client has obtained the
   * token credentials that are used to authenticate to the Evernote API.
   * In this sample application, we simply store these credentials in the user's
   * session. A real application would typically persist them.
   *
   * This step is defined in RFC 5849 section 2.3:
   * http://tools.ietf.org/html/rfc5849#section-2.3
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  function getTokenCredentials() {
    global $lastError, $currentStatus;
    
    if (isset($_SESSION['accessToken'])) {
      $lastError = 'Temporary credentials may only be exchanged for token credentials once';
      return FALSE;
    }
    
    try {
      $oauth = new OAuth(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET);
      $oauth->setToken($_SESSION['requestToken'], $_SESSION['requestTokenSecret']);
      $accessTokenInfo = $oauth->getAccessToken(ACCESS_TOKEN_URL, null, $_SESSION['oauthVerifier']);
      if ($accessTokenInfo) {
        $_SESSION['accessToken'] = $accessTokenInfo['oauth_token'];
        $_SESSION['accessTokenSecret'] = $accessTokenInfo['oauth_token_secret'];
        $_SESSION['noteStoreUrl'] = $accessTokenInfo['edam_noteStoreUrl'];
        $_SESSION['shard'] = $accessTokenInfo['edam_shard'];
        $_SESSION['webApiUrlPrefix'] = $accessTokenInfo['edam_webApiUrlPrefix'];
        // The expiration date is sent as a Java timestamp - milliseconds since the Unix epoch
        $_SESSION['tokenExpires'] = (int)($accessTokenInfo['edam_expires'] / 1000);
        $_SESSION['userId'] = $accessTokenInfo['edam_userId'];
        $currentStatus = 'Exchanged the authorized temporary credentials for token credentials';
        return TRUE;
      } else {
        $lastError = 'Failed to obtain token credentials: ' . $oauth->getLastResponse();
      }
    } catch (OAuthException $e) {
      $lastError = 'Error obtaining token credentials: ' . $e->getMessage();
    }  
    return FALSE;
  }
  
  /*
   * Demonstrate the use of token credentials obtained via OAuth by listing the notebooks
   * in the resource owner's Evernote account using the Evernote API. Returns an array
   * of String notebook names.
   *
   * Once you have obtained the token credentials identifier via OAuth, you can use it
   * as the auth token in any call to an Evernote API function.
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  function listNotebooks() {
  	global $lastError, $currentStatus, $notebooks;
  	$result = array();
  	$_SESSION['caching']=FALSE;

  	try {
  		$parts = parse_url($_SESSION['noteStoreUrl']);
  		if (!isset($parts['port'])) {
  			if ($parts['scheme'] === 'https') {
  				$parts['port'] = 443;
  			} else {
  				$parts['port'] = 80;
  			}
  		}
  		$noteStoreTrans = new THttpClient($parts['host'], $parts['port'], $parts['path'], $parts['scheme']);
  
  		$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
  		$noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
   		$authToken = $_SESSION['accessToken'];
   		
   		//get list of notebooks from EN or from cache
   		try{
  			$notebooks = $noteStore->listNotebooks($authToken);
  			saveNoteBooks($notebooks);
  		} catch (EDAMSystemException $e) {
  			if ($e->errorCode==19) {//too many requests
  				$_SESSION['caching']=TRUE;
  				$notebooks = getCachedNotebooks();
  				count($notebooks) ? $result = TRUE : $result = FALSE;
  				return $result;
  			}
  			else
  				die($e);
  		}
  		
		// get shared notebooks  		
	    // http://discussion.evernote.com/topic/23750-access-notes-from-linked-notebook/
		$linked_notebooks = $noteStore->listLinkedNotebooks($authToken);
		
		if ($linked_notebooks) {
			 foreach ($linked_notebooks as $ln) {
				 $parts['pathShared']='/edam/note/'.$ln->shardId;
				 $noteStoreTrans2 = new THttpClient($parts['host'], $parts['port'], $parts['pathShared'], $parts['scheme']);
				 $noteStoreProt2 = new TBinaryProtocol($noteStoreTrans2);
				 $noteStore2 = new NoteStoreClient($noteStoreProt2, $noteStoreProt2);
				 $sk = $ln->shareKey ? $ln->shareKey : "";

				 try {
				 	$auth_shared = $noteStore2->authenticateToSharedNotebook($sk, $authToken);		
				 }
				 catch (Exception $e) {
					//if linkedNotebooks are available but you can't authenticate anymore (booh EN!), move on with life
					break;
				 }	

				 //shared notebook
				 $sharedNotebook = $noteStore2->getSharedNotebookByAuth($auth_shared->authenticationToken);
				 $g = $sharedNotebook->notebookGuid;

				//remember authentication tokens for later
				 $_SESSION['accessTokensShared'][$g]=array('token'=>$auth_shared->authenticationToken,'path'=>$parts['pathShared']);

				//modify - for display purposes on select-notebook
				$sharedNotebook->name=$ln->shareName; 
				$sharedNotebook->guid=$g;
				$sharedNotebook->stack='Shared';

				 //add shared notebooks
				 $notebooks[]=$sharedNotebook;
			}
		}	

  		if (!empty($notebooks)) {
  			$result=$notebooks;
  			/* Sean's change
  			   foreach ($notebooks as $notebook) {
  				$result[] = $notebook->name;
  			}*/
  		}

  		$notebooks = $result;
  		$currentStatus = 'Successfully listed content owner\'s notebooks';
  		return TRUE;
  	} catch (EDAMSystemException $e) {
  		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
  			$lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
  		} else {
  			$lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
  		}
  	} catch (EDAMUserException $e) {
  		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
  			$lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
  		} else {
  			$lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
  		}
  	} catch (EDAMNotFoundException $e) {
  		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
  			$lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
  		} else {
  			$lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
  		}
  	} catch (Exception $e) {
  		$lastError = 'Error listing notebooks: ' . $e->getMessage();
  	}
  	return FALSE;
  }  
  /*
   * Reset the current session.
   */
  function resetSession() {
    if (isset($_SESSION['requestToken'])) {
      unset($_SESSION['requestToken']);
    }
    if (isset($_SESSION['requestTokenSecret'])) {
      unset($_SESSION['requestTokenSecret']);
    }
    if (isset($_SESSION['oauthVerifier'])) {
      unset($_SESSION['oauthVerifier']);
    }
    if (isset($_SESSION['accessToken'])) {
      unset($_SESSION['accessToken']);
    }
    if (isset($_SESSION['accessTokenSecret'])) {
      unset($_SESSION['accessTokenSecret']);
    }
    if (isset($_SESSION['noteStoreUrl'])) {
      unset($_SESSION['noteStoreUrl']);
    }
    if (isset($_SESSION['webApiUrlPrefix'])) {
      unset($_SESSION['webApiUrlPrefix']);
    }
    if (isset($_SESSION['tokenExpires'])) {
    	unset($_SESSION['tokenExpires']);
    }
    if (isset($_SESSION['userId'])) {
    	unset($_SESSION['userId']);
    }
    if (isset($_SESSION['notebooks'])) {
      unset($_SESSION['notebooks']);
    }
  }
  
  /*
   * Get the URL of this application. This URL is passed to the server (Evernote)
   * while obtaining unauthorized temporary credentials (step 1). The resource owner 
   * is redirected to this URL after authorizing the temporary credentials (step 2).
   */
  function getCallbackUrl() {
    $thisUrl = (empty($_SERVER['HTTPS'])) ? "http://" : "https://";
    $thisUrl .= $_SERVER['SERVER_NAME'];
    $thisUrl .= ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? "" : (":".$_SERVER['SERVER_PORT']);
    $thisUrl .= $_SERVER['SCRIPT_NAME'];
    $thisUrl .= '?action=callback';
    return $thisUrl;
  }

  /*
   * Get the Evernote server URL used to authorize unauthorized temporary credentials.
  */
  function getAuthorizationUrl() {
  	$url = AUTHORIZATION_URL;
  	$url .= '?oauth_token=';
  	$url .= urlencode($_SESSION['requestToken']);
  	return $url;
  }

  /*
   * http://discussion.evernote.com/topic/4521-en-media-hash/
  */
  function hashXmlToBin($hash) {

  	$chunks = explode("\n", chunk_split($hash,2,"\n"));
  	$calc_hash = "";
  	foreach ($chunks as $chunk) {
  		$newdata="";
  		if (!empty($chunk)) {
  			$len = strlen($chunk);
  			for($i=0;$i<$len;$i+=2) {
  				$newdata .= pack("C",hexdec(substr($chunk,$i,2)));
  			}
  			$bin_chunk = $newdata;
  			$calc_hash .= $bin_chunk;
  		}
  	}
  	return $calc_hash;
  }

/* ----------------------------------------------------------------------
 *                         Sean's additions
 * ----------------------------------------------------------------------
 */

/*
* get notestore or show error
*
* @return 	Obj or False	$noteStore or nothing		return notestore obj or show an error message
*/
function getNoteStore($g = null) { //optional guid
  	global $lastError, $currentStatus, $noteStore, $vars;
	$errHead=t("Error connecting to the notestore for this Guid");
	$noteStore = FALSE; 

	try {
		$parts = parse_url($_SESSION['noteStoreUrl']);
		if (!isset($parts['port'])) {
			if ($parts['scheme'] === 'https') {
				$parts['port'] = 443;
			} else {
				$parts['port'] = 80;
			}
		}
	//if this guid is a linked one (that is, sb else shared it), then change path
        if ($g && isset($_SESSION['accessTokensShared']) && (array_key_exists($g,$_SESSION['accessTokensShared']))) {
		$parts['path'] = $_SESSION['accessTokensShared'][$g]['path'];
	}

		$noteStoreTrans = new THttpClient($parts['host'], $parts['port'], $parts['path'], $parts['scheme']);
		$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
		$noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);

		return $noteStore; 
		//otherwise	handle errors
	} catch (EDAMSystemException $e) {
		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
			$lastError = $errHead . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
		} else {
			$lastError = $errHead . $e->getCode() . ": " . $e->getMessage();
		}
	} catch (EDAMUserException $e) {
		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
			$lastError = $errHead . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
		} else {
			$lastError = $errHead . $e->getCode() . ": " . $e->getMessage();
		}
	} catch (EDAMNotFoundException $e) {
		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
			$lastError = $errHead . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
		} else {
			$lastError = $errHead . $e->getCode() . ": " . $e->getMessage();
		}
	} catch (Exception $e) {
		$lastError = $errHead . $e->getMessage();
	}

	//show error
		oops($lastError);
}

/* get a list of notebooks, and store in vars 
 * @return 	mixed 		notebooks if successful
 */
function getNoteBooks() {
	global $vars, $noteStore, $notebooks;
	
	//if no notebooks, get from cache
	if (!isset($notebooks))
		$notebooks = getCachedNotebooks();
	
	$r = array();
	//$s = $noteStore;
	$authToken = $_SESSION['accessToken']; 
	$p = new NotesMetadataResultSpec();
	$p->includeTitle=1; // return this
	
	//flatten stacks
	if (listNoteBooks()) {
		foreach ($notebooks as $k=>$n) {
			$o[$n->guid]=$n->name;
			if ($n->stack)
				$a[$n->guid]=$n->stack.'²'.$n->name; 
			else
				$a[$n->guid]=$n->name;
		}
		asort($a); //sort

		foreach ($a as $k=>$v) 
			$r[$k]=str_replace('²',': ',$v); //alt approach (needs work): //$f[$k]=str_replace('²',':',$o[$k]); -- removes duplicates
	}
	else 
		oops(t('There are no notebooks here? That seems odd. Please click Back and choose another.'));
	
	return $r;
		
}

/* count number of notes per book, and store in vars
 * @param	text	$guid	the notebook guid
 * @return 	mixed 	$c		total or error 
*/
function getNoteBookCount($guid) {
	global $vars, $noteStore, $notebooks;
	if ($_SESSION[''])
	$r = FALSE;
	$s = $noteStore;
	$authToken = array_key_exists($guid,$_SESSION['accessTokensShared']) ? $_SESSION['accessTokensShared'][$guid]['token'] : $_SESSION['accessToken'];
	$p = new NotesMetadataResultSpec();
	$p->includeTitle=1; // return this
	$c = 0; //count

	//flatten stacks
	$f = new NoteFilter();
	$f->notebookGuid=$guid; //from URL
	$r = $s->findNotesMetadata($authToken, $f, 0, 1, $p); // m = max
	$c = $r->totalNotes;
	return ($c);
}

/*
 * Get a set of notes (basic info: guid and title without note content) given this filter
*
* @param 	notestore			$s				notestore object
* @param 	txt					$g				guid of notebook
* @param 	txt					$o				offset
* @param 	txt					$m				max notes to return
* @return 	object				$r				resultset of notes
* Return  	mixed				$z or redir		notes if it worked, error otherwise
*/
function getNotes($s, $g, $o, $m=50) {
	global $lastError, $currentStatus;
	
	if (isset($_SESSION['accessTokensShared']))
		$authToken = array_key_exists($g,$_SESSION['accessTokensShared']) ? $_SESSION['accessTokensShared'][$g]['token'] : $_SESSION['accessToken'];
	else
		$authToken = $_SESSION['accessToken'];
	$errHead = "Unable to filter the result set: ";
	$r = "";
	$t = 0; //total notes
	$y = array(); //combined obj
	$p = null; //metadata parameters

    try {
    	$n=$s->getNoteBook($authToken,$g);
    }catch(EDAMSystemException $e) {
    	$errors=unserialize(EV_ERROR_ENUMERATIONS);
    	if ($e->errorCode==19) {//too many requests
    		$x = getCachedNotebook($g);
    		$y = $x->notes;
    		$z = new stdClass;
    		$z->notes = $y;
    		$z->totalNotes = $x->totalCached;
    		return $z;
    	}
    }catch(Exception $e) {
    	oops($errors[$e->errorCode].print_r($e,1));
    }
    
	$_SESSION['notebook']=array('guid'=>$g, 'name'=>$n->name);

	$p = new NotesMetadataResultSpec();
	$p->includeTitle=1; // return this
	//$p->includeCreated=1; //for sorting
	
	try {
		$f = new NoteFilter();
		$f->notebookGuid=$g; //from URL
		$f->order = NoteSortOrder::CREATED;
		$f->ascending = true;
		$i=0;
		do {
			$i++;
			//$r = $s->findNotes($authToken, $f, $o, $m); // m = max
			$r = $s->findNotesMetadata($authToken, $f, $o, $m, $p); // m = max
			
			//http://stackoverflow.com/questions/240660/in-php-how-do-you-change-the-key-of-an-array-element
			if ($o) {
				foreach ($r->notes as $k=>$v){
					$r->notes[$o+$k]=$r->notes[$k];
					unset($r->notes[$k]);
				}
			}
			$x = $r->notes;
			$y = $y + $x;
			$o = $o + $m;
		}while ($m > $o);
		//}while ($r->totalNotes > $o); //-- ALL NOTES
		$z = new stdClass;
		//pr($y,1);
		$z->notes = $y;
		$z->totalNotes=$r->totalNotes;
		if ($z->totalNotes===0) {
			oops(t('No notes in this notebook. Please click Back and choose another.'));
		}else if (!$z) {
			$t=t('Could not load notes for GUID: ').$g;
			oops($t);
		}else 
			return $z;
		
		
	} catch (EDAMSystemException $e) {
		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
			$lastError = $errHead . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
		} else {
			$lastError = $errHead . $e->getCode() . ": " . $e->getMessage();
		}
	} catch (EDAMUserException $e) {
		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
			$lastError = $errHead . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
		} else {
			$lastError = $errHead . $e->getCode() . ": " . $e->getMessage();
		}
	} catch (EDAMNotFoundException $e) {
		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
			$lastError = $errHead . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
		} else {
			$lastError = $errHead . $e->getCode() . ": " . $e->getMessage();
		}
	} catch (Exception $e) {
		$lastError = $errHead . $e->getMessage();
	}
	// print error
	oops($lastError);
}

/*
 * get note content, then parse it by converting <en-media> into downloadable links, add target=_blank to all notes to keep offline notes working
 * @param 	obj 	$s		notestore obj
 * @param 	obj 	$n		notes resultset of notes search (e.g. all notes in a notebook)
 * @return 	bool 			true if adjustment worked, false otherwise
*/
function adjustNotes($n,$s) {
	$vars = array();
	$cached = FALSE;
	
	if (isset($_SESSION['showCachedNotebook'])) {
		unset($_SESSION['showCachedNotebook']);
		$cached = TRUE;
	}
	if (!empty($n->notes)) {
		//setup
		$vars['totalNotes']=$n->totalNotes;
		foreach ($n->notes as $note) {
			if ($cached) {
				$vars['cards'][]=array('Q'=>$note['q'],'A'=>$note['a'],'E'=>$note['edit']);
			}
			else 
				_adjustNotes($s, $n, $vars, $note);
		}
		return $vars;
	}//if notes exist
	else
		oops(t("Evernote reports that you've reached your limit of requests allowed per block of time. Please click Back and try a notebook you've previously opened. Then, when about 15 minutes have passed, try opening this notebook again. "));
}

/*
 * heavy lifting of adjustNotes
 * @param obj	$n		notes resultset
 * @param obj	$s		notestore		
 * @param arr	$vars	used to return important info
 * @param obj	$note	the note itself
 */
function _adjustNotes($s, $n = FALSE, &$vars, $note = NULL, $guid = NULL) {
	//ugly but functional -- if bgload, use its nbgd, otherwise look in _GET
	$g = isset($_GET['nbgd']) ? $_GET['nbgd'] : "";
	if (!$g)
		$g = isset($_GET['notebook']) ? $_GET['notebook'] : "";
	if (isset($_SESSION['accessTokensShared']))
		$authToken = array_key_exists($g,$_SESSION['accessTokensShared'])? $_SESSION['accessTokensShared'][$g]['token'] : $_SESSION['accessToken'];
	else
		$authToken = $_SESSION['accessToken'];

	//loop
	if (!isset($guid)) { 
		$guid = $note->guid;
		$title = $note->title;
	}else { //single
		$meta  = $s->getNote($authToken, $guid, 0,0,0,0);
		$title = $meta->title;
	}

	//already in db?
	if (isCachedNote($guid)) {
		$cNote=getCachedNote($guid);
		$usn=$cNote['usn'];
		$q=$cNote['q'];
		$a=$cNote['a'];
		$edit=$cNote['edit'];
	}
	else {
		
		//try to get more notes and give error otherwise
		try {
			$content = $s->getNoteContent($authToken, $guid);
		}catch (EDAMSystemException $e) {
	  		if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
	  			$lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
	  		} else {
	  			$lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
	  		}
	  		oops($lastError);
	  	}catch(Exception $e) {
	  		die($e);
	  	}
  	
		$content = str_replace('&#',"",$content);
		$content = str_replace('&nbsp;'," ",$content);
		$content = str_replace('&mdash;','--', $content);
		$content = str_replace('<br>','<br/>',$content);//html_entity_decode($content);
	
		//remove these things too
		$stripme = array('background-color: rgb(255, 255, 255)','background-color: 255', 'background-color: white', 'background-color: #FFF', 'background-color: #ffffff',
	                         'background-color: rgb(0, 0, 0)', 'background-color: 0', 'background-color: #000', 'background-color: black', 'background-color: #000000',
	                         'background-color: rgb(250, 250, 250)',
				 'font-size: 7px', 'font-size: 8px', 'font-size: 9px', 'font-size: 10px', 'font-size: 11px','font-size: 12px', 'font-size: 13px','font-size: 14px', 'font-size: 15px', 'font-size: 16px', 'font-size: 17px', 'font-size: 18px', 'font-size: 19px','font-size: 20px', 'font-size: 21px', 'font-size: 22px', 'font-size: 23px', 'font-size: 24px', 'font-size: 25px', 'font-size: 26px', 'font-size: 27px', 'font-size: 28px', 'font-size: 29px',
	                         'line-height: .5em', 'line-height: 1em', 'line-height: 1.5em', 'line-height: 2em', 'line-height: 15px',
				 'color="#010101"','color="#222222"', //special purpose
				 'line-height:','font-size:',//could cause trouble!
				 'color: rgb(0, 0, 0)', 'color: rgb(255, 255, 255)', 
	                         'color: black', 'color: white', 
				 '<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">',
				 'From &lt;', /* this is a hack, I don't know why it's needed. without it, the link won't display, prob due to strip tags */
				 'Created with Microsoft OneNote 2007.',
				 'Created with Microsoft OneNote 2010.',
				 'Created with Microsoft OneNote 2013.',
				 'Created with Microsoft OneNote 2016.');
		foreach ($stripme as $val) {
		  $content = str_replace($val,'',$content);
		}
	
		$fonts = array('<font size="1">','<font size="2">','<font size="3">','<font size="4">','<font size="5">','<font size="6">','<font size="7">');
		$content = str_replace($fonts,'<font>',$content);
	
			
		$edit = EVERNOTE_SERVER."/edit/{$guid}";
			
		//append - based on find and replace <en-media> with <img> ones - http://stackoverflow.com/questions/3194875/php-dom-replace-element-with-a-new-element
		$dom = new DOMDocument;
		$dom->loadXml($content);
	
		// nested UL's cause JQMobile to make nested lists. phased out in 1.4 -- jquery mobile issue: https://github.com/jquery/jquery-mobile/issues/5657
		// to fix, add divs to all ul's
		$new_div = $dom->createElement('div');
	  	$new_div->setAttribute('class','wrapper');
		$us = $dom->getElementsByTagName('ul');
		foreach ($us as $val) { //http://stackoverflow.com/questions/8426391/wrap-all-images-with-a-div-using-domdocument
		  //Clone our created div
		  $new_div_clone = $new_div->cloneNode();
		  //Replace ul with this wrapper div
		  $val->parentNode->replaceChild($new_div_clone,$val);
		  //Append this ul to wrapper div
		  $new_div_clone->appendChild($val);
		}
	
		//list all <en-media>
		$as = $dom->getElementsByTagName('a');
		foreach ($as as $a) {
			$a->setAttribute('target', "_blank");
		}
	
		//list all p's -- find those ending in date, like: ,2012 (indicating import from OneNote)
		$aps = $dom->getElementsByTagName('p');
		$remove = array();
		foreach ($aps as $k=>$p) {
			if ($k>2) break; //only process the first 2, where dates would be from OneNote import
			// date ending in a year
			//if (preg_match("/,\s([0-9]){4}/",$p->nodeValue,$matches)) {
			if (preg_match("/([0-9]){4}/",$p->nodeValue,$matches)) {
				$remove[] = $p; 
			}
			// just a time 
			//if (preg_match("/([0-9]{1,2}):([0-9]{2})\s(PM|AM)/",$p->nodeValue,$matches)) {
			if (preg_match("/^([0-9]{1,2}):([0-9]{2})/",$p->nodeValue,$matches)) {
				$remove[] = $p; 
				//pr($p);die();
			}
		}
		if ($remove) {
			foreach ($remove as $r) {
			  //$dom->removeChild($r); //http://php.net/manual/fr/domnode.removechild.php
			  $r->parentNode->removeChild($r);
			}
			unset($remove);
		}
//die();
		//print_r($dom);
		
		//list all <en-media>
		$medias = $dom->getElementsByTagName('en-media');
		foreach ($medias as $media) {
			$hash = $media->getAttribute('hash');
			$hash = hashXmlToBin($hash); //xml to bin for ByHash method below
			$resource=$s->getResourceByHash($authToken, $guid,$hash,false,false,false,false);
			$size = 400; // px wide
			$sizeM = 300; // 300 is max -- mobile px wide -- http://dev.evernote.com/documentation/cloud/chapters/thumbnails.php
			$url=resourceUrl($authToken,$resource,false); //don't resize
		 	$urlThumb=$url;	//NOTE: 7-16-2013 -- no resizing since Evernote's resizing is not very good
			//$urlThumb=resourceUrl($authToken,$resource,$sizeM); //resize
	
			//if image, show inline
			$inline=array('image/png','image/jpeg','image/gif');
			if (in_array($resource->mime,$inline)) {
				//$resize=isMobile() ? $sizeM : $size;
		
				$img=$dom->createElement('img');
	
				$img->setAttribute('src', isMobile() ? $urlThumb : $url); //resized or not
			 	//print($urlThumb);die();	
	
				$lm=$dom->createElement('a');
				$lm->setAttribute('href', $url);
				$lm->setAttribute('target', "_blank");
				$lm->setAttribute('class', "evernote-image");
				$lm->appendChild($img); //image link: <a><img>...
		
				//$img->setAttribute('height', $resource->height);
			}else { //show link
				$mime=str_replace('application/','',$resource->mime);
				$filename=$resource->attributes->fileName;
					
				//sanitize a little
				$badbits = array('#','_','@'); //http://stackoverflow.com/questions/3957218/php-dom-createelement-allow-spaces
				$filename = preg_replace('/\s\s+/', ' ', $filename); //remove line breaks
				//$filename = html_entity_decode($filename);
				$filename = htmlspecialchars($filename);
				$filename = strip_tags($filename);
				$filename = str_replace($badbits,'',$filename); //sanitize for file sys
				$filename = substr($filename,0,254); //shorten
				$filename = trim($filename);
					
				$lm=$dom->createElement('a',"{$filename}");// element
				$lm->setAttribute('href', $url);
				$lm->setAttribute('target', "_blank");
				$lm->setAttribute('class', "download-attachment");
			}
			// append to DOM
			$media->appendChild($lm);
		}//foreach medias
		
		//remember Q&A for this card
		$q=$title;
			
		$a=$dom->saveHTML(); //not saveXML -- also anchors with real links to resources
	
		$a=html_entity_decode($a,ENT_QUOTES,"UTF-8"); //represent accented characters in A the same as Q
		//pr($a);
	//	$a=strip_tags($a,"<a><img><h1><h2><p><fieldset><ul><li><strong><b><u><i><br><div><span><meta><table><th><tr><td><link>");	 //maybe allow <pre>, disallows <font>
		$a=strip_tags($a,"<a><img><p><ol><ul><li><br><table><th><tr><td>");	 //maybe allow <pre>, disallows <font>
		//excluded tags 11-14-2012: <script><input><code>
		//pr($a,1);
			
		//remove these from question
		$bad = array('.htm','.html','.doc','.docx','.xls','.xlsx','.ppt','.pptx'); //occur if imported folders
		$q=str_replace($bad,'',$q);
		$q= $q ? $q : " ";
		
		//cleanup - remove question in initial part of answer (e.g. quickly pasting into Evernote client or importing)
		$x=strpos($a,$q);
			
		//don't remove identical Q&A if A is Q -- e.g. notes containing just links to PDF
		$qr = trim(strip_tags($q)); //q length
		$ar = trim(strip_tags($a)); //a length
	
		//remove stray slashes so preg_replace doesn't get confused
		$qr = str_replace( '/', '\/', $qr );
		$ar = str_replace( '/', '\/', $ar );
		
		try{
			//if the first words of A match Q, remove that first instance 
			if (strpos($ar,$q)===0)
				$a=preg_replace('/'.$qr.'/', "", $a, 1); //http://stackoverflow.com/questions/1252693/php-str-replace-that-only-acts-on-the-first-match
			}catch(Exception $e) {
				print_r($e);die();
		}
		
		//remove initial line breaks
		$x=strpos($a,"<br/>");
		$x ? $a=substr_replace($a,"",$x,strlen(("<br/>"))) : FALSE;
		$x=strpos($a,"<br/>");
		$x ? $a=substr_replace($a,"",$x,strlen(("<br/>"))) : FALSE;
	
		//NOTE: If your HTML includes complex UL and especially LI with style, JQuery Mobile may interpret it as something you want to hide. (e.g., en vue de in Fr Wiktionary) - 7-31-2013
		//$a= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $a); //--REMOVE STYLE
	
		//other cleanup
		$q = trim($q);
		$a = trim($a);
		
		//get usn -- TODO (slow)
		foreach ($n->notes as $key=>$val)
			if ($val->guid==$guid) {
				$usn = $val->updateSequenceNum;
		}
	}
	
	$vars['cards'][]=array('Q'=>$q,'A'=>$a,'E'=>$edit);
	saveNote($guid,$g,$usn,$q,$a,$edit); //g is notebook guid
	return $vars;	
}

/*
 * http://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time
 */
function humanTiming ($time)
{

	$time = time() - $time; // to get the time since that moment

	$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
	);

	foreach ($tokens as $unit => $text) {
		if ($time < $unit) continue;
		$numberOfUnits = floor($time / $unit);
		return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
	}

}

/*
 * @param	text	$txt	text of error message
 * handle errors by redirecting to an error page
 */
function oops($txt) {
	$_SESSION['lastError'] = $txt;
	header ('Location: /oops');
	exit();
}


?>


