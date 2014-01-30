<?php
$tab['depot']['xen_import'] = 'Xen VM Import';
$tabhandler['depot']['xen_import'] = 'showImportTab';

$ophandler['depot']['xen_import']['discoverVMs'] = 'discoverVMs';

function showImportTab() {
	global $tagtree;
	echo "<h2 style='padding-left: 10px;'>Xen VM Import</h2>";
        startPortlet("Errors");
	echo "<div style='color: red;font-weight: bold;'>";
        echo "Can only have one tag selected!";
	echo "</div><br>";
        finishPortlet();


	echo "<style>label {display: block; font-weight: bold;} th {background-color: #eee; }</style>
		<div style='float: left; width: 30%'>";

	startPortlet("Options");
	?>
	<form method='post' name='discover' action='?module=redirect&page=depot&tab=xen_import&op=discoverVMs'>
		<table align='center'>
			<tr>
				<td>
					<label for='vmMasters'>Xen Pool Masters (IP, one per line)</label>
					<textarea rows='20' cols='20' name='vmMasters'></textarea><br><br>
					<input type='submit' value='Discover VMs'>
				</td>
				<td>
					<label for='xenUser'>Username:</label><input type='text' length='20' name='xenUser'>
                                        <label for='xenPass'>Password:</label><input type='password' length='20' name='xenPass'><br><br>
					<strong>Pool Tag</strong>
					<div class=tagselector><table border=0 align=center cellspacing=0 class="tagtree">
	<?php
					printTagCheckboxTable ('pooltags', array(), array(), $tagtree, 'object');
	?>
					</table></div>
					<br><br>
					<strong>VM Tag</strong>
					<div class=tagselector><table border=0 align=center cellspacing=0 class="tagtree">
	<?php
					printTagCheckboxTable ('vmtags', array(), array(), $tagtree, 'object');
	?>
					</table></div>
				</td>
			</tr>
		</table>
	<?php

	finishPortlet();

	echo "</form></div>
		<div style='float: left; width: 70%'>";
	startPortlet("VMs Discovered");
	echo "<strong>No virtual machines discovered.</strong><br><br>"; ?>

	<table border="1" bordercolor="CCCCCC" cellspacing="0" style="width: 100%;">
		<tr>
			<th></th>
			<th>VM Name</th>
			<th>IP Address</th>
			<th>Guest OS</th>
			<th>VM UUID</th>
		<tr>
		
		<tr>
			<th colspan="5">Pool TX-C5220-Pool1</th>
		</tr>

		<tr>
			<td><input type="checkbox" name="check1"></td>
			<td>TX-Box01</td>
			<td>192.168.129.150</td>
			<td>Centos 5.2 (Nawahee)</td>
			<td>somereallynonsenselongnumberhereforaUID</td>
		</tr>
	</table>
	<br>
	<strong>Import VM Pools into:</strong> Pools<br>
	<strong>Import VMs into:</strong> Virtuals
	<br><br>
	<input type="submit" name="import" value="Import Selected VMs and pools">
	<input type="submit" name="clear" value="Clear discovered VMs">
	
	<?php

	finishPortlet();

	startPortlet("Debug");
	echo "<div style='text-align: left;'>";
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>";
	finishPortlet();
	echo "</div>";

	echo "</div><br clear='both'>";

	startPortlet();
	echo "XenVMImport by Tyler Montgomery";
	finishPortlet();
}

function discoverVMs() {
	session_start();
	echo "<h1>TEST</h1>";
//	$_SESSION['RTLT'] = "def";
	$_SESSION['vmDiscovered'] = "abc";
	print_r($_SESSION);
	die(session_id());
}


?>
