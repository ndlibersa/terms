<?php
//this script runs entire installation process in 5 steps

//take "step" variable to determine which step the current is
$step = $_POST['step'];


//perform field validation(step 3) and database connection tests (step 3) and send back to previous step if not working
$errorMessage = array();
if ($step == "3"){

	$database_host = $_POST['database_host'];
	$database_name = $_POST['database_name'];
	$database_username = $_POST['database_username'];
	$database_password = trim($_POST['database_password']);
	$resolver = $_POST['resolver'];
	$open_url = $_POST['open_url'];
	$sid = trim($_POST['sid']);
	$client_identifier = trim($_POST['client_identifier']);


	if (!$database_host) $errorMessage[] = 'Host name is required';
	if (!$database_name) $errorMessage[] = 'Database name is required';
	if (!$database_username) $errorMessage[] = 'Database User name is required';
	if (!$database_password) $errorMessage[] = 'Database Password is required';
	if (!$resolver) $errorMessage[] = 'Resolver is required';
	if (($resolver != 'SFX') && ($resolver != 'SerialsSolutions')) $errorMessage[] = 'Resolver must be either SFX or Serials Solutions';
	if (($resolver == 'SFX') && (!$open_url)) $errorMessage[] = 'Open URL is required for SFX';
	if (($resolver == 'SerialsSolutions') && (!$client_identifier)) $errorMessage[] = 'Client Identifier is required for Serials Solutions';

	//only continue to checking DB connections if there were no errors this far
	if (count($errorMessage) > 0){
		$step="2";
	}else{

		//write the config file
		$configFile = "../admin/configuration.ini";
		$fh = fopen($configFile, 'w');

		if (!$fh){
			$errorMessage[] = "Could not open file " . $configFile . ".  Please verify you can write to the /admin/ directory.";
		}else{

			//verify that we can select the proper table from the database with the info provided

			//first check connecting to host
			$link = @mysql_connect("$database_host", "$database_username", "$database_password");
			if (!$link) {
				$errorMessage[] = "Could not connect to the server '" . $database_host . "'<br />MySQL Error: " . mysql_error();
			}else{

				//next check that the database exists
				$dbcheck = @mysql_select_db("$database_name");
				if (!$dbcheck) {
					$errorMessage[] = "Unable to access the database '" . $database_name . "'.  Please verify it has been created.<br />MySQL Error: " . mysql_error();
				}else{

					//make sure the tables don't already exist - otherwise this script will overwrite all of the data!
					$query = "SELECT count(*) count FROM information_schema.`COLUMNS` WHERE table_schema = '" . $database_name . "' AND table_name='Expression'";

					//if Expression table doesn't exist in this schema, error out
					if (!$row = mysql_fetch_array(mysql_query($query))){
						$errorMessage[] = "Please verify your database user has access to select from the information_schema MySQL metadata database.";
					}else{
						if ($row['count'] > 0){
							//successful - now we should check that sfx works as well
							if ($resolver == "SFX"){
								$test_open_url = $open_url;

								//check if there is already a ? in the URL so that we don't add another when appending the parms
								if (strpos($open_url, "?") > 0){
									$test_open_url .= "&sid=" . $sid . "&sfx.response_type=simplexml";
								}else{
									$test_open_url .= "?sid=" . $sid . "&sfx.response_type=simplexml";
								}


								//Make the call to SFX and load results
								@$xml = simplexml_load_file($test_open_url);

								if (!$xml){
									$errorMessage[] = "Connection to SFX failed.  Please verify open URL: <br />" . $open_url;
								}

							}else{
								$open_url = "http://" . $client_identifier . ".openurl.xml.serialssolutions.com/openurlxml?version=1.0&url_ver=Z39.88-2004";
								//load xml from the open url into DOM
								$dom = new DomDocument();
								$dom->load($open_url);

								$xmlData = array();
								$xmlData = $dom->getElementsByTagName('diagnostic');


								foreach ($xmlData as $diag) {
									foreach($diag->childNodes as $d) {
										if (($d->nodeName == "ssdiag:details") && ($d->nodeValue == "Exception Occurred")){
											$errorMessage[] = "Connection to Serials Solutions failed.  Please verify open URL: <br />" . $open_url;
										}
									}
								}


								//this isn't needed in the config file for serials solutions
								$open_url='';
							}


							if (count($errorMessage) == 0){
								//success - now write the config file
								$iniData = array();
								$iniData[] = "[settings]";
								$iniData[] = "resolver =" . $resolver;
								$iniData[] = "open_url=" . $open_url;
								$iniData[] = "sid=" . $sid;
								$iniData[] = "client_identifier=" . $client_identifier;

								$iniData[] = "\n[database]";
								$iniData[] = "type = \"mysql\"";
								$iniData[] = "host = \"" . $database_host . "\"";
								$iniData[] = "name = \"" . $database_name . "\"";
								$iniData[] = "username = \"" . $database_username . "\"";
								$iniData[] = "password = \"" . $database_password . "\"";

								fwrite($fh, implode("\n",$iniData));
								fclose($fh);

							}
						}else{
							$errorMessage[] = "CORAL Licensing table Expression not found in schema " . $database_name . ".  Please verify that you are pointing to the correct CORAL Licensing schema.";
						}
					}
				}
			}






		}


	}

	if (count($errorMessage) > 0){
		$step="2";
	}


}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CORAL Installation</title>
<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
<center>
<table style='width:700px;'>
<tr>
<td style='vertical-align:top;'>
<div style="text-align:left;">


<?php if(!$step){ ?>

	<h3>Welcome to a new CORAL License Module Terms Tool Add-On installation!</h3>
	This installation will:
	<ul>
		<li>Check that you are running PHP 5</li>
		<li>Set up the config file with settings to connect to your CORAL Licensing Database and SFX or Serials Solutions instance</li>
	</ul>

	<br />
	To get started you should:
	<ul>
		<li>Know your host, username and password for MySQL with permissions to select from your CORAL Licensing Database</li>
		<li>To use SFX, know your SFX Open URL and SID</li>
		<li>To use Serials Solutions, know your client identifier</li>
		<li>Verify that your /admin/ directory is writable by server during the installation process (chmod 777).  After installation you should chmod it back and remove the /install/ directory.</li>
	</ul>


	<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
	<input type='hidden' name='step' value='1'>
	<input type="submit" value="Continue" name="submit">
	</form>


<?php
//first step - check system info and verify php 5
} else if ($step == '1') {
	ob_start();
    phpinfo(-1);
    $phpinfo = array('phpinfo' => array());
    if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
    foreach($matches as $match){
        if(strlen($match[1]))
            $phpinfo[$match[1]] = array();
        elseif(isset($match[3]))
            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
        else
            $phpinfo[end(array_keys($phpinfo))][] = $match[2];
    }




    ?>

	<h3>Getting system info and verifying php version</h3>
	<ul>
	<li>System: <?php echo $phpinfo['phpinfo']['System'];?></li>
    <li>PHP version: <?php echo phpversion();?></li>
    <li>Server API: <?php echo $phpinfo['phpinfo']['Server API'];?></li>
	</ul>

	<br />

	<?php


	if (phpversion() >= 5){
	?>
		<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
		<input type='hidden' name='step' value='2'>
		<input type="submit" value="Continue" name="submit">
		</form>
	<?php
	}else{
		echo "<span style='font-size=115%;color:red;'>PHP 5 is not installed on this server!  Installation will not continue.</font>";
	}

//second step - to write configuration file ask for DB info with select permissions and SFX information
} else if ($step == '2') {

	if (!$database_host) $database_host='localhost';
	if (!$database_name) $database_name='coral_licensing_prod';
	?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
		<h3>MySQL info with permissions select from your CORAL Licensing Database</h3>
		<?php
			if (count($errorMessage) > 0){
				echo "<span style='color:red'><b>The following errors occurred:</b><br /><ul>";
				foreach ($errorMessage as $err)
					echo "<li>" . $err . "</li>";
				echo "</ul></span>";
			}
		?>
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<tr>
				<td>&nbsp;Database Host</td>
				<td>
					<input type="text" name="database_host" value='<?php echo $database_host?>' size="30">
				</td>
			</tr>
			<tr>
				<td>&nbsp;Database Schema Name</td>
				<td>
					<input type="text" name="database_name" size="30" value="<?php echo $database_name?>">
				</td>
			</tr>
			<tr>
				<td>&nbsp;Database Username</td>
				<td>
					<input type="text" name="database_username" size="30" value="<?php echo $database_username?>">
				</td>
			</tr>
			<tr>
				<td>&nbsp;Database Password</td>
				<td>
					<input type="password" name="database_password" size="30" value="<?php echo $database_password?>">
				</td>
			</tr>
			<tr>
				<td>&nbsp;Resolver</td>
				<td>
					<select name="resolver" id="resolver">
					<option value='SFX' <?php if ($resolver == 'SFX') echo 'selected'; ?>>SFX</option>
					<option value='SerialsSolutions' <?php if ($resolver == 'SerialsSolutions') echo 'selected'; ?>>Serials Solutions</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;Open URL <span style='font-size:90%; color:red'>(SFX only)</span></td>
				<td>
					<input type="text" name="open_url" size="30" value="<?php echo $open_url?>">
				</td>
			</tr>
			<tr>
				<td>&nbsp;Source ID <span style='font-size:90%; color:red'>(SFX only)</span></td>
				<td>
					<input type="text" name="sid" size="30" value="<?php echo $sid?>">
				</td>
			</tr>

			<tr>
				<td>&nbsp;Client Identifier <span style='font-size:90%; color:red'>(Serials Solutions only)</span></td>
				<td>
					<input type="text" name="client_identifier" size="30" value="<?php echo $client_identifier?>">
				</td>
			</tr>

			<tr>
				<td colspan=2>&nbsp;</td>
			</tr>
			<tr>
				<td align='left'>&nbsp;</td>
				<td align='left'>
				<input type='hidden' name='step' value='3'>
				<input type="submit" value="Continue" name="submit">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" value="Cancel" onclick="document.location.href='index.php'">
				</td>
			</tr>

		</table>
		</form>
<?php
//third step - ask for DB info to log in from CORAL
} else if ($step == '3') {
?>
	<h3>CORAL Licensing Terms Tool Add-On installation is now complete!</h3>
	It is recommended you now:
	<ul>
		<li>Set up your .htaccess file</li>
		<li>Remove the /install/ directory for security purposes</li>
	</ul>

<?php
}
?>

</td>
</tr>
</table>
<br />
</center>


</body>
</html>