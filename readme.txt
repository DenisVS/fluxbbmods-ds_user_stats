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

TODO  http://example.com/admin_groups.php  Fatal error: Call to undefined function generate_ds_stats_legend_cache() in /data/sites/example.com/www/admin_groups.php on line 552

codepage userstats


Users Current Position Online
Mod version:  1.7
*Исключть шаги:
2
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
	message($lang_common['No permission'], false, '403 Forbidden');

#
#---------[ . AFTER, ADD ]---------------------------------------------------
#

if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
    require PUN_ROOT.'include/cache.php';

#
#---------[ . FIND (line: ) ]---------------------------------------------
#

generate_admin_menu('groups');


NOTE: There should be four occurrences of this in the code of admin_groups.php. The next step applies to all of them.
#
#---------[ . AFTER, ADD FOR EACH ENTRY(4 TIMES)! ]---------------------------
#


generate_ds_stats_legend_cache();

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



