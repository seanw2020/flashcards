<?php

/* CookieCheck
 *
 * This script provides a function for checking whether or not a visitor
 * accessing one of your PHP scripts has cookies enabled.
 *
 * This script is designed to be easily included as part of an existing PHP
 * script. To use the script, simply include this file into your custom script
 * and run the command cc_cookie_cutter(). The return value of this function is
 * TRUE if cookies are enabled and FALSE if they are disabled. The script also
 * sets the global value $CC_ERROR_MSG to something other than NULL if an error
 * or warning was generated during the running of cc_cookie_cutter(). In the
 * case where an error was generated, cc_cookie_cutter() will return FALSE. If a
 * warning only was generated, cc_cookie_cutter() will return normally (i.e.
 * TRUE if cookies are enabled and FALSE if cookies are disabled).
 *
 * All externally visible tokens are prefixed with 'cc_', 'CC_', 'cc-', 'CC-',
 * '_cc_' or '_CC_'. Please be aware that if your script uses those prefixes, 
 * naming conflicts could potentially arise.
 *
 * This script sends headers as part of its logic. This means that this script
 * needs to be included and the cc_cookie_cutter() function called before any
 * output (including whitespace) is sent. This may necessitate the use of PHP's
 * output control functions (ob_start, ob_flush, ob_end_flush, etc).
 *
 *
 * EXAMPLE USAGE:
 *  ...
 *  include_once(cookiecheck.php);
 *  $cookies_enabled = cc_cookie_cutter();
 *  if (!$cookies_enabled) {
 *      // Code to display a warning that cookies are unavailable
 *      ...
 *  }
 *  // Execution only reaches this point if cookies are available
 *  ...
 *
 *
 * Written by Jath Palasubramaniam
 *
 * Copyright 2008 Laden Donkey Studios. All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */





/* SETTINGS - May need modification */

// CC_SESSION_PATH - directory to store temporary session data
define('CC_SESSION_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../tmp/cc_sessions');

// CC_COOKIE_LIFE_DAYS - how many days a test is valid for; 0 for session only
define('CC_COOKIE_LIFE_DAYS', (7));

// CC_COOKIE_PATH - path on your site that the test is valid for; / means all
define('CC_COOKIE_PATH', '/');

// CC_PROTOCOL - protocol for client-server communication; http, https, etc
define('CC_PROTOCOL', 'http');

// CC_SESSION_ID_STEM - prefix to use with session ids for this script
define('CC_SESSION_ID_STEM', 'cc-sessid-');





/* SETTINGS - Unlikely to need modification */

// CC_QUERY - variable name used in the GET query string for this script
define('CC_QUERY', 'cc_status');

// CC_COOKIE - name of the test cookie sent by this script
define('CC_COOKIE', 'cc_test');

// CC_SESSION_NAME - name of sessions used by this script
define('CC_SESSION_NAME', 'cc_session');

// CC_SESSION_TIMEOUT - time in seconds before the test is aborted
define('CC_SESSION_TIMEOUT', (60));





/* GLOBALS - Accessible after this page has been included */
// $CC_ERROR_MSG - will be modified by cc_cookie_cutter() if an error occurs
$CC_ERROR_MSG = NULL;





/* FUNCTIONS - Public */

// cc_cookie_cutter - run a test to see whether cookies are enabled or not
// 	Takes no arguments
//  Returns TRUE if cookies are enabled, FALSE if they are disabled
function cc_cookie_cutter() {

	// Declare $CC_ERROR_MSG to refer to the global variable
	global $CC_ERROR_MSG;

	if (isset($_COOKIE[CC_COOKIE])) {

		// Cookies are enabled
		if (isset($_GET[CC_QUERY])) {
			// Reload the page using the initial query string
			if (!_cc_initialise_session_settings()) {
				$CC_ERROR_MSG = 
					'CookieCheck Error: Unable to initialise session settings';
				return FALSE;
			}
			session_id(CC_SESSION_ID_STEM . $_GET[CC_QUERY]);
			session_start();

			// Get the initial query string and prepare it for appending
			$qstring = $_SESSION['_SERVER']['QUERY_STRING'];
			$qstring = ($qstring == '' ? '': '?') . $qstring;
			// Get needed $_SERVER variables
			$http_host = $_SESSION['_SERVER']['HTTP_HOST'];
			$php_self = $_SESSION['_SERVER']['PHP_SELF'];
			session_write_close();
			// Reload the page, the session id will be propogated in the cookie
			header('Location: ' . CC_PROTOCOL . '://' . $http_host . $php_self .
				$qstring);
			exit();
			// Do not continue; rather exit and reload the page
		}
		// Restore any globals that are saved
		$old_session_settings = _cc_save_session_settings();
		if (!_cc_initialise_session_settings()) {
			$CC_ERROR_MSG = 
				'CookieCheck Error: Unable to initialise session settings';
			return FALSE;
		}
		session_id(CC_SESSION_ID_STEM . strval($_COOKIE[CC_COOKIE]));
		session_start();
		_cc_restore_globals();
		session_destroy();
		if (!_cc_restore_session_settings($old_session_settings)) {
			$CC_ERROR_MSG = 
				'CookieCheck Warning: Unable to restore session settings';
		}

		return TRUE;

	} else {

		// Cookies are either disabled or not yet tested for
		if (isset($_GET[CC_QUERY])) {
			// Restore globals as they were previously sent
			$old_session_settings = _cc_save_session_settings();
			if (!_cc_initialise_session_settings()) {
				$CC_ERROR_MSG = 
					'CookieCheck Error: Unable to initialise session settings';
				return FALSE;
			}
			session_id(CC_SESSION_ID_STEM . strval($_GET[CC_QUERY]));
			session_start();
			_cc_restore_globals();
			session_destroy();
			if (!_cc_restore_session_settings($old_session_settings)) {
				$CC_ERROR_MSG =
					'CookieCheck Warning: Unable to restore session settings';
			}
			// Continue on and return FALSE as cookies are disabled
		} else {
			// Save globals as we are going to reload this page
			if (!_cc_initialise_session_settings()) {
				$CC_ERROR_MSG = 
					'CookieCheck Error: Unable to initialise session settings';
				return FALSE;
			}
			$session_id = strval(mt_rand());
			session_id(CC_SESSION_ID_STEM . $session_id);
			session_start();
			_cc_save_globals();
			session_write_close();

			// Append the session id to the end of the existing query string
			$qstring = $_SERVER['QUERY_STRING'] . 
				($_SERVER['QUERY_STRING'] == '' ? '' : '&') . CC_QUERY . '=' .
				$session_id;
			// Send a test cookie
			setcookie(CC_COOKIE, $session_id, 
				(time() + CC_COOKIE_LIFE_DAYS * 24 * 60 * 60), CC_COOKIE_PATH);
			header('Location: ' . CC_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . 
				$_SERVER['PHP_SELF'] . '?' . $qstring);
			exit();
			// Do not continue; rather exit and reload the page
		}
		
		return FALSE;

	}

}





/* FUNCTIONS - Private */

// _cc_initialise_session_settings - configure the session settings
// 	Takes no arguments
//  Returns TRUE on success, FALSE on failure
function _cc_initialise_session_settings() {

	// Initialise the session save path
	$old_session_save_path = session_save_path();
	session_save_path(session_save_path() . CC_SESSION_PATH);
	$session_save_path = session_save_path();
	if (!file_exists($session_save_path)) {
		if (!mkdir($session_save_path, 0755, TRUE)) {
			session_save_path($old_session_save_path);
			return FALSE;
		}
	} else {
		if (!(is_dir($session_save_path) && is_readable($session_save_path) && 
			is_writeable($session_save_path))) {
			session_save_path($old_session_save_path);
			return FALSE;
		}
	}

	// Initialise session garbage collection
	ini_set('session.gc_maxlifetime', CC_SESSION_TIMEOUT);
	ini_set('session.gc_probability', 1);
	ini_set('session.gc_divisor', 1);

	// Initialise the use of cookies for sessions
	ini_set('session.use_cookies', 0);

	// Initialise the session name
	session_name(CC_SESSION_NAME);

	return TRUE;

}


// _cc_save_session_settings - save the default/custom session settings
// 	Takes no arguments
//  Returns an array containing the old settings
function _cc_save_session_settings() {

	// Save the session save path
	$old_session_settings['save_path'] = session_save_path();

	// Save the session garbage collection
	$old_session_settings['gc_maxlifetime'] = ini_get('session.gc_maxlifetime');
	$old_session_settings['gc_probability'] = ini_get('session.gc_probability');
	$old_session_settings['gc_divisor'] = ini_get('session.gc_divisor');

	// Save the user of cookies for sessions
	$old_session_settings['use_cookies'] = ini_get('session.use_cookies');

	// Save the session name
	$old_session_settings['name'] = session_name();

	return $old_session_settings;

}
	

// _cc_restore_session_settings - restore the default/custom session settings
// 	Takes an array containing the old settings
//  Returns TRUE on success, FALSE on error
function _cc_restore_session_settings($old_session_settings) {

	// Check that argument is valid
	if (!is_array($old_session_settings)) {
		return FALSE;
	}

	// Restore the session save path
	session_save_path($old_session_settings['save_path']);

	// Restore session garbage collection
	ini_set('session.gc_maxlifetime', $old_session_settings['gc_maxlifetime']);
	ini_set('session.gc_probability', $old_session_settings['gc_probability']);
	ini_set('session.gc_divisor', $old_session_settings['gc_divisor']);

	// Restore the use of cookies for sessions
	ini_set('session.use_cookies', $old_session_settings['use_cookies']);

	// Restore the session name
	session_name($old_session_settings['name']);

	return TRUE;

}


// _cc_save_globals - saves the values of the PHP global arrays
// 	Takes no arguments
//  Returns no value
function _cc_save_globals() {

	// Serialize and save each of the super globals
	// $GLOBALS is not saved in this manner as it seems to dynamically reference
	//  each of the other super globals anyway
	// Explicitly saving or restoring $GLOBALS causes some sort of error, though
	//  I can't figure out exactly what it is
	// Suffice to say, $GLOBALS works as expected without explicitly doing
	//  anything to save or restore it (I think)
	$_SESSION['_FILES'] = $_FILES;
	$_SESSION['_SERVER'] = $_SERVER;
	$_SESSION['_ENV'] = $_ENV;
	$_SESSION['_COOKIE'] = $_COOKIE;
	$_SESSION['_GET'] = $_GET;
	$_SESSION['_POST'] = $_POST;
	$_SESSION['_REQUEST'] = $_REQUEST;
	// Set a flag to signify that we are storing the globals to a fresh session
	$_SESSION['cc_flag'] = TRUE;

	return;

}


// _cc_restore_globals - restores the values of the PHP global arrays
// 	Takes no arguments
//  Returns no value
function _cc_restore_globals() {

	// Only restore the globals if we have freshly saved them
	if (isset($_SESSION['cc_flag'])) {
		// Unserialize and restore the super globals removing them from the 
		//  session variable once done
		// $GLOBALS is not restored in this manner as it seems to dynamically
		//  reference each of the other super globals anyway
		// Explicitly saving or restoring $GLOBALS causes some sort of error,
		//  though I can't figure out exactly what it is
		// Suffice to say, $GLOBALS works as expected without explicitly doing
		//  anything to save or restore it (I think)
		$_FILES = $_SESSION['_FILES'];
		unset($_SESSION['_FILES']);
		$_SERVER = $_SESSION['_SERVER'];
		unset($_SESSION['_SERVER']);
		$_ENV = $_SESSION['_ENV'];
		unset($_SESSION['_ENV']);
		$_COOKIE = $_SESSION['_COOKIE'];
		unset($_SESSION['_COOKIE']);
		$_GET = $_SESSION['_GET'];
		unset($_SESSION['_GET']);
		$_POST = $_SESSION['_POST'];
		unset($_SESSION['_POST']);
		$_REQUEST = $_SESSION['_REQUEST'];
		unset($_SESSION['_REQUEST']);
		// Remove the flag that identified the session information as fresh
		unset($_SESSION['cc_flag']);
	}

	return;

}


// No trailing whitespace after the PHP close tag to avoid sending whitespace
?>
