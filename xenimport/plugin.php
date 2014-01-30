<?php
/**
 * Xen Virtual Machine Importer
 *
 * Scans for and imports Xen virtual machines on a network
 * 
 * @author Tyler Montgomery <tylerfixer@thecubed.com>
 * @license GPLv3
 * 
 */


$tab['depot']['xen_import'] = 'Xen VM Import';
$tabhandler['depot']['xen_import'] = 'showImportTab';

$ophandler['depot']['xen_import']['discoverVMs'] = 'discoverVMs';
$ophandler['depot']['xen_import']['clearList'] = 'clearList';

$sesskey = 'xenImport';

include(dirname(__FILE__).'/include/profiler.php');
$profiler = new cfProfiler("/tmp/cfprofiler", 0660);

/** Render the main interface tab for Xen VM Import
*/
function showImportTab() {
	global $profiler;
	$profiler->marker("begin showImportTab");

	global $sesskey;
	$xtpl = array();
	
	$profiler->marker("showImportTab - populating template");

	// populate template, so we're not referencing session all the time
	if (isset($_SESSION[$sesskey])) {
		$xtpl = $_SESSION[$sesskey];
	}

	$profiler->marker("showImportTab - end populate template");

	// Finalize data and output template
	include(dirname(__FILE__).'/views/importtab.php');

	$profiler->marker("end showImportTab");	
}

/** Handle POST data from form, and feed it into the session.
	Should only be called internally by RackTables upon clicking the View's submit button
*/
function discoverVMs() {
	global $profiler;
	$profiler->marker("begin discoverVMs");

	global $sesskey;

	set_time_limit(300);

	// set session
	session_start();

	// clear out the last run, to remove any accumulated detritus
	$_SESSION[$sesskey] = array();

	// nope, we can't have broken anything just yet.	
	$errors = FALSE;

	// switch on the import type
	switch ($_POST['importtype']) {
		case 'mass':
			$profiler->marker("discoverVMs - mass scan selected");
			// validate and massage mass import data
			$postKeys = array(
				"xenMasters",
				"xenUser",
				"xenPass"
			);	
			$errors = validatePost($postKeys);

			if (!$errors) {
				// create masters array
				$tmpmasters = explode("\n", $_POST['xenMasters']);
				foreach ($tmpmasters as $master) {
					$new['host'] = $master;
					$new['user'] = $_POST['xenUser'];
					$new['pass'] = $_POST['xenPass'];
					$xenMasters[] = $new;
					$profiler->marker("discoverVMs - scheduled to check ".$new['host']);
				}
				//$_SESSION[$sesskey]['xenMasters'] = $xenMasters;
			}
			break;
		case 'perhost':
			$profiler->marker("discoverVMs - per-host scan selected");
			// validate and massage per-host data
			$postKeys = array(
				"masterlist"
			);
			$errors = validatePost($postKeys);

			if (!$errors) {
				// create masters array
				foreach ($_POST['masterlist'] as $masterjson){
					$master = json_decode($masterjson);
					$new['host'] = $master->host;
					$new['user'] = $master->user;
					$new['pass'] = $master->pass;
					$xenMasters[] = $new;
					$profiler->marker("discoverVMs - scheduled to check ".$new['host']);
				}
			}
			break;

		case 'file':
			$_SESSION[$sesskey]['errors'][] = "File import not implemented!";
			return FALSE;
			break;
	}

	
	// check for processing errors
	if ($errors) {
		// abandon ship! abort this function and show the errors. they're in the session, so no cleanup needed
		return FALSE;
	} else {
		// no errors? great! clear the errors list just in case the user corrected them from a previous run
		unset($_SESSION[$sesskey]['errors']);
	}

	// add the tags to our session so they persist
	$_SESSION[$sesskey]['vmpool_tag'] = $_POST['pooltags'][0];
	$_SESSION[$sesskey]['vm_tag'] = $_POST['vmtags'][0];
	
	$profiler->marker("discoverVMs - about to include xencrawl");

	// import the xen library and init the crawler
	include(dirname(__FILE__).'/include/xencrawl.php');
	$crawler = new XenCrawler();
	
	// crawl the xen pool, and save it to the session
	foreach ($xenMasters as $master) {
		try {
			$profiler->marker("discoverVMs - crawling ".$master['host']);

			// attempt to crawl, and catch errors
			$_SESSION[$sesskey]['vms'][] = $crawler->crawl($master['host'], $master['user'], $master['pass']);

			$profiler->marker("discoverVMs - finished ".$master['host']);
		} catch (APIException $e) {
			// add the error to the list, and mooooove on
			$_SESSION[$sesskey]['errors'][] = "XENAPI Error from ".$master['host']." : ".$e->getMessage();

			$profiler->marker("discoverVMs - Error crawling ".$master['host']);
			continue;
		}
	}

	$profiler->marker("end discoverVMs");	

	/*
	$_SESSION[$sesskey]['vms'] = array(
		array(
			"name" => "VMPoolSample01",
			"virtuals" => array(
				array("name" => "potato", "ip" => "172.16.1.1", "vmuuid" => "abcdef", "ostype" => "someOS")
			)
		)
	);
	*/

}

/** Clear any session keys related to this app
*/
function clearList() {
	global $sesskey;
	// start session, and clear our key.
	session_start();
	unset($_SESSION[$sesskey]);
}

/** Validate Post keys to ensure that there's no missing fields
*/
function validatePost($postKeys){
	global $sesskey;

	// add our extra required post keys
	$postKeys[] = "pooltags";
	$postKeys[] = "vmtags";

	// do some rudimentary input validation checking
	// to ensure that the post key is set, add it to the array above
	foreach ($postKeys as $postKey) {
		if (!isset($_POST[$postKey]) || empty($_POST[$postKey])){
			$_SESSION[$sesskey]['errors'][] = "Error, ".$postKey." not set";
			$errors = TRUE;
		}
	}

	// these are special cases, only ONE tag should be allowed.
	// while I could use a radioGroup, then I'd have to rewrite RackTables' UI code to do that.
	// TODO: don't be lazy, use radiogroups
	if (count($_POST['pooltags']) > 1) {
		$_SESSION[$sesskey]['errors'][] = "Choose only ONE tag for the new VM pool";
		$errors = TRUE;
	}
	if (count($_POST['vmtags']) > 1) {
		$_SESSION[$sesskey]['errors'][] = "Choose only ONE tag for the newly imported VMs";
		$errors = TRUE;
	}
	return $errors;

}

?>
