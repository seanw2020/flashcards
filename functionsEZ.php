<?php

/*
 * Sean's functions
*
*/

/*
 * http://stackoverflow.com/questions/13187919/mysqli-php-login-from-cookie-some-sql-injection-prevention-example
 * try to login via the cookie or create a new cookie
 * 
 * return true or redirect
 */
function loginCheck() {
	//leave if already logged in
	if (isset($_SESSION['login']) && $_SESSION['login']==TRUE)
		return TRUE;
	
	//if cookie
	if (isset($_COOKIE['requestToken'])) {
		//check db
		$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
		$s=$m->prepare("SELECT sid,userId,requestToken,accessToken,accessTokenSecret,noteStoreUrl,webApiUrlPrefix,shard,ip 
				        FROM sess WHERE requestToken=?");
		$s->bind_param('s',$rt);
		$rt=$_COOKIE['requestToken'];
		$s->execute();
		$s->store_result();
		$n=$s->num_rows;
		//match db?
		if ($n) {
			if ($n==1) { // 1 match
				$_SESSION['login']=TRUE;
				$s->bind_result($_SESSION['sid'],$_SESSION['userId'],$_SESSION['requestToken'],$_SESSION['accessToken'],
						$_SESSION['accessTokenSecret'],$_SESSION['noteStoreUrl'],$_SESSION['webApiUrlPrefix'],
						$_SESSION['shard'],$_SESSION['ip']);
				$s->fetch();
			}
			elseif ($n>1) { // >1 match
				$_SESSION['login']=FALSE;
				$_SESSION['lastError']= t('More than one result in database for this cookie. Not logged in!');
				oops();
			}
		}else { // no match
			$_SESSION['login']=FALSE;
			header('Location: /logout');
			exit();
		}
	}else // no cookie
		$_SESSION['login']=FALSE;
	
	return ($_SESSION['login']);
	 
}

/*
 * wrapper to make and store the cookie
*/
function doCookie() {
	bakeCookie();
	storeCookie();
	header('Location: /select-notebook');
	exit();
}

/*
 * create a cookie
 */
function bakeCookie() {
	//create cookie
	$year=time()+86400*365; //86400 = 1 day
	setcookie('requestToken',$_SESSION['requestToken'],$year);
}


/*
 * after logging in, remember it
 */
function storeCookie() {
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	
	//store in db
	//ORDER: uid,userId (EV's),requestToken,accessToken,accessTokenSecret,noteStoreUrl,webApiUrlPrefix,shard,ip
	$s=$m->prepare("INSERT INTO sess VALUES (?,?,?,?,?,?,?,?,?)");
	handleDbErr();
	$s->bind_param('sssssssss',$uid,$uidEv,$rt,$at,$ats,$ns,$wu,$sh,$ip);
	$uid=NULL;
	$uidEv=$_SESSION['userId'];
	$rt=$_SESSION['requestToken'];
	$at=$_SESSION['accessToken'];
	$ats=$_SESSION['accessTokenSecret'];
	$ns=$_SESSION['noteStoreUrl'];
	$wu=$_SESSION['webApiUrlPrefix'];
	$sh=$_SESSION['shard'];
	$ip=$_SERVER["REMOTE_ADDR"];
	$s->execute();
	
	//store in session
	$_SESSION['uid']=$s->insert_id;
}

/*
 * save a note to our local database
 * 
* @param 	string			$guid			guid
* @param 	string			$g				notebook guid
* @param 	string			$usn			updateSequenceNum
* @param 	string			$q				question in html
* @param 	string			$a				answer in html
* @param 	string			$e				edit link in html
*/
function saveNote($guid,$g,$usn,$q,$a,$edit) {
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	handleDbErr();
	$title = $q;
	$content = $a;
	$u = $_SESSION['userId'];

	//if guid not in db, cache it
	if (isCachedNote($guid)==FALSE) {
		$s=$m->prepare("INSERT INTO notes VALUES (?,?,?,?,?,?,?)");
		$s->bind_param('sssssss',$guid,$g,$usn,$title,$content,$edit,$u);
		$s->execute();
	}else {//does exist
		//if higher USN(EN) update
	}
}


/*
 * save a notebooks meta data to our local database for select-notebook
*
* @param 	array			$notebooks		array of EDAM Notebook objects
*/
function saveNoteBooks($notebooks) {
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	handleDbErr();

	$u = $_SESSION['userId'];
	//remove existing local entries
	$s=$m->prepare("DELETE FROM notebooks WHERE userId=?");
	$s->bind_param('s',$u);
	$s->execute();
	$s->store_result();
	$n=$s->num_rows;

	//insert latest version
	foreach ($notebooks as $key=>$val) {
	//if guid not in db, cache it
		$s=$m->prepare("INSERT INTO notebooks VALUES (?,?,?,?,?,?)");
		$s->bind_param('dsssss',$u,$guid,$name,$usn,$nada,$stack);
		$guid = $val->guid;
		$name = $val->name;
		$usn = $val->updateSequenceNum;
		$nada = NULL;
		$stack = $val->stack;
		$s->execute();
	}
}
/*
 * check db for cached note
*
* @param 	string			$guid			guid
* Return	integer			$n				number of matching guids
*/
function isCachedNote($guid) {
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	handleDbErr();
	
	//already exists in db?
	$s=$m->prepare("SELECT guid FROM notes WHERE guid=?");
	$s->bind_param('s',$guid);
	$s->execute();
	$s->store_result();
	$n=$s->num_rows;
	
	return $n;
}

/*
 * get cached note
*
* @param 	string			$guid			guid
* Return	integer			$n				number of matching guids
*/
function getCachedNote($guid) {
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	handleDbErr();

	//already exists in db?
	$s=$m->prepare("SELECT updateSequenceNum, title, content, edit FROM notes WHERE guid=?");
	$s->bind_param('s',$guid);
	$s->execute();
	$s->store_result();
	$s->bind_result($cNote['usn'], $cNote['q'], $cNote['a'], $cNote['edit']);
	$s->fetch();
	
	return $cNote;
}

/*
 * get cached notebook
*
* @param 	string			$nbGuid			guid of notebook
* Return	stdClass		$r				notes and note count
*/
function getCachedNotebook($nbGuid) {
	$r = new stdClass;
	$i = 0;
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	$notes = array();
	handleDbErr();
	
	//already exists in db?
	$s=$m->prepare("SELECT updateSequenceNum, title, content, edit FROM notes WHERE nbGuid=?");
	$s->bind_param('s',$nbGuid);
	$s->execute();
	$s->store_result();
	$s->bind_result($usn, $q, $a, $edit);
	while ($s->fetch())
		$notes[$i++]=array('usn'=>$usn, 'q'=>$q, 'a'=>$a, 'edit'=>$edit);
	$t = $s->affected_rows;
	
	$r->notes=$notes;
	$r->totalCached=$t;
	$_SESSION['showCachedNotebook']=TRUE;
	return $r;
}

/*
 * get cached notebooks -- a list for select-notebook
*
* @param 	string			$nbGuid			guid of notebook
* Return	stdClass		$r				notes and note count
*/
function getCachedNotebooks() {
	$r = new stdClass;
	$i = 0;
	$m = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	$notebooks = array();
	handleDbErr();

	//already exists in db?
	$s=$m->prepare("SELECT guid, name, updateSeqNum, stack, total FROM notebooks WHERE userId=?");
	$s->bind_param('s',$_SESSION['userId']);
	$s->execute();
	$s->store_result();
	$s->bind_result($guid, $name, $usn, $stack, $total);
	while ($s->fetch()) {
		$r = new stdClass;
		$r->guid=$guid; 
		$r->name=$name; 
		$r->usn=$usn;
		$r->stack=$stack;
		$r->total=$total;
		$notebooks[$i++]=$r;
	}
	$t = $s->affected_rows;
	return $notebooks;
}

/*
 * output errors
 */
function handleDbErr() {
	/* check connection */
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
}
/*
 * debugging wrapper
*/
function pr($var=NULL, $die=FALSE) {
	if (!isset($var)) {
		print 'SESSION: <pre>'; print_r($_SESSION); print '</pre>';
		print 'COOKIE: <pre>'; print_r($_COOKIE); print '</pre>';
		die();
	}
	else {
		print '<pre>'; print_r($var); print '</pre>';
		if ($die)
			die();
	}
	
}

/*
 * allow translateable strings
 */
function t($str) {
	return $str;
}

/*
 * common to all pages
 */
function bootstrap() {
	session_start();
	require_once('config.php');
	require_once('functionsEV.php');
	if (!loginCheck()) {
		header('Location: /login');
		exit();
	}
}

/*
 * return a resource url
 */
function resourceUrl($authToken, $resource, $resize = 300) {
	//build URL
	if (!$resize)
		$url=EVERNOTE_SERVER."/shard/".$_SESSION['shard']."/res/".$resource->guid; //originals
	else
		$url=EVERNOTE_SERVER."/shard/".$_SESSION['shard']."/thm/res/".$resource->guid."?size={$resize}"; //thumbnail
	return $url;
}

/* DELETE ME 11-3-2012
 * http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
 */
function do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
			'method' => 'POST',
			'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url");// $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url");//, $php_errormsg");
	}
	return $response;
}

/*
 * remove given DOM elements
 * http://stackoverflow.com/questions/4663294/php-dom-stripping-span-tags-leaving-their-contents
 */
function DOMRemove(DOMNode $from) {
	$sibling = $from->firstChild;
	do {
		$next = $sibling->nextSibling;
		$from->parentNode->insertBefore($sibling, $from);
	} while ($sibling = $next);
	$from->parentNode->removeChild($from);
}

/*
 * http://code.google.com/p/php-mobile-detect/
 */
function isMobile() {
	//return 1;
	//if(!isset($user_agent)) {
		//$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	//}
	//return (strpos($user_agent, 'iPhone') !== FALSE);
	
	include_once 'lib/mobile_detect/Mobile_Detect.php';
	$detect = new Mobile_Detect();

	//var_dump($detect->isTablet());die();

	//if a mobile device but not a tablet (don't resize on tablets)
	if ($detect->isMobile() && !$detect->isTablet()) {
		return TRUE;
	}else
		return FALSE;
}
