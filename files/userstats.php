<?php
//$Id:$
//define('PUN_DEBUG', 1);
define('PUN_SHOW_QUERIES', 1);
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set("display_startup_errors","1");
define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
$ds_stats_conf = unserialize($pun_config['o_ds_stats']);
(isset($ds_stats_conf['ent_per_page']) ? $ds_stats_conf['ent_per_page']:$ds_stats_conf['ent_per_page'] = 50);
require PUN_ROOT.'include/ds_stats/english_country.php';	// Load the language country vars
$multibyte = (isset($lang_common['lang_multibyte']) && $lang_common['lang_multibyte']) ? true : false;	// Detect two byte character sets
$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'User stats');
require PUN_ROOT.'header.php';
if(($pun_user['g_id'] == PUN_GUEST && $ds_stats_conf['perm_guests'] == "0")||($pun_user['g_id'] > PUN_MOD && $ds_stats_conf['perm_users'] == "0")||($pun_user['g_id'] == PUN_MOD && $ds_stats_conf['perm_mods'] == "0"))
	message($lang_common['No permission']);

// Pull userstats
$result = $db->query('SELECT * FROM '.$db->prefix.'userstats ORDER BY date '.$ds_stats_conf['sort_order']) or error('Unable to fetch userstats for forum', __FILE__, __LINE__, $db->error());
$num_entries_count = $db->num_rows($result);	// Count entries

// Determine the post offset (based on $_GET['p'])
$num_pages = ceil($num_entries_count / $ds_stats_conf['ent_per_page']);
$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];

//============= START ALT =============
// All this just to determine the correct alternate color (red or blue) to start off with on each page
// Adjust the date
$ttoday = time();
$tdiff = ($pun_user['timezone'] - $pun_config['o_server_timezone']) * 3600;

if ($p > 1)
{
	$h1 = ($p-1)*$ds_stats_conf['ent_per_page'];
	$h2 = $h1 + 1;
	// Pull userstats
	$result2 = $db->query('SELECT * FROM '.$db->prefix.'userstats ORDER BY date '.$ds_stats_conf['sort_order'].' LIMIT '.$h1.','.$h2,'') or error('Unable to fetch userstats for forum', __FILE__, __LINE__, $db->error());
	$stat_data = $db->fetch_assoc($result2);
	$stat_nr = $stat_data['date']+$tdiff;
	$stat_nr = date("Y-m-d",$stat_nr);

	// Pull userstats
	$result2 = $db->query('SELECT DISTINCT FROM_UNIXTIME(date+'.$tdiff.', \'%Y-%m-%d\') as timestamp FROM '.$db->prefix.'userstats 
		WHERE FROM_UNIXTIME(date+'.$tdiff.', \'%Y-%m-%d\') >= \''.$stat_nr.'\' ORDER BY timestamp '.$ds_stats_conf['sort_order'].'
		') or error('Unable to fetch alternate days list for forum', __FILE__, __LINE__, $db->error());

	// Count entries
	$num_alternate = $db->num_rows($result2);
	$koos = ($num_alternate % 2) ? TRUE : FALSE; // Odd = TRUE ; Even = FALSE
}
//============= END ALT =============

// Generate paging links
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'userstats.php?disp=1');
if ($num_entries_count < $ds_stats_conf['ent_in_database'])
	$num_displayed = " (".$num_entries_count." displayed)";
else
	$num_displayed = "";
?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<div class="clearer"></div>
	</div>
</div>
	<div class="blocktable">
		<div class="box">
		<div class="inbox">
	<h2><span>User stats - last <?php echo $ds_stats_conf['ent_in_database'] ?> visitors<?php echo $num_displayed ?></span></h2>
<?php
echo "
<table cellspacing='0'>
		<thead>
			<tr>
				<th class='tc3' scope='col'>Date</th><th class='tcr' scope='col'>Username</th><th class='tc3' scope='col'>Userip</th><th class='tc3' scope='col'>Browser</th><th class='tc3' scope='col'>Operating system</th><th class='tc3' scope='col'>Country</th>
			</tr>
";
?>
		</thead>
		<tbody>
<?php 
$day_alt = "0";
if (isset($koos))
	$day_alt = ($koos) ? "0" : "1";

// Output file info
if($db->num_rows($result))
{
	$entry_count = 0;
	while ($cur_entry = $db->fetch_assoc($result))
	{
		++$entry_count;
		if (($entry_count >= ((($p-1)*$ds_stats_conf['ent_per_page']) + 1)) && ($entry_count < (($p*$ds_stats_conf['ent_per_page']) + 1)))
		{
			$merkdit = 1;
			if (substr($cur_entry['username'], 0, 5) == 'Guest') $merkdit = 0;
			if ($cur_entry['browser'] == 'Robot') $merkdit = 2;
			$uri = "http://".$_SERVER['HTTP_HOST'].$cur_entry['uri'];
			$browser = (!empty($cur_entry['browser'])) ? $cur_entry['browser'] : "&nbsp;";
			$opsys = (!empty($cur_entry['opsys'])) ? $cur_entry['opsys'] : "&nbsp;";
			$country = ( $cur_entry['country'] ) ;
			if (strlen(trim($country)) == 1) $country = 'Earth';	//unset($country);
			if($pun_user['g_id'] == PUN_GUEST || $pun_user['g_id'] > PUN_MOD)
			{	
				$browser = "No permission";
				$cur_entry['userip'] = "No permission";
				$opsys = "No permission";
			}
			$explodedCurEnt = explode('/',$cur_entry['uri']);
			if ($cur_entry['uri'] == "unknown")
				$uri_title = "unknown";
			else if ($cur_entry['subject'] != "")
				$uri_title = $cur_entry['subject'];
			else if (end($explodedCurEnt) == "")
				$uri_title = "index.php";
			else
				$uri_title = end($explodedCurEnt);

			if ($prev_date != format_time($cur_entry['date'], true))
				$day_alt = (!($day_alt));
			
			$day_color = ($day_alt == "0") ? "#004DFC" : "#FF2D03";
			$prev_date = format_time($cur_entry['date'], true);

			if($pun_user['g_id'] == PUN_GUEST || $pun_user['g_id'] > PUN_MOD)
				{$browser = "No permission";
				$cur_entry['userip'] = "No permission";
				$opsys = "No permission";
			}
			$uri_title = htmlspecialchars($uri_title);
?>
		<tr>
			<td class="tc3"<?php echo " style='BORDER-LEFT-WIDTH:4px;BORDER-LEFT-COLOR: ".$day_color.";"; if ($merkdit == 1) {echo "BACKGROUND-COLOR: #".$ds_stats_conf['highlight_users'];} else if ($merkdit == 2)	{echo "BACKGROUND-COLOR: #".$ds_stats_conf['highlight_bots'];}  echo "'"; ?>><?php if ($cur_entry['uri'] != 'unknown') echo '<a href="'.$uri.'">'; ?><img src="img/world_link.png" alt="<?php echo $uri_title ?>" title="<?php echo $uri_title ?>" /><?php if ($cur_entry['uri'] != 'unknown') echo '</a>'; ?>&nbsp;<?php echo format_time($cur_entry['date']) ?></td>
			<td class="tcr"<?php if ($merkdit == 1)	{echo " style='background-color: #".$ds_stats_conf['highlight_users']."'";} else if ($merkdit == 2)	{echo " style='background-color: #".$ds_stats_conf['highlight_bots']."'";} ?>><?php echo $cur_entry['username'] ?></td>
			<td class="tc3"<?php if ($merkdit == 1) {echo " style='background-color: #".$ds_stats_conf['highlight_users']."'";} else if ($merkdit == 2)	{echo " style='background-color: #".$ds_stats_conf['highlight_bots']."'";}  ?>><?php echo $cur_entry['userip'] ?></td>
			<td class="tc3"<?php if ($merkdit == 1) {echo " style='background-color: #".$ds_stats_conf['highlight_users']."'";} else if ($merkdit == 2)	{echo " style='background-color: #".$ds_stats_conf['highlight_bots']."'";}  ?>><?php echo $browser ?></td>
			<td class="tc3"<?php if ($merkdit == 1) {echo " style='background-color: #".$ds_stats_conf['highlight_users']."'";} else if ($merkdit == 2)	{echo " style='background-color: #".$ds_stats_conf['highlight_bots']."'";}  ?>><?php echo $opsys ?></td>
			<td class="tc3"<?php if ($merkdit == 1) {echo " style='background-color: #".$ds_stats_conf['highlight_users']."'";} else if ($merkdit == 2)	{echo " style='background-color: #".$ds_stats_conf['highlight_bots']."'";}  ?>><?php echo $country ?></td>
		</tr>
<?php
		}
	}
}
?>
		</tbody>
		</table>
	</div>
	</div>
	</div>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<div class="clearer"></div>
	</div>
</div>
<?php
echo '<pre>'; var_dump( $ds_stats_conf); echo '</pre>';
require PUN_ROOT.'footer.php';
?>
