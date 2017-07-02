




include/cache.php


# ADD AT THE END

//
// Generate the stats cache PHP script
//
function generate_ds_stats_today_cache($todaystamp, $online_list)
{
	global $db;

	$result = $db->query('SELECT username, id, group_id, last_visit from '.$db->prefix.'users WHERE last_visit >= '.$todaystamp.' ORDER by last_visit DESC') or error('Unable to find the list of the users online today', __FILE__, __LINE__, $db->error());

	$attended_today = array();
	while ($today = $db->fetch_assoc($result))
		$attended_today[] = $today; // Get users today

  // flipp array - get users tuday. Keys is ID.
  foreach ($attended_today  as $current_user_today) 
  {
    
    if ($current_user_today["id"] != 1) 
    {
      $attended_ids[$current_user_today['id']] = true;
    }
  }
	// Output list as PHP code
	$content = '<?php'."\n\n".'define(\'ATTENDED_TODAY_LOADED\', 1);'."\n\n".'$attended_ids = '.var_export($attended_ids, true).';'."\n\n".'$attended_today = '.var_export($attended_today, true).';'."\n\n".'?>';
	fluxbb_write_cache_file('cache_ds_stats_today.php', $content);
}

