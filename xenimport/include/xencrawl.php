<?php
/**
 * Xen Virtual Machine Importer - crawler library
 *
 * Searches for virtual machines on a given virtual machine pool host
 *
 * @author Tyler Montgomery <tylerfixer@thecubed.com>
 * @license GPLv3
 *
 */

include(dirname(__FILE__).'/xenapi.php');

class XenCrawler {

	/** Crawl a Xen pool master and return an array of servers and their specs
		@param	host		The hostname of the Xen pool master to crawl
		@param	login		The username to connect to XAPI with
		@param	password	The password to connect with. Not sent via HTTPS, so use caution.
		@returns	Array of servers with named values of key metrics, see src for more.
	*/
	function crawl($host,$login,$password) {
		// Connect to Xen
		$xenserver = new XenApi("http://".$host, $login, $password);

		// Get a reference to each VM
		$vmRefList = $xenserver->VM_get_all();

		// Create our final array
		$vmArray = array();

		// Get the pool name
		$poolRef = $xenserver->pool_get_all();
		$vmArray['name'] = $xenserver->pool_get_name_label($poolRef[0]);

		// Query server about each VM reference
		foreach ($vmRefList as $vm) {
		    $record = $xenserver->VM_get_record($vm);
			
			// Server is powered on, and is NOT a control domain
			if ($record['power_state'] == 'Running' && $record['is_control_domain'] == ''){

				// Create the info array
				$vmInfo = array(
					"name" => $record['name_label'],
					"description" => $record['name_description'],
					"vmuuid" => $record['uuid']
				);

				// Does the record have a metrics reference?
				if (!strstr($record['guest_metrics'],"NULL")){

					// Get the OS version
					$os = $xenserver->VM_guest_metrics_get_os_version($record['guest_metrics']);
					$vmInfo['ostype'] = $os['name'];

					// Get the IP address
					$network = $xenserver->VM_guest_metrics_get_networks($record['guest_metrics']);
					$vmInfo['ip'] = $network['0/ip'];
				}

				// Add it to the array
				$vmArray['virtuals'][] = $vmInfo;
			}
		}

		return $vmArray;
	}

}
?>
