<?php
//$Id:$
/***********************************************************************/

// Some info about your mod.
$mod_title      = 'DS User Stats';
$mod_version    = '1.0';
$release_date   = '2017-06-24';
$author         = 'DenisVS';
$based_on       = 'User Stats by Koos pampoen10@yahoo.com';
$author_email   = 'deniswebcomm@gmail.com';

// Set this to false if you haven't implemented the restore function (see below)
$mod_restore	= true;


// This following function will be called when the user presses the "Install" button.
function install()
{
	global $db, $db_type, $pun_config;

	//New Install
	switch ($db_type)
	{
		case 'pgsql':
			$db->start_transaction();

			$sql = 'CREATE TABLE '.$db->prefix."userstats (
					id SERIAL,
					username VARCHAR(200) NOT NULL DEFAULT '',
					userid INT NOT NULL DEFAULT 1,
					userip VARCHAR(15),
					subject VARCHAR(255) NOT NULL DEFAULT '',
					uri VARCHAR(200) NOT NULL DEFAULT '',
					date INT NOT NULL DEFAULT 0,
					browser VARCHAR(200) NOT NULL DEFAULT '',
					opsys VARCHAR(200) NOT NULL DEFAULT '',
					country VARCHAR(200) NOT NULL DEFAULT '',
					PRIMARY KEY (id)
					)";
			break;

		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db->prefix."userstats (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					username VARCHAR(200) NOT NULL DEFAULT '',
					userid INT(10) UNSIGNED NOT NULL DEFAULT 1,
					userip VARCHAR(15),
					subject VARCHAR(255) NOT NULL DEFAULT '',
					uri VARCHAR(200) NOT NULL DEFAULT '',
					date INT(10) UNSIGNED NOT NULL DEFAULT 0,
					browser VARCHAR(200) NOT NULL DEFAULT '',
					opsys VARCHAR(200) NOT NULL DEFAULT '',
					country VARCHAR(200) NOT NULL DEFAULT '',
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
		
		case 'mysqli_innodb':
		case 'mysql_innodb':
			$sql = 'CREATE TABLE '.$db->prefix."userstats (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					username VARCHAR(200) NOT NULL DEFAULT '',
					userid INT(10) UNSIGNED NOT NULL DEFAULT 1,
					userip VARCHAR(15),
					subject VARCHAR(255) NOT NULL DEFAULT '',
					uri VARCHAR(200) NOT NULL DEFAULT '',
					date INT(10) UNSIGNED NOT NULL DEFAULT 0,
					browser VARCHAR(200) NOT NULL DEFAULT '',
					opsys VARCHAR(200) NOT NULL DEFAULT '',
					country VARCHAR(200) NOT NULL DEFAULT '',
					PRIMARY KEY (id)
					)";
			break;

		case 'sqlite':
			$db->start_transaction();

			$sql = 'CREATE TABLE '.$db->prefix."userstats (
					id INTEGER NOT NULL,
					username VARCHAR(200) NOT NULL DEFAULT '',
					userid INTEGER NOT NULL DEFAULT 1,
					userip VARCHAR(15),
					subject VARCHAR(255) NOT NULL DEFAULT '',
					uri VARCHAR(200) NOT NULL DEFAULT '',
					date INTEGER NOT NULL DEFAULT 0,
					browser VARCHAR(200) NOT NULL DEFAULT '',
					opsys VARCHAR(200) NOT NULL DEFAULT '',
					country VARCHAR(200) NOT NULL DEFAULT '',
					PRIMARY KEY (id)
					)";
			break;
	}


	
	
	$db->query($sql) or error('Unable to create table '.$db->prefix.'userstats.', __FILE__, __LINE__, $db->error());

	if ($db_type == 'pgsql' || $db_type == 'sqlite')
		$db->end_transaction();
}

// This following function will be called when the user presses the "Restore" button (only if $mod_uninstall is true (see above))
function restore()
{
	global $db, $db_type, $pun_config;

	if ($db_type == 'pgsql' || $db_type == 'sqlite')
		$db->start_transaction();

	$db->query('DROP TABLE '.$db->prefix.'userstats') or error('Unable to remove table '.$db->prefix.'userstats.', __FILE__, __LINE__, $db->error());

	if ($db_type == 'pgsql' || $db_type == 'sqlite')
		$db->end_transaction();
}

/***********************************************************************/

// DO NOT EDIT ANYTHING BELOW THIS LINE!


// Circumvent maintenance mode
define('PUN_TURN_OFF_MAINT', 1);
define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

// We want the complete error message if the script fails
if (!defined('PUN_DEBUG'))
	define('PUN_DEBUG', 1);

$version = explode(".", $pun_config['o_cur_version']);
// Make sure we are running a PunBB version that this mod works with


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $mod_title ?> installation</title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_config['o_default_style'].'.css' ?>" />
</head>
<body>

<div id="punwrap">
<div id="puninstall" class="pun" style="margin: 10% 20% auto 20%">

<?php

if (isset($_POST['form_sent']))
{
	if (isset($_POST['install']))
	{
		// Run the install function (defined above)
		install();

?>
<div class="block">
	<h2><span>Installation successful</span></h2>
	<div class="box">
		<div class="inbox">
			<p>Your database has been successfully prepared for <?php echo pun_htmlspecialchars($mod_title) ?>. See readme.txt for further instructions.</p>
		</div>
	</div>
</div>
<?php

	}
	else
	{
		// Run the restore function (defined above)
		restore();

?>
<div class="block">
	<h2><span>Restore successful</span></h2>
	<div class="box">
		<div class="inbox">
			<p>Your database has been successfully restored.</p>
		</div>
	</div>
</div>
<?php

	}
}
else
{

?>
<div class="blockform">
	<h2><span>Mod installation</span></h2>
	<div class="box">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?foo=bar">
			<div><input type="hidden" name="form_sent" value="1" /></div>
			<div class="inform">
				<p>This script will update your database to work with the following modification:</p>
				<p><strong>Mod title:</strong> <?php echo pun_htmlspecialchars($mod_title).' '.$mod_version ?></p>
				<p><strong>Author:</strong> <?php echo pun_htmlspecialchars($author) ?> (<a href="mailto:<?php echo pun_htmlspecialchars($author_email) ?>"><?php echo pun_htmlspecialchars($author_email) ?></a>)</p>
				<p><strong>Disclaimer:</strong> Mods are not officially supported by PunBB. Mods generally can't be uninstalled without running SQL queries manually against the database. Make backups of all data you deem necessary before installing.</p>
<?php if ($mod_restore): ?>				<p>If you've previously installed this mod and would like to uninstall it, you can click the restore button below to restore the database.</p>
<?php endif; ?>			</div>
			<p><input type="submit" name="install" value="Install" /><?php if ($mod_restore): ?><input type="submit" name="restore" value="Restore" /><?php endif; ?></p>
		</form>
	</div>
</div>
<?php

}

?>

</div>
</div>

</body>
</html>
