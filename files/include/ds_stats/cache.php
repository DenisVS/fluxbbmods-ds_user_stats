<?php

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';



    function unique_array_by_key($array, $key) { 
      $temp_array = array(); 
      $i = 0; 
      $key_array = array(); 
      foreach($array as $val) { 
        if (!in_array($val[$key], $key_array)) { 
          $key_array[$i] = $val[$key]; 
          $temp_array[$i] = $val; 
        } 
        $i++; 
      } 
      return $temp_array; 
    }

//
// Generate the stats today cache PHP script
//
function generate_ds_stats_today_cache($todaystamp, $online_list, $new_user, $localUsersWeekDay, $diff_user_time)
{

	global $db;

	$result = $db->query('SELECT username, id, group_id, last_visit from '.$db->prefix.'users WHERE last_visit >= '.$todaystamp.' ORDER by last_visit DESC') or error('Unable to find the list of the users online today', __FILE__, __LINE__, $db->error());

	$attended_today = array();
	while ($today = $db->fetch_assoc($result))
		$attended_today[] = $today; // Get users today

// to avoid refreshing until new user off
if ($new_user != false) 
{
    $current['id']  = $new_user['user_id'];
    $current["username"] = $new_user["ident"];
    $current["group_id"] = $new_user["group_id"];
    $current["last_visit"] = time();
    $attended_today[] =  $current;
}


    
	$attended_today = unique_array_by_key($attended_today, 'id'); 

  // flipp array - get users tuday. Keys is ID.
  foreach ($attended_today  as $current_user_today) 
  {
    if ($current_user_today["id"] != 1) 
    {
      $attended_ids[$current_user_today['id']] = true;
    }
  }
  //if ($new_user_id != false) $attended_ids[$new_user_id] = true; // to avoid refreshing until new user off
	// Output list as PHP code
	$content = '<?php'."\n\n".'define(\'ATTENDED_TODAY_LOADED\', 1);'."\n\n".'$attended_ids = '.var_export($attended_ids, true).';'."\n\n".'$attended_today = '.var_export($attended_today, true).';'."\n\n".'$previousWeekDay = '.$localUsersWeekDay.';'."\n\n".'?>';
	fluxbb_write_cache_file('cache_ds_stats_today_' . $diff_user_time . '.php', $content);
}

//
// Generate the stats legend PHP script
//
function generate_ds_stats_legend_cache()
{
	global $db;
  $result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id != 3 ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
		while ($cur_group = $db->fetch_assoc($result))
      $legend[] = $cur_group;

	// Output list as PHP code
	$content = '<?php'."\n\n".'define(\'LEGEND_LOADED\', 1);'."\n\n".'$legend = '.var_export($legend, true).';'."\n\n".'?>';
	fluxbb_write_cache_file('cache_ds_stats_legend.php', $content);
}

//
// Generate the stats past online cache PHP script
//
function generate_ds_stats_past_cache($period_check, $period_unit, $ttl_past_cache)
{

	global $db;
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
				$period_check = time() - $period*60;
			}
			else
			{
				// can use hours
				$period = ceil((time()-$period_min)/60/60);
				$period_check = time() - $period*60*60;
			}
		}
		else
		{
			// use minutes
			$period = ceil((time()-$period_min)/60);
			$period_check = time() - $period*60;
		}
	}
	$result2 = $db->query('SELECT DISTINCT u.username, u.userid, u.userip, r.group_id FROM '.$db->prefix.'userstats AS u LEFT JOIN '.$db->prefix.'users as r ON u.userid = r.id WHERE u.date >='.$period_check.' AND u.browser <> \'Robot\' ORDER BY u.userip') or error('Unable to fetch user stats', __FILE__, __LINE__, $db->error());
	$num_entries_count = $db->num_rows($result2);	// Count entries
	while ($current_past_online = $db->fetch_assoc($result2))
	{
		$users_past_online[] = $current_past_online;
	}
	
	// Output list past online as PHP code
	$content = '<?php'."\n\n".'define(\'PAST_ONLINE_LOADED\', 1);'."\n\n".'$users_past_online = '.var_export($users_past_online, true).';'."\n\n".'$period_min = '.var_export($period_min, true).';'."\n".'$num_entries_count = '.var_export($num_entries_count, true).";\n".'$ttl_past_cache = '.var_export($ttl_past_cache, true).";\n".'$past_timestamp = '.var_export(time(), true).";\n\n".'?>';
	fluxbb_write_cache_file('cache_ds_stats_past.php', $content);
}


?>
