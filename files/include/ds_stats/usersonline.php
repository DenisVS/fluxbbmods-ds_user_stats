<?php
//$Id$
// Load the ds_stats.php language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/ds_stats.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/ds_stats.php';
else
	require PUN_ROOT.'lang/English/ds_stats.php';

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';
?>
<div id="brdstats" class="block">
	<h2><span><?php echo $lang_index['Board info'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p class="conr"><?php echo $lang_usersonline['Newest user'] . ': '; echo ($pun_user['g_view_users'] == '1') ? '<a href="profile.php?id='.$stats['last_user']['id'].'">'.pun_htmlspecialchars($stats['last_user']['username']).'</a>' : pun_htmlspecialchars($stats['last_user']['username']); ?></p>
			<dl id="statslist">
				<dt style="DISPLAY: inline; HEIGHT: 0"><?php echo '<strong>'.$lang_usersonline['Board stats'].':</strong>'; ?></dt>
				<dd style="DISPLAY: inline; HEIGHT: 0"><?php echo ''.$stats['total_users'].'&nbsp;'.$lang_usersonline['Users'].'&nbsp;|'; ?></dd>
				<dd style="DISPLAY: inline; HEIGHT: 0"><?php echo ''.$stats['total_topics'].'&nbsp;'.$lang_usersonline['Topics'].'&nbsp;|'; ?></dd>
				<dd style="DISPLAY: inline; HEIGHT: 0"><?php echo ''.$stats['total_posts'].'&nbsp;'.$lang_usersonline['Posts']; ?></dd>
			</dl><hr size="1" style="margin:0;">
<?php
if ($pun_config['o_users_online'] == '1')
{
	// Fetch users online info and generate strings for output
	$num_guests = $num_bots = 0;
	$users = array();
	$result_online = $db->query('SELECT u.user_id, u.ident, r.group_id FROM '.$db->prefix.'online as u LEFT JOIN '.$db->prefix.'users as r ON u.user_id = r.id WHERE u.idle=0 ORDER BY u.ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

	while ($current_user_online = $db->fetch_assoc($result_online))
	{
    $all_users_online[] = $current_user_online;
		if ($current_user_online['user_id'] > 1)
		{
			if ($pun_user['g_view_users'] == '1')
				$users[] = "\n\t\t\t\t".'<dd><a '.((isset($ds_stats_conf['group_color'][$current_user_online['group_id']]) && $ds_stats_conf['topic_colors'] == '1') ? 'style="COLOR: #'.$ds_stats_conf['group_color'][$current_user_online['group_id']].'" ' : '').'href="profile.php?id='.$current_user_online['user_id'].'">'.pun_htmlspecialchars($current_user_online['ident']).'</a>';
			else
				$users[] = "\n\t\t\t\t".'<dd><span '.((isset($ds_stats_conf['group_color'][$current_user_online['group_id']]) && $ds_stats_conf['topic_colors'] == '1') ? 'style="COLOR: #'.$ds_stats_conf['group_color'][$current_user_online['group_id']].'" ' : '').'">'.pun_htmlspecialchars($current_user_online['ident']).'</span>';
		}
		else
		{
			if (strpos($current_user_online['ident'], '[Bot]') !== false) 	++$num_bots; else ++$num_guests;
		}
	}

	//$num_guests = $num_guests - $num_bots;
	$num_users = count($users);
	$bots_dsp = ($num_bots == "1") ? $lang_usersonline['Bot single'] : $lang_usersonline['Bots plural'];
	$guests_dsp = ($num_guests == "1") ? $lang_usersonline['Guest single'] : $lang_usersonline['Guests plural'];
	$users_dsp = ($num_users == "1") ? $lang_usersonline['Registered user single'] : $lang_usersonline['Registered users plural'];
	if(($pun_user['g_id'] == PUN_GUEST && $ds_stats_conf['perm_guests'] == "0")||($pun_user['g_id'] > PUN_MOD && $ds_stats_conf['perm_users'] == "0")||($pun_user['g_id'] == PUN_MOD && $ds_stats_conf['perm_mods'] == "0"))
	{
		$online_label = $lang_usersonline['Online'].':';
	}
	else
	{
		
		$online_label = '<a href="userstats.php">'.$lang_usersonline['Online'].':</a>';
	}

	if ($num_users > 0)
	{
		echo "\t\t\t".'<dl id="onlinelist" class= "clearb">'."\n\t\t\t\t".'<dt><strong>'.$online_label.'</strong>&nbsp;'.($num_guests+$num_users+$num_bots).'&nbsp;['.$num_bots.'&nbsp;'.$bots_dsp.', '.$num_guests.'&nbsp;'.$guests_dsp.', '.$num_users.'&nbsp;'.$users_dsp.']</dt>'."\t\t\t\t".implode(',</dd> ', $users).'</dd>'."\n\t\t\t".'</dl>'."\n";
	}
	else
	{
		echo "\t\t\t".'<dl id="onlinelist" class= "clearb">'."\n\t\t\t\t".'<dt><strong>'.$online_label.'</strong>&nbsp;'.($num_guests+$num_users+$num_bots).'&nbsp;['.$num_bots.'&nbsp;'.$bots_dsp.', '.$num_guests.'&nbsp;'.$guests_dsp.', '.$num_users.'&nbsp;'.$users_dsp.']</dt>'."\n\t\t\t".'</dl>'."\n";
	}

//########### MOST ONLINE START
	$rnum_guests = $rnum_users = $rnum_bots = 0;
	foreach ($all_users_online as $user_id => $value)
	{
		if ($all_users_online[$user_id] > 1)
			++$rnum_users;
		else
		{
 			if (strpos($all_users_online['ident'], '[Bot]') !== false) 	++$rnum_bots; else ++$rnum_guests;
		}
	}
	$rnum_total = $rnum_users + $rnum_guests;
	$usersonline_ob_en = "1";
	if ($rnum_total > $ds_stats_conf['max_users'] && $usersonline_ob_en)
	{
		$ds_stats_conf['max_date'] = time();
		$ds_stats_conf['max_users'] = $rnum_total;
		$result = $db->query('INSERT INTO '.$db->prefix.'config 
		(conf_name, conf_value) VALUES (\'o_ds_stats\', \''.preg_replace('~\R~u', "\n", trim(serialize($ds_stats_conf))).'\') 
		ON DUPLICATE KEY UPDATE conf_value=\''.preg_replace('~\R~u', "\n", trim(serialize($ds_stats_conf))).'\'') or error('Unable to update config', __FILE__, __LINE__, $db->error());

		generate_config_cache();	// Regenerate the config cache
	}
    // Adjust the date
    $diff = ($pun_user['timezone'] - $pun_config['o_default_timezone']) * 3600 ;
	$ds_stats_conf['max_date'] += $diff;

	if ($ds_stats_conf['most_online'])
	{
	?>
			<dl id="mostonline" class= "clearb">
				<dt style="DISPLAY: inline; HEIGHT: 0"><?php echo $lang_usersonline['Most online'] ?>:&nbsp;<?php echo $ds_stats_conf['max_users'] ?> (<?php echo date("D M j, Y g:i a",$ds_stats_conf['max_date']) ?>)</dt>				
			</dl>
	<?php
	}
	//########### MOST ONLINE END

	//########### PAST ONLINE START
	if ($ds_stats_conf['past_online_enable'] && isset($ds_stats_conf['past_online_show'][$pun_user['group_id']]) && $ds_stats_conf['past_online_show'][$pun_user['group_id']] == 1)
	{
		$num_guests_ot = 0;
		$users_ot = array();
		$period = substr($ds_stats_conf['past_online_time'], 0, -1);
		$period_unit = substr($ds_stats_conf['past_online_time'], -1);
		$period_isint = preg_match("/^\d+$/", $period);

		if (!$period_isint || (($period_unit != "m") && ($period_unit != "h")))
		{
			// Use default (15m)
			$period = 15;
			$period_unit = "m";
			$period_check = time() - $period*60;
		}

		if ($period_unit == "h")
		{
			$period_check = time() - $period*60*60;
			$period_string = ($period == "1") ? $lang_usersonline['Online past hour single'] : str_replace('<NUM>', $period, $lang_usersonline['Online past hours plural']);
		}
		else
		{
			$period_check = time() - $period*60;
			$period_string = ($period == "1") ? $lang_usersonline['Online past minute single'] : str_replace('<NUM>', $period, $lang_usersonline['Online past minutes plural']);
		}

		// Fetch minimum date in userstats table
		$result = $db->query('SELECT MIN(date) AS mindate FROM '.$db->prefix.'userstats') or error('Unable to fetch minimum date from userstats table', __FILE__, __LINE__, $db->error());
		$period_min = $db->fetch_assoc($result);
		$period_min = $period_min['mindate'];
		$period_range = time() - $period_min;
		if ($period_check < $period_min)
		{
			// set up new period - using min value as base
			if ($period_unit == "h")
			{
			
				if (((time()-$period_min)/60) < 60)
				{
					// must use minutes instead of hours
					$period = ceil((time()-$period_min)/60);
					$period_unit = "m";
					$period_check = time() - $period*60;
					$period_string = ($period == "1") ? $lang_usersonline['Online past minute single'] : str_replace('<NUM>', $period, $lang_usersonline['Online past minutes plural']);
				}
				
				else
				{
					// can use hours
					$period = ceil((time()-$period_min)/60/60);
					$period_unit = "h";
					$period_check = time() - $period*60*60;
					$period_string = ($period == "1") ? $lang_usersonline['Online past hour single'] : str_replace('<NUM>', $period, $lang_usersonline['Online past hours plural']);
				}
			}
			else
			{
				// use minutes
				$period = ceil((time()-$period_min)/60);
				$period_unit = "m";
				$period_check = time() - $period*60;
				$period_string = ($period == "1") ? $lang_usersonline['Online past minute single'] : str_replace('<NUM>', $period, $lang_usersonline['Online past minutes plural']);
			}
		}
		$result2 = $db->query('SELECT DISTINCT u.username, u.userid, u.userip, r.group_id FROM '.$db->prefix.'userstats AS u LEFT JOIN '.$db->prefix.'users as r ON u.userid = r.id WHERE u.date >='.$period_check.' AND u.browser <> \'Robot\' ORDER BY u.userip') or error('Unable to fetch user stats', __FILE__, __LINE__, $db->error());
		$jj_userip_pre = $jj_userid_pre = "nantsoke"; // Just some random text for initialization
		$jj_userids = array(); // Initialize array
		$count_guest = $count_user = $deleteby = $count_row = $countj = 0;
		$first_occur = 1;
		$num_entries_count = $db->num_rows($result2);	// Count entries

		while ($pun_user_online = $db->fetch_assoc($result2))
		{
			++$count_row;
			$jj_userid = $pun_user_online['userid'];
			$jj_userip = $pun_user_online['userip'];
			if ($jj_userip == $jj_userip_pre)
			{
				// For the first occurrence
				if ($first_occur)
				{
					if ($jj_userid_pre == 1)
						++$count_guest;
					else
						++$count_user;
						$first_occur = 0;
				}
					if ($jj_userid == 1)
					++$count_guest;
				else
					++$count_user;

				// Is last row's userip equals to second last row's userip?
				if ($count_row == $num_entries_count)
				{
					if ($count_user == 0)
						$deleteby = $count_guest-1;
					else
						$deleteby = $count_guest;

					$num_guests_ot = $num_guests_ot - $deleteby;
					list($count_guest, $count_user, $deleteby) = 0;		// Clear counters
				}
			}
			else
			{
				if ($count_user > 0 || $count_guest > 0)
				{
					$first_occur = 1;
					if ($count_user == 0)
						$deleteby = $count_guest-1;
					else
						$deleteby = $count_guest;

					$num_guests_ot = $num_guests_ot - $deleteby;
					$count_guest = $count_user = $deleteby = 0;	// Clear counters
				}
			}
			if ($jj_userid == "1")
			{
				++$num_guests_ot;
			}
			else
			{
				if (!in_array($jj_userid, $jj_userids))
				{
					$jj_userids[] = $jj_userid;
					$countj = $countj + 1;
					$users_ot[$countj]["username"] = $pun_user_online['username'];
					$users_ot[$countj]["link"] = "\n\t\t\t\t".'<dd style="DISPLAY: inline; HEIGHT: 0"><a '.((isset($ds_stats_conf['group_color'][$pun_user_online['group_id']]) && $ds_stats_conf['topic_colors'] == '1') ? 'style="COLOR: #'.$ds_stats_conf['group_color'][$pun_user_online['group_id']].'" ' : '').'href="profile.php?id='.$pun_user_online['userid'].'">'.pun_htmlspecialchars($pun_user_online['username']).'</a>';
				}
			}
			$jj_userid_pre = $jj_userid;
			$jj_userip_pre = $jj_userip;
		}
		$num_users_ot = count($users_ot);

	//########### PAST ONLINE END

		echo '			<div class="clearer"></div>';
		if ($num_users_ot > 0)
		{
			// **** Sort the active user list alphabetically start
			$sortthis = 'username';
			$orde = "0";

			// sort by ?
			foreach ($users_ot as $pos => $val)
			{
				$tmp_array[$pos] = $val[$sortthis];
					if ($orde == "1")
					{
						arsort($tmp_array);
					}
					
					elseif ($orde == "0")
					{
						natcasesort($tmp_array);
					}
			}
			// **** Sort the active user list alphabetically end
			$guests_ot_dsp = ($num_guests_ot == "1") ? $lang_usersonline['Guest single'] : $lang_usersonline['Guests plural'];
			$users_ot_dsp = ($num_users_ot == "1") ? $lang_usersonline['Registered user single'] : $lang_usersonline['Registered users plural'];
			echo "\t\t\t".'<dl id="pastonline" class= "clearb">'."\n\t\t\t\t".'<dt style="DISPLAY: inline; HEIGHT: 0">'.$period_string.':&nbsp;'.($num_guests_ot+$num_users_ot).'&nbsp;['.$num_guests_ot.'&nbsp;'.$guests_ot_dsp.', '.$num_users_ot.'&nbsp;'.$users_ot_dsp.']</dt>';
			$user_count = 0;
			// display however you want
			foreach ($tmp_array as $pos => $val)
			{
				++$user_count;

				if ($user_count < $num_users_ot)
					echo(($pun_user['g_view_users'] == '1') ? ($users_ot[$pos]['link'].', </dd>') : $users_ot[$pos]['username'].', </dd>');
				else
					echo(($pun_user['g_view_users'] == '1') ? ($users_ot[$pos]['link'].'</dd>') : ' '.$users_ot[$pos]['username'].'</dd>');

			}
			echo "\n\t\t\t".'</dl>'."\n";
		}
		else
		{
			$guests_ot_dsp = ($num_guests_ot == "1") ? $lang_usersonline['Guest single'] : $lang_usersonline['Guests plural'];
			$users_ot_dsp = ($num_users_ot == "1") ? $lang_usersonline['Registered user single'] : $lang_usersonline['Registered users plural'];
			echo "\t\t\t".'<dl id="pastonline" class= "clearb">'."\n\t\t\t\t".'<dt style="DISPLAY: inline; HEIGHT: 0"><strong>'.$period_string.':&nbsp;['.$num_guests_ot.'&nbsp;'.$guests_ot_dsp.', '.$num_users_ot.'&nbsp;'.$users_ot_dsp.']</strong></dt>'."\n\t\t\t".'</dl>'."\n";
		}
	}
	// users online today 
		if (isset($ds_stats_conf['today_show'][$pun_user['group_id']]) && $ds_stats_conf['today_show'][$pun_user['group_id']] == 1)
	{
	$date = getdate(time() + $diff);
	$todaystamp = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
	
  // Caching of online today
  if (file_exists(FORUM_CACHE_DIR.'cache_ds_stats_today.php'))
  {
      include FORUM_CACHE_DIR.'cache_ds_stats_today.php';
  }
  else  
  {
      generate_ds_stats_today_cache($todaystamp, $all_users_online);
      include FORUM_CACHE_DIR.'cache_ds_stats_today.php';
  }
  foreach ($all_users_online  as $current_user_online) 
  {
      if ($current_user_online["user_id"] != 1) 
      {
          if (!isset($attended_ids[$current_user_online['user_id']])) 
          {
              generate_ds_stats_today_cache($todaystamp, $all_users_online);
              include FORUM_CACHE_DIR.'cache_ds_stats_today.php';
          }
      }
  }
  // Caching end

	$users_today = array();
	foreach ($attended_today as $user_online_today)
	{
		if ($pun_user['g_view_users'] == '1')
			$users_today[] =  "\n\t\t\t\t".'<dd><a '.((isset($ds_stats_conf['group_color'][$user_online_today['group_id']]) && $ds_stats_conf['topic_colors'] == '1') ? 'style="COLOR: #'.$ds_stats_conf['group_color'][$user_online_today['group_id']].'" ' : '').' href="profile.php?id='.$user_online_today['id'].'" title="'.sprintf($lang_usersonline['Last visit'], pun_htmlspecialchars($user_online_today['username']), format_time($user_online_today['last_visit'])).'">'.pun_htmlspecialchars($user_online_today['username']).'</a>';
		else
			$users_today[] =  "\n\t\t\t\t".'<dd><span '.((isset($ds_stats_conf['group_color'][$user_online_today['group_id']]) && $ds_stats_conf['topic_colors'] == '1') ? 'style="COLOR: #'.$ds_stats_conf['group_color'][$user_online_today['group_id']].'" ' : '').' >'.pun_htmlspecialchars($user_online_today['username']).'</span>';
	}
	if (count($users_today) > 0) 
		echo "\t\t\t".'<dl id="onlinelist" class="clearb">'."\n\t\t\t\t".'<dt>'.$lang_usersonline['Online today'].': </dt>'.implode(',</dd> ', $users_today).'</dd>'."\n\t\t\t".'</dl>'."\n";
}

if (isset($ds_stats_conf['show_legend'][$pun_user['group_id']]) && $ds_stats_conf['show_legend'][$pun_user['group_id']] == 1)
	{
	
    // Caching of legend
    if (file_exists(FORUM_CACHE_DIR.'cache_ds_stats_legend.php'))
    {
        include FORUM_CACHE_DIR.'cache_ds_stats_legend.php';
    }
    else  
    {
        generate_ds_stats_legend_cache();
        include FORUM_CACHE_DIR.'cache_ds_stats_legend.php';
    }
    // Caching end
	
		echo '			<div style="TEXT-ALIGN: center; FONT-SIZE: 0.8em"><hr size="1" style="margin:0;">';
		echo "\t\t\t\t".$lang_usersonline['Legend'].' ';
		foreach ($legend as $cur_group)
		{
			$usersonline_group = $cur_group['g_title'];
			echo '[<a '.((isset($ds_stats_conf['group_color'][$cur_group['g_id']]) && $ds_stats_conf['topic_colors'] == '1') ? ' style="COLOR: #'.$ds_stats_conf['group_color'][$cur_group['g_id']].'"' : '').' href="userlist.php?show_group='.$cur_group['g_id'].'">'.$usersonline_group.'</a>] ';
		}
		echo '			</div>';
	}
}
else
	echo "\t\t".'</dl>'."\n\t\t\t".'<div class="clearer"></div>'."\n";
?>
		</div>
	</div>
</div>
