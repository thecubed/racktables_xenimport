	
<?php global $tagtree, $taglist; ?>
<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
?>
<style>
	/* keep all styles separate from RackTables main UI */
	.xvmimport label { 
		display: block;
		font-weight: bold;
	}
	.xvmimport th {
		background-color: #eee;
	}
	.xvmimport td {
		text-align: left;
		padding-left: 2px;
	}
	.xvmimport td.center {
		text-align: center;
	}
	.xvmimport table.options {
		margin: 0;
	}
	.xvmimport #masterlist {
		width: 260px;
		height: 200px;
	}
	.xvmimport .buttons input {
		width: 70px;
	}

	.xvmimport .tabs {
	  min-width: 320px;
	  position: relative;   
	  min-height: 450px;
	  clear: both;
	  margin: 25px 0;
	  margin-right: 10px;
	}
	.xvmimport .tabs .tab {
	  float: left;
	}
	.xvmimport .tabs .tab label.tablabel {
	  background: #eee; 
	  padding: 10px; 
	  border: 1px solid #ccc; 
	  margin-left: -1px; 
	  position: relative;
	  left: 1px; 
	}
	.xvmimport .tabs .tab [type=radio] {
	  display: none;   
	}
	.xvmimport .tabs .content {
	  position: absolute;
	  top: 36px;
	  left: 0;
	  background: white;
	  right: 0;
	  bottom: 0;
	  padding: 20px;
	  border: 1px solid #ccc; 
	}
	.xvmimport .tabs [type=radio]:checked ~ label.tablabel {
	  background: white;
	  border-bottom: 1px solid white;
	  z-index: 2;
	}
	.xvmimport .tabs [type=radio]:checked ~ label.tablabel ~ .content {
	  z-index: 1;
	}

</style>
<script>
	function finalizeForm() {
		list = document.forms['discover']['masterlist'];
		for (var i=0; i<list.options.length; i++) {
			list.options[i].selected = true;
		} 
	}
	function addHost() {
		info = { 
			"host": document.forms['discover']['addhost'].value,
			"user": document.forms['discover']['adduser'].value,
			"pass": document.forms['discover']['addpass'].value
		};

		if (info.host == "" || info.user == "" || info.pass == "") {
			return false;
		}

		option = document.createElement("option");
		option.text = info.host;
		option.value = JSON.stringify(info);
		document.forms['discover']['masterlist'].add(option);

		 document.forms['discover']['addhost'].value = "";
		 document.forms['discover']['adduser'].value = "";
		 document.forms['discover']['addpass'].value = "";

	}
	function removeHost() {
		selectbox = document.forms['discover']['masterlist'];
		for(var i=selectbox.options.length-1;i>= 0;i--) {
			if(selectbox.options[i].selected == true) {
				try {
					 selectbox.remove(i, null);
				} catch(error) {
					 selectbox.remove(i);
				}

			}
		}
	}

</script>

<div class="xvmimport">

	<h2 style='padding-left: 10px;'>Xen VM Import</h2>

	<?php
		if (isset($xtpl['errors'])) { 	
			startPortlet("Errors");
			
			foreach ($xtpl['errors'] as $error) {
			?>
				<div style='color: red;font-weight: bold;'>
					<?php echo $error ?>
				</div>
				<br>
	<?php
			}
			echo "<a href='?module=redirect&page=depot&tab=xen_import&op=clearList'>Clear Errors</a>";
			finishPortlet();
		}
	 ?>

	<div style='float: left; width: 600px;'>
		<?php startPortlet("Options"); ?>
			<form method='post' name='discover' action='?module=redirect&page=depot&tab=xen_import&op=discoverVMs'>
				<table align="center" class="options">
					<tr>
						<td>
							<div class="tabs">
								<div class="tab">
									<input type="radio" id="import-mass" name="importtype" value="mass" checked>
									<label for="import-mass" class="tablabel">Mass Scan</label>
									<div class="content">
										<label for='xenMasters'>Xen Pool Masters (one per line)</label>
										<textarea rows='16' cols='30' name='xenMasters'>192.168.129.97</textarea><br>
										<label for='xenUser'>Username:</label><input type='text' length='32' name='xenUser'>
										<label for='xenPass'>Password:</label><input type='password' length='32' name='xenPass'><br><br>
									</div>
								</div>
								
								<div class="tab">
									<input type="radio" id="import-per" name="importtype" value="perhost">
									<label for="import-per" class="tablabel">Per-Server Scan</label>
									<div class="content">
										<label for="masterlist">Xen Pool Masters</label>
										<select name="masterlist[]" id="masterlist" multiple>
										</select>
										
										<table>
											<tr>
												<td>
													<label for="addhost">Hostname / IP:</label>
													<input type="text" length="32" name="addhost">
													<label for="adduser">Username:</label>	
													<input type="text" length="32" name="adduser">
													<label for="addpass">Password:</label>
													<input type="password" length="32" name="addpass">
												</td>
												<td class="buttons">
													<input type="button" name="remove" value="Remove" onclick="removeHost();"><br>
													<input type="button" name="add" value="Add" onclick="addHost();">
												</td>
											</tr>
										</table>
									</div>
								</div>

								<div class="tab">
									<input type="radio" id="import-file" name="importtype" value="file">
									<label for="import-file" class="tablabel">File Import</label>

									<div class="content">
										<em>Coming soon!</em>
									</div>
								</div>

							</div>
						</td>
						<td>
							<strong>Pool Tag</strong>
							<div class="tagselector">
								<table border=0 align=center cellspacing=0 class="tagtree">
									<?php printTagCheckboxTable ('pooltags', array(), array(), $tagtree, 'object'); ?>
								</table>
							</div>
							<br>
							<strong>VM Tag</strong>
							<div class="tagselector">
								<table border=0 align=center cellspacing=0 class="tagtree">
									<?php printTagCheckboxTable ('vmtags', array(), array(), $tagtree, 'object'); ?>
								</table>
							</div>
							<br><br>
							<input type='submit' value='Process VMs' onclick="finalizeForm()">
						</td>
					</tr>
				</table>
			</form>
	<?php finishPortlet(); ?>
</div>

<div style='width: auto; overflow: auto; min-width: 500px;/*width: 70%*/'>
	<?php 
		startPortlet("VMs Discovered"); 
		
		if (!isset($xtpl['vms'])) {
	?>
		<strong>Use the menu on the left to scan for virtual machines.</strong>
		<br><br>
	<?php
		} else {
	?>
		<form method='post' name='importVMs' action='?module=redirect&page=depot&tab=xen_import&op=importVMs'>
		<table border="1" bordercolor="CCCCCC" cellspacing="0" style="width: 100%;">
			<tr>
				<th><input type="checkbox" name="checkAll" onclick="boxes = document.getElementsByClassName('importCheckbox'); for (var i=0;i < boxes.length;i++) {boxes[i].checked = this.checked};"></th>
				<th>VM Name</th>
				<th>IP Address</th>
				<th>Guest OS</th>
				<th>VM UUID</th>
			<tr>

			<?php	
				foreach ($xtpl['vms'] as $vmpool) {
			?>
				<tr>
					<th colspan="5"><?php echo $vmpool['name'] ?></th>
				</tr>

				<?php
					foreach ($vmpool['virtuals'] as $vm) {
				?>

					<tr>
						<td class="center"><input type="checkbox" name="import[]" value="<?php echo $vmpool['name']."!@!".$vm['name'] ?>" class="importCheckbox"></td>
						<td><?php echo $vm['name'] ?></td>
						<td><?php echo $vm['ip'] ?></td>
						<td><?php echo $vm['ostype'] ?></td>
						<td><?php echo $vm['vmuuid'] ?></td>
					</tr>

			<?php
					}
				}
			?>
		</table>
		<br>
		
		<table border="1" cellspacing="0" bordercolor="CCCCCC">
			<tr>
				<th colspan="3">Import Tags</th>
			</tr>
			<tr>
				<th width="100"></th>
				<th width="100">ID</th>
				<th width="150">Name</th>
			</tr>
			<tr>
				<th>VM Pool</th>
				<td><?php echo $xtpl['vmpool_tag'] ?></td>
				<td><?php echo $taglist[$xtpl['vmpool_tag']]['tag'] ?></td>
			</tr>
			<tr>
				<th>Imported VMs</th>
				<td><?php echo $xtpl['vm_tag'] ?></td>
				<td><?php echo $taglist[$xtpl['vm_tag']]['tag'] ?></td>
			</tr>
		</table>

		<br><br>
		<strong>Click 'import' to begin the import process</strong><br>
		or select 'clear' to clear the list of importable VMs<br><br>
		<input type="submit" name="import" value="Import selected VMs and pools">
		<input type="button" name="clear" value="Clear discovered VMs" onclick="location.href = '?module=redirect&page=depot&tab=xen_import&op=clearList'; return false;">
		</form>
		<br><br>
	<?php
		} 
		finishPortlet(); 
	?>

	<?php startPortlet("Debug") ?>
		<div style='text-align: left;'>
			<a href="#" onclick="el = document.getElementById('debugmsg'); el.style.display = (el.style.display != 'none' ? 'none' : '' )">Toggle Debug Messages</a>
			<pre id="debugmsg" style="display: none;">
				<?php //print_r($_SESSION); ?>
				---
				<?php //print_r($taglist); ?>
			</pre>
		</div>
	<?php finishPortlet(); ?>

</div>
<br clear='both'>

<?php 
	startPortlet();
	echo "XenVMImport by Tyler Montgomery <br>";

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
	echo 'Page generated in '.$total_time.' seconds.';


	finishPortlet();
?>

</div>
