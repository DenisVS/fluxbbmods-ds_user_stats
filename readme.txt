##
##        Mod title:  DS Stats
##
##      Mod version:  1.0
##  Works on FluxBB:  1.5.10
##     Release date:  2017-07-02
##           Author:  DenisVS (deniswebcomm@gmail.com)
##
##      Description:  
##                    
##                    
##
##
##   Affected files:  
##                    
##                    footer.php
##                    viewtopic.php
##                    
##
##       Affects DB:  Yes
##
##            Notes:  This is just a template. Don't try to install it! Rows
##                    in this header should be no longer than 78 characters
##                    wide. Edit this file and save it as readme.txt. Include
##                    the file in the archive of your mod. The mod disclaimer
##                    below this paragraph must be left intact. This space
##                    would otherwise be a good space to brag about your mad
##                    modding skills :)
##
##       DISCLAIMER:  Please note that "mods" are not officially supported by
##                    FluxBB. Installation of this modification is done at 
##                    your own risk. Backup your forum database and any and
##                    all applicable files before proceeding.
##
##


Users Current Position Online
Mod version:  1.7
Исключть шаги
12
13
14
15
16
17
18
19


#
#---------[ 1. UPLOAD ]-------------------------------------------------------
#


#
#---------[ 2. RUN ]----------------------------------------------------------
#

install_mod.php

#
#---------[ 3. DELETE ]-------------------------------------------------------
#

install_mod.php

#
#---------[ . OPEN ]---------------------------------------------------------
#

admin_groups.php

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

if ($pun_user['g_id'] != PUN_ADMIN)
	message($lang_common['No permission']);

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

generate_admin_menu('groups');

#
#---------[ . AFTER, ADD FOR EACH ENTRY(4 TIMES)! ]---------------------------------------------------
#

generate_ds_stats_legend_cache();

#
#---------[ . OPEN ]---------------------------------------------------------
#

include/cache.php

#
#---------[ . ADD AT THE END OF FILE ]---------------------------------------------------
#


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
function generate_ds_stats_past_cache($period_check, $period_unit, $ttl_past_cache, $num_entries_count_bak)
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
		while ($pun_user_online = $db->fetch_assoc($result2))
    $users_past_online[] = $pun_user_online;

	// Output list past online as PHP code
	$content = '<?php'."\n\n".'define(\'PAST_ONLINE_LOADED\', 1);'."\n\n".'$users_past_online = '.var_export($users_past_online, true).';'."\n\n".'$period_min = '.var_export($period_min, true).';'."\n".'$num_entries_count = '.var_export($num_entries_count, true).";\n".'$ttl_past_cache = '.var_export($ttl_past_cache, true).";\n".'$num_entries_count_bak = '.var_export($num_entries_count_bak, true).";\n".'$past_timestamp = '.var_export(time(), true).";\n\n".'?>';
	fluxbb_write_cache_file('cache_ds_stats_past.php', $content);
}

#
#---------[ . OPEN ]---------------------------------------------------------
#

functions.php

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

		$pun_user['is_guest'] = false;
		$pun_user['is_admmod'] = $pun_user['g_id'] == PUN_ADMIN || $pun_user['g_moderator'] == '1';

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#

		$pun_user['is_bot'] = false;


#
#---------[ . FIND (line: ) ]---------------------------------------------
#

function set_default_user()
{
	global $db, $db_type, $pun_user, $pun_config;
 
	$remote_addr = get_remote_address();

#
#---------[ . BEFORE, ADD ]---------------------------------------------------
#

use Jaybizzle\CrawlerDetect\CrawlerDetect;

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#

  if (!defined('FORUM_BOT_FUNCTIONS_LOADED'))
  {
      require_once 'include/crawler-detect/vendor/autoload.php';
      $CrawlerDetect = new CrawlerDetect;
      if($CrawlerDetect->isCrawler($_SERVER['HTTP_USER_AGENT']))
      {
            $is_bot = $CrawlerDetect->getMatches($_SERVER['HTTP_USER_AGENT']);
            $remote_addr = $remote_addr.'[Bot]'.$is_bot;
      }
      define('FORUM_BOT_FUNCTIONS_LOADED', true);
  }

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

  $pun_user['is_guest'] = true;
  $pun_user['is_admmod'] = false;

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#
#
        
	$pun_user['is_bot'] = (isset($is_bot) ? $is_bot : false);

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

function update_users_online()
{
	global $db, $pun_config;

	$now = time();

	// Fetch all online list entries that are older than "o_timeout_online"
	$result = $db->query('SELECT user_id, ident, logged, idle FROM '.$db->prefix.'online WHERE logged<'.($now-$pun_config['o_timeout_online'])) or error('Unable to fetch old entries from online list', __FILE__, __LINE__, $db->error());
	while ($cur_user = $db->fetch_assoc($result))
	{
		// If the entry is a guest, delete it
		if ($cur_user['user_id'] == '1')
			$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($cur_user['ident']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
		else
		{
			// If the entry is older than "o_timeout_visit", update last_visit for the user in question, then delete him/her from the online list
			if ($cur_user['logged'] < ($now-$pun_config['o_timeout_visit']))
			{
				$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$cur_user['logged'].' WHERE id='.$cur_user['user_id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
				$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$cur_user['user_id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
			}
			else if ($cur_user['idle'] == '0')
				$db->query('UPDATE '.$db->prefix.'online SET idle=1 WHERE user_id='.$cur_user['user_id']) or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
		}
	}
}

#
#---------[ . REPLACE WITH ]-------------------------------------------------
#


function update_users_online()
{
	global $db, $pun_config, $pun_user, $online_users, $online_guests, $cur_position;

	$now = time();
	$cur_o_timeout = $now - $pun_config['o_timeout_online'];

	// Fetch all online list entries that are older than "o_timeout_online"
	$result = $db->query('SELECT * FROM '.$db->prefix.'online') or error('Unable to fetch old entries from online list', __FILE__, __LINE__, $db->error());
	while ($cur_user = $db->fetch_assoc($result))
	{	
	
		if ($cur_user['logged'] < $cur_o_timeout)
		{
			// If the entry is a guest, delete it
			if ($cur_user['user_id'] == '1')
				$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($cur_user['ident']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
			else
			{
				// If the entry is older than "o_timeout_visit", update last_visit for the user in question, then delete him/her from the online list
				if ($cur_user['logged'] < ($now-$pun_config['o_timeout_visit']))
				{
					$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$cur_user['logged'].' WHERE id='.$cur_user['user_id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
					$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$cur_user['user_id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
				}
				else if ($cur_user['idle'] == '0')
					$db->query('UPDATE '.$db->prefix.'online SET idle=1 WHERE user_id='.$cur_user['user_id']) or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
			}
		}
		else
		{
			
			if ($cur_user['user_id'] == 1)
				$online_guests[] = $cur_user['ident'];
			else
				$online_users[$cur_user['user_id']] = $cur_user['ident'];

		}
	}
	
	if (!$pun_user['is_bot'])
	{
		if ($pun_user['is_guest'])
			$db->query('UPDATE '.$db->prefix.'online SET currently = \''.$db->escape($cur_position).'\' WHERE ident=\''.$db->escape(get_remote_address()).'\'') or error('Unable to update user position in the online list1', __FILE__, __LINE__, $db->error());
		else	
			$db->query('UPDATE '.$db->prefix.'online SET currently= \''.$db->escape($cur_position).'\' WHERE user_id='.$pun_user['id']) or error('Unable to update user position in the online list2', __FILE__, __LINE__, $db->error());
	}
}


#
#---------[ . OPEN ]---------------------------------------------------------
#

viewtopic.php

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

require PUN_ROOT.'include/common.php';

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#


$ds_stats_conf = unserialize($pun_config['o_ds_stats']);

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

require PUN_ROOT.'header.php';

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#

require PUN_ROOT.'include/ds_stats/userstats_track.php'; // Load userstats track


#
#---------[ . FIND (line: ) ]---------------------------------------------
#

			$username = '<a href="profile.php?id='.$cur_post['poster_id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';

#
#---------[ . REPLACE WITH ]-------------------------------------------------
#

$username = '<a '.((isset($ds_stats_conf['group_color'][$cur_post['g_id']]) && $ds_stats_conf['topic_colors'] == '1') ? 'style="COLOR: #'.$ds_stats_conf['group_color'][$cur_post['g_id']].'" ' : '').'href="profile.php?id='.$cur_post['poster_id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';

#
#---------[ . OPEN ]---------------------------------------------------------
#

index.php

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

require PUN_ROOT.'header.php';

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#

require PUN_ROOT.'include/ds_stats/userstats_track.php';  // Load userstats module

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

list($stats['total_topics'], $stats['total_posts']) = array_map('intval', $db->fetch_row($result));

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#


// USERSONLINE MOD:
$ds_stats_conf = unserialize($pun_config['o_ds_stats']);
if ($ds_stats_conf['online_enabled'])
{	
	require PUN_ROOT.'include/ds_stats/usersonline.php';
}
else
{

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

$footer_style = 'index';
require PUN_ROOT.'footer.php';

#
#---------[ . BEFORE, ADD ]---------------------------------------------------
#

}



