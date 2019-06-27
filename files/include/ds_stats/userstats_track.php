<?php
  /**
   * User Stats - include/ds_stats/userstats_track.php
   * //$Id$
   * @author : Koos
   * @email  : pampoen10@yahoo.com
   * @version 1.2.2
   * @release date : 2009-09-20
   */


define('PUN_ROOT', './');
$ds_stats_conf = unserialize($pun_config['o_ds_stats']);

if (isset($ds_stats_conf['IP'][$_SERVER ['REMOTE_ADDR']])) $stopLog = true;


if ($ds_stats_conf['stats_enabled'])
{
	if ($pun_user['is_bot'])
	{
		if (isset($ds_stats_conf['bots'][$pun_user['is_bot']])) {
			if ($ds_stats_conf['bots'][$pun_user['is_bot']] == 0) {$stopLog = true;}
		} else {
			if (isset($ds_stats_conf['otherBots'])) {
				if ($ds_stats_conf['otherBots'] == 0)	{$stopLog = true;}
			}
		}

		if (!$stopLog) {
			require_once ('include/ua_parser/UserAgentParser.php');
			$userInfo = parse_user_agent ($_SERVER['HTTP_USER_AGENT']);
			$username = $pun_user['is_bot'];
			$browser = 'Robot';
			$opsys = $userInfo['browser'].'  '.$userInfo ['system']['name'].'  '.$userInfo['platform'].'  '.$userInfo ['system']['details'];
		}
	}
	else
	{
		require_once ('include/ua_parser/UserAgentParser.php');
		$userInfo = parse_user_agent ($_SERVER['HTTP_USER_AGENT']);
		$browser = $userInfo ['browser'].' '.$userInfo['version'];
		$opsys = $userInfo ['system']['name'].' '.$userInfo['platform'].' '.$userInfo ['system']['details'];
		$username = $pun_user['username'];
	}

	if(!$stopLog)
	{
		$now = time();
		// Step 7 - get topic subject
		$js_subject = (isset($cur_topic['subject']) && ($cur_topic['subject'] != "")) ? $cur_topic['subject'] : "";

		// Step 8 - get URI
		// Construct REQUEST_URI if it isn't set
		if (!isset($_SERVER['REQUEST_URI']))
			$_SERVER['REQUEST_URI'] = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '').'?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');

		if (isset($_SERVER['REQUEST_URI']) && ($_SERVER['REQUEST_URI'] != ""))
		{
		$js_uri = str_replace('ajax&', '', $_SERVER['REQUEST_URI']) ;
		} else {$_SERVER['REQUEST_URI'] = 'unknown';}

		// Step 9 - get country
		if  (function_exists('apache_request_headers'))	{
			$headers = apache_request_headers();
			if (isset($headers['COUNTRY_CODE']) && isset($headers['COUNTRY_NAME']) && isset($headers['CITY_NAME']))	{
				$countryData['country_name'] = $headers['COUNTRY_NAME'];
				$countryData['city'] = $headers['CITY_NAME'];
			}
		}
		if (function_exists('geoip_record_by_name'))	{
			$countryData = geoip_record_by_name($_SERVER ['REMOTE_ADDR']);
		}

		$country = $countryData['country_name'] .", ". $countryData['city'];
		$result = $db->query('SELECT id FROM '.$db->prefix.'userstats ') or error('Unable to fetch userstats list for forum', __FILE__, __LINE__, $db->error());
		$num_entries_count = $db->num_rows($result);
		if ($num_entries_count > $ds_stats_conf['ent_in_database'])
		{
			// Let's delete the older entries so that there are only $ds_stats_conf['ent_in_database']
			// Pull userstats
			$result = $db->query('SELECT * FROM '.$db->prefix.'userstats ORDER BY date DESC ') or error('Unable to fetch userstats for forum', __FILE__, __LINE__, $db->error());
			// Output file info
			if($db->num_rows($result))
			{
				$entry_count = 0;
				while ($cur_entry = $db->fetch_assoc($result))
				{
					++$entry_count;
					if ($entry_count == $ds_stats_conf['ent_in_database'])
					{
						$del_date = $cur_entry['date'];
						break;
					}
				}
			}
			// Delete some stats
			$db->query('DELETE FROM '.$db->prefix.'userstats WHERE date<'.$del_date) or error('Unable to delete userstats', __FILE__, __LINE__, $db->error());
		}
		// Check if it was logged 120 seconds ago
		//- we don't want to log consecutive visits (e.g. because of refresh) by the same user to the same page within a 120 second period
		$result = $db->query('SELECT * FROM '.$db->prefix.'userstats WHERE userip=\''.$_SERVER ['REMOTE_ADDR'].'\' ORDER BY date DESC LIMIT 1') or error('Unable to fetch latest userstats entry', __FILE__, __LINE__, $db->error());
		$userstats_latestentry = $db->fetch_assoc($result);
		$userstats_check = (
		$pun_user['id'] == $userstats_latestentry['userid'] &&
		$_SERVER ['REMOTE_ADDR'] == $userstats_latestentry['userip'] &&
		$js_uri == $userstats_latestentry['uri'] &&
		$browser == $userstats_latestentry['browser'] &&
		$opsys == $userstats_latestentry['opsys'] &&
		$country == $userstats_latestentry['country'] &&
		((time()-$userstats_latestentry['date']) < 120)
		) ? "1" : "0";

		$SHR = explode('/',$_SERVER['HTTP_REFERER']);

		if (!
			((end($SHR) == 'userstats.php') || ($userstats_check))
			)
		{
			// Log it
			if ($num_entries_count > ($ds_stats_conf['ent_in_database']-1))
			{
				// Get minimum date's id
				$result = $db->query('SELECT id, min(date) as min_date FROM '.$db->prefix.'userstats GROUP BY id ORDER BY min_date ') or error('Unable to fetch diary info', __FILE__, __LINE__, $db->error());
				while ($cur_entry = $db->fetch_assoc($result))
				{
					$mindate_id = $cur_entry[id];
					break;
				}
				// Update the userstats entry
				$db->query('UPDATE '.$db->prefix.'userstats SET username=\''.$db->escape($username).'\', userid='.$pun_user['id'].', userip=\''.$db->escape($_SERVER ['REMOTE_ADDR']).'\', date='.$now.', browser=\''.$db->escape($browser).'\', opsys=\''.$db->escape($opsys).'\', subject=\''.$db->escape($js_subject).'\', uri=\''.$db->escape($js_uri).'\', country=\''.$db->escape($country).'\' WHERE id='.$mindate_id) or error('Unable to update userstats entry', __FILE__, __LINE__, $db->error());
			}
			else
			{
			// Insert userstats
			$db->query('INSERT INTO '.$db->prefix.'userstats (username, userid, userip, date, browser, opsys, subject, uri, country) VALUES(\''.$db->escape($username).'\', '.$pun_user['id'].', \''.$db->escape($_SERVER ['REMOTE_ADDR']).'\', '.$now.', \''.$db->escape($browser).'\', \''.$db->escape($opsys).'\', \''.$db->escape($js_subject).'\', \''.$db->escape($js_uri).'\', \''.$db->escape($country).'\')') or error('Unable to insert into userstats table', __FILE__, __LINE__, $db->error());
			//$new_pid = $db->insert_id();
			}
		}
	}
}

?>
