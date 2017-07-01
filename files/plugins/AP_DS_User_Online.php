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

// Load the admin_plugin_example.php language file
if (file_exists(PUN_ROOT.'lang/'.$admin_language.'/admin_DS_User_Online.php'))
        require PUN_ROOT.'lang/'.$admin_language.'/admin_DS_User_Online.php';
else
        require PUN_ROOT.'lang/English/admin_DS_User_Online.php';


// Load the admin_plugin_example.php language file
//require PUN_ROOT.'lang/'.$admin_language.'/admin_DS_User_Online.php';
 
// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
$ds_stats_conf = unserialize($pun_config['o_ds_stats']); 
echo '<pre>'; var_dump ($ds_stats_conf); echo '</pre><br /><br /><br /><br />';




//
// The rest is up to you!
//





// Save options
if (isset($_POST['save_options']))
{
	 if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';
 
// Regenerate the config cache
generate_config_cache();
//generate_config_cache();
	 
	 
	// echo '<pre>'; var_dump ($_POST); echo '</pre>';
	 
	 
	 
/*
    ["topicColors"]=>
	["pastOnlineEnable"]=>
	["pastOnlineTime"]=>
    ["mostOnline"]=>
    ["groupColor"]=>
	*/ 
	 
	$ds_stats_conf['online_enabled'] = (isset($_POST["modStatus"]) ? $_POST["modStatus"] : 0);
	$ds_stats_conf['topic_colors'] = (isset($_POST["topicColors"]) ? $_POST["topicColors"] : 0);
	$ds_stats_conf['past_online_enable'] = (isset($_POST["pastOnlineEnable"]) ? $_POST["pastOnlineEnable"] : 0);
	$ds_stats_conf['past_online_time'] = (isset($_POST["pastOnlineTime"]) ? $_POST["pastOnlineTime"] : 0);
	$ds_stats_conf['most_online'] = (isset($_POST["mostOnline"]) ? $_POST["mostOnline"] : 0);

if (isset ($_POST["showLegend"]))
{
  foreach ($_POST["showLegend"] as $g_id => $legendStatus)	
  {
	  $ds_stats_conf['show_legend'][$g_id] = $legendStatus;
	}
} 
	// echo '<pre>'; var_dump ($ds_stats_conf); echo '</pre>';

	$ds_stats_conf['show_legend'] = (isset($_POST["showLegend"]) ? $_POST["showLegend"] : 0);

	foreach ($_POST["groupColor"] as $g_id => $val) {
		$ds_stats_conf['group_color'][$g_id] = $val;
		if (!$val || $val == 'FFFFFF') {unset ($ds_stats_conf['group_color'][$g_id]);}
	}


	$result = $db->query('INSERT INTO '.$db->prefix.'config 
	(conf_name, conf_value) VALUES (\'o_ds_stats\', \''.preg_replace('~\R~u', "\n", trim(serialize($ds_stats_conf))).'\') 
	ON DUPLICATE KEY UPDATE conf_value=\''.preg_replace('~\R~u', "\n", trim(serialize($ds_stats_conf))).'\'') or error('Unable to update config', __FILE__, __LINE__, $db->error());

	generate_config_cache();

	 echo '<pre>'; var_dump ($ds_stats_conf); echo '</pre>';
	redirect('admin_loader.php?plugin=AP_DS_User_Online.php','Settings Saved, Redirecting &hellip;');
//*/
	die(); 
}

 

{
	// Display the admin navigation menu
	generate_admin_menu($plugin);



 
?>
	<div class="plugin blockform">
		<h2><span><?php echo $lang_admin_DS_User_Online['Plugin title'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_admin_DS_User_Online['Explanation 1'] ?></p>
			</div>
		</div>
<script src="js/jscolor.min.js"></script>
<?php 


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
 
		<h2 class="block2"><span><?php echo $lang_admin_DS_User_Online['Options'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;foo=bar">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Online['Mod status'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Online['Enable mod'] ?></th>
									<td>
										<input type="checkbox" name="modStatus" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['online_enabled'])) ? (($ds_stats_conf['online_enabled'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> <?php echo $lang_admin_DS_User_Online['Enable mod checkbox'];?>
									</td>
								</tr>
							</table>

						</div>

					</fieldset>

					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Online['Settings'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">


								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Online['Color in topic view'] ?></th>
									<td>
										<input type="checkbox" name="topicColors" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['topic_colors'])) ? (($ds_stats_conf['topic_colors'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> <?php echo $lang_admin_DS_User_Online['Group color in topic'];?>
									</td>
								</tr>




								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Online['Past online'] ?></th>
									<td>
										<input type="checkbox" name="pastOnlineEnable" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['past_online_enable'])) ? (($ds_stats_conf['past_online_enable'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/> <?php echo $lang_admin_DS_User_Online['Show online past xx'];?>
									</td>
								</tr>


								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Online['Past online period'] ?></th>
									<td>
										<input type="text" name="pastOnlineTime" id="ourFormId1" title="Our title" value="<?php echo((isset($ds_stats_conf['past_online_time'])) ? ($ds_stats_conf['past_online_time'] ) : '60m'); ?>"/>  <?php echo $lang_admin_DS_User_Online['Examples 15m'];?>
									</td>
								</tr>


								<tr>
									<th scope="row"><?php echo $lang_admin_DS_User_Online['Show most online'] ?></th>
									<td>
										<input type="checkbox" name="mostOnline" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['most_online'])) ? (($ds_stats_conf['most_online'] == 1) ? 'checked="checked"' : false ) : 'checked="checked"'); ?>/>   <?php echo $lang_admin_DS_User_Online['Most online at time'];?>
									</td>
								</tr>




<?php


$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups' );
while ($row = $result->fetch_assoc()) {
?>
								<tr>
									<th scope="row"><?php echo $row["g_title"];?></th>
									<td>
										<input maxlength="6" size="6" class="jscolor" type="text" name="groupColor[<?php echo $row["g_id"];?>]" id="ourFormId1" title="Our title" value="<?php echo((isset($ds_stats_conf['group_color'][$row["g_id"]])) ? ($ds_stats_conf['group_color'][$row["g_id"]]) : ''); ?>"/>   <?php echo $lang_admin_DS_User_Online['Group color for'];?> <?php echo $row["g_title"];?>. <?php echo $lang_admin_DS_User_Online['Leave blank'];?>
									</td>
									<td>
										<input type="checkbox" name="showLegend[<?php echo $row["g_id"];?>]" id="ourFormId1" title="Our title" value="1" <?php echo((isset($ds_stats_conf['show_legend'][$row["g_id"]])) ? (($ds_stats_conf['show_legend'][$row["g_id"]] == 1) ? 'checked="checked"' : false ) : false); ?>/>  Show Legend
										
									</td>
								</tr>

<?php
}
$result->free();	// free result set


?>
	
	

								
							</table>

						</div>

					</fieldset>



					<fieldset>
						<legend><?php echo $lang_admin_DS_User_Online['IP list'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">


							</table>
  					</div>
					</fieldset>

					<th scope="row"><div><input type="submit" name="save_options" value="<?php echo $lang_admin_DS_User_Online['Save settings'] ?>" tabindex="2" /></div></th>

			</form>


<hr>


<hr>



		</div>
	</div>
<?php
 
}
 
// Note that the script just ends here. The footer will be included by admin_loader.php

