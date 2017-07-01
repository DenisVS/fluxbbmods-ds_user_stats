<?php
//$Id$
/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_DEBUG', 1);
define('PUN_SHOW_QUERIES', 1);
ini_set('display_errors', 1);
 
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

$ds_stats_conf = unserialize($pun_config['o_ds_stats']);
 
// Load the admin_plugin_example.php language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_DS_User_Stats.php';
 
// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
 
//
// The rest is up to you!
//



// Add bots
if (isset($_POST['add_bots']))
{
  foreach ($_POST['newBots']  as $botNUM => $botName)  
  {
    $ds_stats_conf['bots'][$botName] = true;
  }
	$db->query('INSERT INTO '.$db->prefix.'config 
	(conf_name, conf_value) VALUES (\'o_ds_stats\', \''.serialize($ds_stats_conf).'\') 
	ON DUPLICATE KEY UPDATE conf_value=\''.serialize($ds_stats_conf).'\'') or error('Unable to update config', __FILE__, __LINE__, $db->error());

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';
 
// Regenerate the config cache
generate_config_cache();

	redirect('admin_loader.php?plugin=AP_DS_User_Stats.php','Settings Saved, Redirecting &hellip;');
	die();
}

// Delete bots
if (isset($_POST['delete_bots']))
{
  foreach ($_POST['existBots']  as $botNUM => $botName)  
  {
    unset ($ds_stats_conf['bots'][$botName]);
  }
  //echo '<pre>'; var_dump ($ds_stats_conf); echo '</pre>';
	$db->query('INSERT INTO '.$db->prefix.'config 
	(conf_name, conf_value) VALUES (\'o_ds_stats\', \''.serialize($ds_stats_conf).'\') 
	ON DUPLICATE KEY UPDATE conf_value=\''.serialize($ds_stats_conf).'\'') or error('Unable to update config', __FILE__, __LINE__, $db->error());
	
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';
 
// Regenerate the config cache
generate_config_cache();
	
	redirect('admin_loader.php?plugin=AP_DS_User_Stats.php','Settings Saved, Redirecting &hellip;');
	die();
}


// Save options
if (isset($_POST['save_options']))
{
	$ds_stats_conf['stats_enabled'] = (isset($_POST["modStatus"]) ? $_POST["modStatus"] : 0);
	$ds_stats_conf['ent_per_page'] = (isset($_POST["entPerPage"]) ? $_POST["entPerPage"] : 0);
	$ds_stats_conf['ent_in_database'] = (isset($_POST["maxEntries"]) ? $_POST["maxEntries"] : 0);
	$ds_stats_conf['highlight_users'] = (isset($_POST["highlightUser"]) ? $_POST["highlightUser"] : 0);
	$ds_stats_conf['highlight_bots'] = (isset($_POST["highlightBot"]) ? $_POST["highlightBot"] : 0);
	$ds_stats_conf['sort_order'] = (isset($_POST["sortOrder"][0]) ? $_POST["sortOrder"][0] : 'DESC');
	$ds_stats_conf['perm_guests'] = (isset($_POST["permGuests"]) ? $_POST["permGuests"] : 0);
	$ds_stats_conf['perm_users'] = (isset($_POST["permUsers"]) ? $_POST["permUsers"] : 0);
	$ds_stats_conf['perm_mods'] = (isset($_POST["permModerators"]) ? $_POST["permModerators"] : 0);
	$ds_stats_conf['otherBots'] = (isset($_POST["otherBots"]) ? $_POST["otherBots"] : 0);

if (isset($_POST["IP"]) && !empty($_POST["IP"]))
{
	$ips = $_POST["IP"];
	$ips =   str_replace  ("\r", "\n", trim ($ips));
	$ips =   str_replace  ("\n", ' ', trim ($ips));
	$ips = preg_replace("/\s+/", " ", $ips);
	$ips =   explode(' ', trim ($ips));
	foreach ($ips as $ip)	{$ds_stats_conf['IP'][$ip] = true;}
}
else
{
	unset ($ds_stats_conf['IP']);
}
	/*/
	$ips = (isset($_POST["IP"]) ? $_POST["IP"] : false);
	$ips =   str_replace  ("\r", "\n", trim ($ips));
	$ips =   str_replace  ("\n", ' ', trim ($ips));
	$ips = preg_replace("/\s+/", " ", $ips);
	$ips =   explode(' ', trim ($ips));
	unset ($ds_stats_conf['IP']);
	foreach ($ips as $ip)	{$ds_stats_conf['IP'][$ip] = true;}
	*/

if (isset ($ds_stats_conf["bots"]))
{
  foreach ($ds_stats_conf["bots"] as $botName => $botStatus)	{$ds_stats_conf["bots"][$botName] = (isset($_POST["botName"][$botName])) ? 1 : 0;}
} 
//  (isset ($ds_stats_conf["bots"]) ? foreach ($ds_stats_conf["bots"] as $botName => $botStatus)	{$ds_stats_conf["bots"][$botName] = (isset($_POST["botName"][$botName])) ? 1 : 0;}:0);
 //(isset($ds_stats_conf["bots"]) foreach ($ds_stats_conf["bots"] as $botName => $botStatus)	{$ds_stats_conf["bots"][$botName] = (isset($_POST["botName"][$botName])) ? 1 : 0;}  : 0);

	$result = $db->query('INSERT INTO '.$db->prefix.'config 
	(conf_name, conf_value) VALUES (\'o_ds_stats\', \''.preg_replace('~\R~u', "\n", trim(serialize($ds_stats_conf))).'\') 
	ON DUPLICATE KEY UPDATE conf_value=\''.preg_replace('~\R~u', "\n", trim(serialize($ds_stats_conf))).'\'') or error('Unable to update config', __FILE__, __LINE__, $db->error());

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';
 
// Regenerate the config cache
generate_config_cache();

	 echo '<pre>'; var_dump ($ds_stats_conf); echo '</pre>';
	redirect('admin_loader.php?plugin=AP_DS_User_Stats.php','Settings Saved, Redirecting &hellip;');
	die();
}

 

{
	// Display the admin navigation menu
	generate_admin_menu($plugin);
 
?>
	<div class="plugin blockform">
		<h2><span><?php echo $lang_admin_DS_User_Stats['Plugin title'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_admin_DS_User_Stats['Explanation 1'] ?></p>
				<p><?php echo $lang_admin_DS_User_Stats['Explanation 2'] ?></p>
			</div>
		</div>
<script src="js/jscolor.min.js"></script>
<?php 

$result = $db->query('SELECT * FROM '.$db->prefix.'config WHERE conf_name=\'o_ds_stats\'' );
$data = $db->fetch_assoc($result);
$ds_stats_conf = unserialize($data['conf_value']);
//echo '<pre>'; var_dump ($ds_stats_conf); echo '</pre>';
//echo '<pre>'; var_dump ($pun_config); echo '</pre>';

// Get robots from log
$result = $db->query('SELECT username FROM '.$db->prefix.'userstats WHERE browser="Robot"  ORDER BY username') or error('Unable to fetch userstats for forum', __FILE__, __LINE__, $db->error());
while ($cur_entry = $db->fetch_assoc($result))
{
	$robotsList[$cur_entry['username']] = 1;
}

if (isset($ds_stats_conf['IP']))	{foreach ($ds_stats_conf['IP'] as $ip => $key)	{$ipList =  $ipList.$ip. PHP_EOL;}}

?>
 
		<h2 class="block2"><span><?php echo $lang_admin_DS_User_Stats['Options'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;foo=bar">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Stats['Mod status'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Enable mod'] ?></th>
									<td>
										<input type="checkbox" name="modStatus" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['stats_enabled'])) ? (($ds_stats_conf['stats_enabled'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> Плагин включен
									</td>
								</tr>
							</table>

						</div>

					</fieldset>

					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Stats['Legend text'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Max entries'] ?></th>
									<td>
										<input type="text" name="maxEntries" id="ourFormId1" title="Our title" value="<?php echo((isset($ds_stats_conf['ent_in_database'])) ? ($ds_stats_conf['ent_in_database'] ) : '150'); ?>"/> Maximum number of log entries to be stored in the database.
									</td>
								</tr>

								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Entries per page'] ?></th>
									<td>
										<input type="text" name="entPerPage" id="ourFormId1" title="Our title" value="<?php echo((isset($ds_stats_conf['ent_per_page'])) ? ($ds_stats_conf['ent_per_page'] ) : '50'); ?>"/> Entries per page
									</td>
								</tr>

								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Highlight user'] ?></th>
									<td>
										<input name="highlightUser" class="jscolor" value="<?php echo((isset($ds_stats_conf['highlight_users'])) ? ($ds_stats_conf['highlight_users'] ) : 'FFFF00'); ?>"> Highlight color for registered users.
									</td>
								</tr>

								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Highlight bot'] ?></th>
									<td>
										<input name="highlightBot" class="jscolor" value="<?php echo((isset($ds_stats_conf['highlight_bots'])) ? ($ds_stats_conf['highlight_bots'] ) : '99EECC'); ?>"> Highlight color for bots.
									</td>
								</tr>

								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Sort order'] ?></th>
									<td>
										
										<select name="sortOrder[]">
    <option <?php echo((isset($ds_stats_conf['sort_order'])) ? (($ds_stats_conf['sort_order'] == 'DESC') ? false : 'selected' ) : false); ?> value="ASC"><?php echo $lang_admin_DS_User_Stats['Ascending'] ?></option>
    <option <?php echo((isset($ds_stats_conf['sort_order'])) ? (($ds_stats_conf['sort_order'] == 'DESC') ? 'selected' : false ) : 'selected'); ?> value="DESC"><?php echo $lang_admin_DS_User_Stats['Descending'] ?></option>
   </select>
									</td>
								</tr>
								
								
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Guests'] ?></th>
									<td>
										<input type="checkbox" name="permGuests" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['perm_guests'])) ? (($ds_stats_conf['perm_guests'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> Плагин включен
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Users'] ?></th>
									<td>
										<input type="checkbox" name="permUsers" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['perm_users'])) ? (($ds_stats_conf['perm_users'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> Плагин включен
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Moderators'] ?></th>
									<td>
										<input type="checkbox" name="permModerators" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['perm_mods'])) ? (($ds_stats_conf['perm_mods'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> Плагин включен
									</td>
								</tr>
								
								
								
							</table>

						</div>

					</fieldset>


					<fieldset>


						<legend><?php echo $lang_admin_DS_User_Stats['Logging'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">

<?php
if (isset($ds_stats_conf['bots']))	{
foreach ($ds_stats_conf['bots'] as $botName => $botStatus) {
  //echo $botName . $botStatus;
?>


								<tr>
									<th scope="row"><?php echo $botName;?></th>
									<td>
										<input type="checkbox" name="botName[<?php echo $botName; ?>]" id="ourFormId1" title="Our title" value="1" <?php echo((isset($botStatus)) ? (($botStatus == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> <?php echo $lang_admin_DS_User_Stats['Log from'] .' '.$botName?>.
									</td>
								</tr>

<?php
}	}
?>

								<tr>
									<th scope="row">Others bots</th>
									<td>
										<input type="checkbox" name="otherBots" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['otherBots'])) ? (($ds_stats_conf['otherBots'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> <?php echo $lang_admin_DS_User_Stats['Log from'] .' '; ?> others.
									</td>
								</tr>
							</table>
						</div>
						</fieldset>

					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Stats['IP list'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Excluded IP list'];?></th>
									<td>
										<textarea rows="5" cols="12" name="IP"><?php echo $ipList; ?></textarea>
									</td>
								</tr>


							</table>
  					</div>
						</fieldset>

					<th scope="row"><div><input type="submit" name="save_options" value="<?php echo $lang_admin_DS_User_Stats['Save settings'] ?>" tabindex="2" /></div></th>

			</form>

<?php	//Check if bot from log in our list, we unset it
foreach ($robotsList as $botName => $botStatus) {	
	if  (isset($ds_stats_conf['bots'][$botName]))
	{
		unset ($robotsList[$botName]);
	}
}
?>
<hr>
<?php if (sizeof($robotsList) >0 ) { ?>
			<form id="example" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;action=addBots">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Stats['Add bots'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Bots from log'] ?><div><input type="submit" name="add_bots" value="<?php echo $lang_admin_DS_User_Stats['Add'] ?>" tabindex="2" /></div></th>
									<td>
										<select multiple name="newBots[]">
<?php
foreach ($robotsList as $botName => $botStatus) {	?>
                      <option  value="<?php echo $botName; ?>"><?php echo $botName; ?></option>
<?php	}	?>
                    </select>
										<span><?php echo $lang_admin_DS_User_Stats['Bots log list'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
			</form>
			
<hr>


<?php 
}
if (sizeof($ds_stats_conf['bots']) >0 ) { ?>
			<form id="example" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;action=deleteBots">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Stats['Delete bots'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Stats['Bots from config'] ?><div><input type="submit" name="delete_bots" value="<?php echo $lang_admin_DS_User_Stats['Delete'] ?>" tabindex="2" /></div></th>
									<td>
										<select multiple name="existBots[]">
<?php
foreach ($ds_stats_conf['bots'] as $botName => $botStatus) {
?>
                      <option  value="<?php echo $botName; ?>"><?php echo $botName; ?></option>
<?php } ?>
                    </select>
										<span><?php echo $lang_admin_DS_User_Stats['Current bot list'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
			</form>
<hr>
<?php } ?>
		</div>
	</div>
<?php
 
}
 
// Note that the script just ends here. The footer will be included by admin_loader.php

