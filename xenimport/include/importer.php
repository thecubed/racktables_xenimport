<?php
/**
 * Xen Virtual Machine Importer - import library
 *
 * Imports the discovered virtual machines into the Racktables database
 *
 * @author Tyler Montgomery <tylerfixer@thecubed.com>
 * @license GPLv3
 *
 */




/** Import a virtual machine pool into RackTables 
	@param pool	array containing the pool of servers to import
	@param poolTag	the tag to import the pool under
	@param vmTag	the tag to import the individual VMs under
*/
function importXenPool($pool, $poolTag, $vmTag) {
	// set up status
	$status = array();

	// check if our VM pool exists already...
	$vmPool = getSearchResultByField('RackObject', array('id'), 'name', $pool['name'], '', 2);

	if (!$vmPool) {
		// Create the VM pool with the proper name, and in the proper tags
		$vmPoolID = createVMPool($pool['name'],$poolTag);
	} else {
		// VM pool exists already, use it for all new VMs
		$vmPoolID = $vmPool[0]['id'];
	}

	// grab tag
	$vmTagList = array($vmTag);

	// get VM type from dictionary
	$vmTypeID = usePreparedSelectBlade("select * from Dictionary where dict_value = 'VM'")->fetch(PDO::FETCH_ASSOC);
	$vmTypeID = $vmTypeID['dict_key'];

	// import VM into the pool
	foreach ($pool['virtuals'] as $obj){
		// Create the virtual machine if there's no object with the same name (RackTables doesn't like duplicate names)
		if (!createVMInPool($obj['name'], $obj['ip'], $obj['vmuuid'], $obj['osinfo'], $vmTagList, $vmPoolID, $vmTypeID)) {
			$status['duplicates'][] = $obj['name'];
		}
	}

	return $status;
}

/** Create a Virtual Machine in the specified pool
	@param name 	The name of the new Virtual Machine
	@param ip		The IP address to assign the VM
	@param asset	Asset tag to use (VM UUID)
	@param osInfo	Operating System information (to be stored as a comment)
	@param taglist	Array of tags to use for this new object
	@param poolID	Virtual Machine pool to parent this object to
*/
function createVMInPool($name, $ip, $asset, $osInfo, $taglist, $poolID, $vmTypeID) {
	// make sure there's no duplicates
	if (getSearchResultByField('RackObject', array('id'), 'name', $name, '', 2)) {
		return FALSE;
	}

	// commitAddObject ($new_name, $new_label, $new_type_id, $new_asset_no, $taglist = array())
	$newObject = commitAddObject($name, $name, $vmTypeID, $asset, $taglist);

	// add IP address to new object
	if (!empty($ip)) {
		bindIpToObject(ip_parse($ip), $newObject, 'eth0','regular');
	}
	
	// add OS info to new object
	if (!empty($osInfo)){
		usePreparedUpdateBlade('Object', array('comment' => $osInfo), array('id' => $newObject));
	}
	
	// set container
	//commitLinkEntities($parent_entity_type, $parent_entity_id, $child_entity_type, $child_entity_id);
	commitLinkEntities("object", $poolID, "object", $newObject);
}

/** Create a new Virtual Machine Pool
	@param		name	Pool name
	@param		tagid	Tag ID to use for this new VM
	@returns 	poolID	ID of new VMPool object
*/
function createVMPool($name, $tagid){
	// Get "VM Cluster" dict key
	$vmClusterTypeID = usePreparedSelectBlade("select * from Dictionary where dict_value = 'VM Cluster'")->fetch(PDO::FETCH_ASSOC);
	$vmClusterTypeID = $vmClusterTypeID['dict_key'];	
	// Add the pool, with the proper type and tags
	$taglist = array($tagid);
	$poolID = commitAddObject($name,$name,$vmClusterTypeID,'',$taglist);
	
	// return the pool ID
	return $poolID;
}

?>
