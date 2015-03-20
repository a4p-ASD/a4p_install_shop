<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	2.0.0
 *	@date:		23.02.2015
 *
 *
 *	_a4p_installClient2.php
 *
 *	apps4print - download, unzip and install oxid + -modules (a4p + ioly)
 *
 */


/**
 *	@desc:
		Download- und Installationsscript für OXID-Shops + -Module
	
 *	@hints:
		install OXID benötigt unzip als Voraussetzung
	
 *	@how to use:
		set IP whitelist ( line ~85 )
		edit default values ( line ~110 )
		copy to webserver document root
		open with webbrowser
		test and customize

 *	@warnings:
		Beta!!!
		verstößt gegen fast jeden Coding Standard
		kann rekursiv Ordner und Dateien löschen
		keine Prüfung der Eingaben
		greift direkt auf $_GET und $_POST-Variablen zu
		wenig Fehlerprüfungen
		dafür vieles quick&dirty
		Benutzung auf eigene Gefahr und ohne Gewähr!

 *	@longdesc:
		download von PE/EE über http://support.oxid-esales.com/, wenn htaccess-Zugang eingegeben ist
		download der OXID-Version für PE/EE je nach PHP-Version (5.3/5.4) oder nur SOURCE
		download zuerst mit cURL, wenn Datei nicht vorhanden oder 0 Byte: download mit wget

 *	@requirements:
		mysql command line
		unzip command line	oder	php ZipArchive
		wget command line	oder	php cURL

 *	@functions:
		Download von OXID-Editionen - und -Versionen
		Entpacken der Downloads in auswählbaren Zielordner auf Server
		Installieren des OXID-Shops
		Download von Modulen in Shops
		Entpacken der Module in Modul-Ordner des Shops

 *	@changelog
		04.03.2015			Installationsaufgaben fertig; Ausgabe verbessert
		03.03.2015			Einstellungen in oxconfig
		02.03.2015			unrar
		27.02.2015			weitere OXID Installations-Aufgaben
		26.02.2015			Installation für Module
							PE/EE-Versionen abfragen mit htaccess-Zugang
		25.02.2015			Installation OXID
		24.02.2015			Downloaden und entpacken
		23.02.2015			neue Version 2.0.0
		08.02.2013			Version 1.0.0

 *	@todo
	OK	download: htaccess für PE/EE - Downloadurl setzen; download mit auth.
	OK	install: shop url setzen
	OK	install: create db mit mysql root-zugang falls gesetzt
	OK	install: shop admin eintragen
	OK	install: utf8-zeug
	OK	install: htaccess-update
	OK	unrar bei pe/ee
	OK	unrar archiv passwort nicht fix	-> default setting
	OK	install: rechte setzen
	OK	install db: create user (wie angegeben), falls nicht existiert
		fehlerabfragen!!
	OK	'connection with the oxid server'/'check for updates'/'your market'/'main delivery country'
	OK	'nur ein land aktiv'
	OK	Links zu neuem Shop und Admin ausgeben
		install EE ohne Lizenz?!
		getFilesize bei PE/EE (get_headers)
		
 */


// ------------------------------------------------------------------------------------------------
// Aufrufe auf bestimmte IPs einschränken
#$a_ip_whitelist								= array( "xxx.xxx.xxx.xxx" );
$a_ip_whitelist									= false;
if ( $a_ip_whitelist ) {
if ( !is_array( $a_ip_whitelist ) )
	$a_ip_whitelist								= array( $a_ip_whitelist );
( isset( $_SERVER[ "REMOTE_ADDR" ] ) ) ? $s_clientIP = $_SERVER[ "REMOTE_ADDR" ] : $s_clientIP = false;
if ( $s_clientIP ) {
	if ( !in_array( $s_clientIP, $a_ip_whitelist ) )
		die;
} else
	die;
}
// ------------------------------------------------------------------------------------------------



error_reporting( E_ALL );


#var_dump( $_GET );	echo "<hr>\n";
#var_dump( $_POST );	echo "<hr>\n";



// ------------------------------------------------------------------------------------------------
// OXID Installations-Defaultwerte setzen
$o_oxid_install									= new oxid_install();
$o_oxid_install->initDefaults();
$a_defaultValues								= array();
$a_defaultValues[ "htaccess_user_pe" ]			= null;
$a_defaultValues[ "htaccess_pass_pe" ]			= null;
$a_defaultValues[ "htaccess_user_ee" ]			= null;
$a_defaultValues[ "htaccess_pass_ee" ]			= null;
$a_defaultValues[ "languages" ]					= "de";
$a_defaultValues[ "location_countries" ]		= "de";
$a_defaultValues[ "countries" ]					= "a7c40f631fc920687.20179984";
$a_defaultValues[ "database_hostname" ]			= "localhost";
$a_defaultValues[ "database_dbname" ]			= null;
$a_defaultValues[ "database_dbuser" ]			= null;
$a_defaultValues[ "database_dbpass" ]			= null;
$a_defaultValues[ "database_utf8" ]				= true;
$a_defaultValues[ "database_root_user" ]		= "root";
$a_defaultValues[ "database_root_pass" ]		= null;
$a_defaultValues[ "demodata" ]					= null;
$a_defaultValues[ "check_updates" ]				= true;
$a_defaultValues[ "connect_oxidserver" ]		= null;
$a_defaultValues[ "shop_url" ]					= "http://myshop.com/";
$a_defaultValues[ "admin_username" ]			= null;
$a_defaultValues[ "admin_password" ]			= null;
$a_defaultValues[ "archive_password" ]			= null;
$a_defaultValues[ "custom_output_at_end" ]		= "custom text";
$o_oxid_install->setDefaults( $a_defaultValues );
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// OXID Download starten
if ( isset( $_GET[ "downloadOXID" ] ) && ( $_GET[ "downloadOXID" ] == 1 ) ) {

	$o_oxid_download							= new oxid_download();
	$o_oxid_download->initializeDownload();
	$o_oxid_download->startDownload();
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// OXID Download-Dateigröße ermitteln
if ( isset( $_GET[ "filesize_downloadOXID" ] ) && ( $_GET[ "filesize_downloadOXID" ] == 1 ) ) {

	$o_oxid_download							= new oxid_download();
	$o_oxid_download->initializeDownload();
	echo json_encode( $o_oxid_download->checkFileSize() );
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// OXID Dateigröße auf Remoteserver ermitteln
if ( isset( $_GET[ "getfilesize_downloadOXID" ] ) && ( $_GET[ "getfilesize_downloadOXID" ] == 1 ) ) {

	$o_oxid_download							= new oxid_download();
	$o_oxid_download->initializeDownload();
	echo json_encode( $o_oxid_download->getFileSize() );
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// OXID entpacken
if ( isset( $_GET[ "unzipOXID" ] ) && ( $_GET[ "unzipOXID" ] == 1 ) ) {

	$o_oxid_download							= new oxid_download();
	$o_oxid_download->initializeDownload();
	$o_oxid_download->unzipDownload();
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// OXID installieren
if ( isset( $_GET[ "installOXID" ] ) && ( $_GET[ "installOXID" ] == 1 ) ) {

	$o_oxid_install								= new oxid_install();
	$o_oxid_install->installOXID();
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// Module installieren
if ( isset( $_GET[ "installModule_ioly" ] ) && ( $_GET[ "installModule_ioly" ] == 1 ) ) {

	$o_module_download							= new module_download();
	$o_module_download->downloadModule_ioly();

	$o_module_install							= new module_install();
	$o_module_install->installModule_ioly();
}
// ------------------------------------------------------------------------------------------------



// ------------------------------------------------------------------------------------------------
// Shop-Links zurückgeben
if ( isset( $_GET[ "getShopLinks" ] ) && ( $_GET[ "getShopLinks" ] == 1 ) ) {

	$o_oxid_install								= new oxid_install();
	
	$a_ret										= $o_oxid_install->get_shop_links();
	$a_ret[ "custom_output" ]					= null;
	if ( $a_defaultValues[ "custom_output_at_end" ] )
		$a_ret[ "custom_output" ]				= $a_defaultValues[ "custom_output_at_end" ];
	echo json_encode( $a_ret );
}
// ------------------------------------------------------------------------------------------------



// ------------------------------------------------------------------------------------------------
// OXID-PE/-EE -Versionen ermitteln
if ( isset( $_GET[ "get_OXIDversions" ] ) && ( $_GET[ "get_OXIDversions" ] == 1 ) ) {

	$o_oxid_versions							= new oxid_versions();

	if ( $_GET[ "OXID_edition" ] == "pe" ) {
		$s_htaccess_user						= $_POST[ "htaccess_user_pe" ];
		$s_htaccess_pass						= $_POST[ "htaccess_pass_pe" ];
	} else if ( $_GET[ "OXID_edition" ] == "ee" ) {
		$s_htaccess_user						= $_POST[ "htaccess_user_ee" ];
		$s_htaccess_pass						= $_POST[ "htaccess_pass_ee" ];
	}

	echo json_encode( $o_oxid_versions->getVersions_from_OXIDsupportPage( $_GET[ "OXID_edition" ], $s_htaccess_user, $s_htaccess_pass ) );
}


// ------------------------------------------------------------------------------------------------
// Ausgabe
if ( !isset( $_GET[ "b_ajaxMode" ] ) ) {

	ini_set( "display_errors", 1 );

	$o_a4p_installer_output						= new a4p_installer_output();

	$o_a4p_installer_output->showHead();
	$o_a4p_installer_output->showMain( $o_oxid_install );
	$o_a4p_installer_output->showFoot();
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_installer_output {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	protected $s_title							= "apps4print - Installer";

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function showMain( $o_oxid_install ) {


		$o_oxidVersions							= new oxid_versions();

?>

<form id='a4p_form_install_OXID' name='a4p_form_install_OXID' method='POST' style='display: inline;'>

	<table style='width: 100%;'>


		<tr>
			<td colspan='2' class="a4p_logo">
				<img class="a4p_logo" src='http://www.apps4print.com/images/apps4print_logo.png'>
			</td>
		</tr>


		<tr>
			<td colspan='2'><br>
			<b>Installer for OXID</b>
			<hr></td>
		</tr>

<?php
	// ------------------------------------------------------------------------------------------------
	// OXID-Versionen (CE/PE/EE)

	$a_OXID_latestCE_Version					= $o_oxidVersions->get_OXID_latestCE_Version();

	$s_edition_checked							= "ce";
	if ( isset( $_POST[ "oxid_version" ] ) )
		$s_edition_checked						= $_POST[ "oxid_version" ];

	$s_version_ce_checked						= false;
	if ( isset( $_POST[ "ce_version" ] ) )
		$s_version_ce_checked					= $_POST[ "ce_version" ];



	// ------------------------------------------------------------------------------------------------
	// bereits gesetzte Werte holen und als default speichern
	$a_installOXID_defaults						= $o_oxid_install->getDefaults();
	$a_post_variables							= array( "oxid_version", "ce_version", "htaccess_user_pe", "htaccess_pass_pe", "htaccess_user_ee", "htaccess_pass_ee" );
	foreach( $a_post_variables as $i_key => $s_var ) {
		if ( isset( $_POST[ $s_var ] ) )
			$a_installOXID_defaults[ $s_var ]	= $_POST[ $s_var ];
	}
	// ------------------------------------------------------------------------------------------------


?>
	<tr>
			<td>OXID Version:</td>
			<td>
				<input type='radio' name='oxid_version' id='oxid_ce' value='ce'<?php if ( $s_edition_checked === "ce" ) echo " checked=\"checked\""; ?>>
				<label for='oxid_ce'>CE <font class='hint'>( latest: <?php echo $a_OXID_latestCE_Version[ "version" ]; ?> )</font></label>

				<br>

				<input type='radio' name='oxid_version'	id='oxid_ce_other' value='ce_other'<?php if ( $s_edition_checked === "ce_other" ) echo " checked=\"checked\""; ?>>
				<label for='oxid_ce_other'>CE <font class='hint'>( other version )</font>:</label>
				<select id='ce_version' name='ce_version' data-oxid_edition='ce_other'>
<?php
				// ------------------------------------------------------------------------------------------------
				// neueste Version oben
				$a_OXID_CE_Versions_reverse = array_reverse( $o_oxidVersions->a_OXID_CE_Versions, true );
				foreach ( $a_OXID_CE_Versions_reverse as $s_version => $s_revision ) {
					echo "\t\t\t<option value='" . $s_version . "'";
					if ( $s_version_ce_checked === $s_version )
						echo " selected=\"selected\"";
					echo ">" . $s_version . "</option>\n";
				}
?>
				</select>

				<br>

				<input type='radio' name='oxid_version' id='oxid_pe' value='pe'<?php if ( $s_edition_checked === "pe" ) echo " checked=\"checked\""; ?>>
				<label for='oxid_pe'>PE <font class='hint'>( license required )</font>:</label>
				<select id='pe_version' name='pe_version' data-oxid_edition='pe'></select>
				<input type='text' id='htaccess_user_pe' name='htaccess_user_pe' value='<?php echo $a_installOXID_defaults[ "htaccess_user_pe" ]; ?>' class='light' placeholder='htaccess username'>
				<input type='password' id='htaccess_pass_pe' name='htaccess_pass_pe' value='<?php echo $a_installOXID_defaults[ "htaccess_pass_pe" ]; ?>' class='light' placeholder='htaccess password'>

				<br>

				<input type='radio' name='oxid_version' id='oxid_ee' value='ee'<?php if ( $s_edition_checked === "ee" ) echo " checked=\"checked\""; ?>>
				<label for='oxid_ee'>EE <font class='hint'>( license required )</font>:</label>
				<select id='ee_version' name='ee_version' data-oxid_edition='ee'></select>
				<input type='text' id='htaccess_user_ee' name='htaccess_user_ee' value='<?php echo $a_installOXID_defaults[ "htaccess_user_ee" ]; ?>' class='light' placeholder='htaccess username'>
				<input type='password' id='htaccess_pass_ee' name='htaccess_pass_ee' value='<?php echo $a_installOXID_defaults[ "htaccess_pass_ee" ]; ?>' class='light' placeholder='htaccess password'>

			</td>

		</tr>

		<tr>
			<td colspan='2'><br></td>
		</tr>

		<tr>
			<td>Target Folder:</td>
			<td>
<?php

	// ------------------------------------------------------------------------------------------------
	// Zielordner

	if ( isset( $_POST[ "targetfolder" ] ) )
		$s_curFolder							= $_POST[ "targetfolder" ];
	else
		$s_curFolder							= false;

	if ( isset( $_POST[ "subfolder" ] ) && ( $_POST[ "subfolder" ] != "" ) )
		$s_curFolder							.= "/" . $_POST[ "subfolder" ];

	$a_targetFolders							= $this->getTargetFolders( $s_curFolder );
	// ------------------------------------------------------------------------------------------------

?>

		<table>
			<tr><td style='vertical-align: top;'>
			<select id='targetfolder' name='targetfolder' onChange='document.a4p_form_install_OXID.submit()'>
<?php
			foreach( $a_targetFolders as $key => $s_folder ) {
				echo "\t\t\t<option value='" . urldecode( $s_folder ) . "'";
				if ( $s_folder === $s_curFolder )
					echo " selected";
				echo ">" . $s_folder . "</option>\n";
			}
?>
			</select>
			&nbsp;/&nbsp;
		</td>
		<td>
<?php

	// ------------------------------------------------------------------------------------------------
	// Unterordner

	$a_subFolders								= $this->getSubfolders( $s_curFolder );

	if ( isset( $_POST[ "subfolder" ] ) )
		$s_curSubFolder							= $_POST[ "subfolder" ];
	else
		$s_curSubFolder							= false;
	// ------------------------------------------------------------------------------------------------

?>

			<select id='subfolder' name='subfolder' onChange='document.a4p_form_install_OXID.submit()'>
				<option></option>
<?php
			foreach( $a_subFolders as $key => $s_folder ) {
				echo "\t\t\t<option value='" . urldecode( $s_folder ) . "'";
				if ( $s_folder === $s_curSubFolder )
					echo " selected";
				echo ">" . $s_folder . "</option>\n";
			}
?>
				</select>
				<br>
				<input type='text' id='new_subfolder' name='new_subfolder' value='' size='30' placeholder='subfolder'>
				<font class='hint'>( create new subfolder )</font>
			</td>
		</tr>
	</table>





			</td>
		</tr>


		<tr>
			<td>Extract file:</td>
			<td>
<?php

	// ------------------------------------------------------------------------------------------------
	// unzip

	if ( isset( $_POST[ "hidden__unzip_file" ] ) )
		$i_unzip							= $_POST[ "hidden__unzip_file" ];
	else
		$i_unzip							= 1;

	if ( $i_unzip )
		$b_unzip							= true;
	else
		$b_unzip							= false;
	// ------------------------------------------------------------------------------------------------

?>
				<input type='hidden' id='hidden__unzip_file' name='hidden__unzip_file' value='<?php echo $i_unzip; ?>'>

				<input type='checkbox' id='unzip_file' name='unzip_file' value='1'<?php if ( $b_unzip ) echo " checked='checked'"; ?>>
				<label for='unzip_file'>unzip</label>
			</td>
		</tr>


<?php

	$this->_showMain__installOXID( $o_oxid_install );


	$this->_showMain__installModules();

?>

		<tr>
			<td colspan='2'><br></td>
		</tr>

		<tr>
			<td colspan='2'>
				<button type='button' id='btn_start_inst_OXID' name='btn_start_inst_OXID' value='install_OXID'>START</button> Download OXID version and unzip to target folder
			</td>
		</tr>

		<tr>
			<td colspan='2'>
				<br>
				<hr>
				<br>
			</td>
		</tr>

		</form>

		<tr>
			<td colspan='2'>
				<div id='a4p_status_install_OXID' class='a4p_status'></div>
				<hr>
			</td>
		</tr>

	</table>

</form>

<?php

	}

	// ------------------------------------------------------------------------------------------------

	protected function _showMain__installOXID( $o_oxid_install ) {


		#$o_oxid_install						= new oxid_install();

		$a_shopLanguages						= $o_oxid_install->getShopLanguages();

		$a_installOXID_defaults					= $o_oxid_install->getDefaults();

		foreach ( $a_installOXID_defaults as $key => $val ) {

			if ( isset( $_POST[ "install_OXID__" . $key ] ) )
				$a_installOXID_defaults[ $key ]	= $_POST[ "install_OXID__" . $key ];

		}

		#var_dump( $a_installOXID_defaults );


?>

		<tr>
			<td>Install OXID:</td>
			<td>


			
			
				<input type='hidden' id='install_OXID__archive_password' name='install_OXID__archive_password' value='<?php echo $a_installOXID_defaults[ "archive_password" ]; ?>'>
			

<?php
	// ------------------------------------------------------------------------------------------------
	// install-checkbox
	if ( isset( $_POST[ "hidden__install_OXID" ] ) )
		$i_install							= $_POST[ "hidden__install_OXID" ];
	else
		$i_install							= 1;

	( $i_install ) ? $b_install = true : $b_install = false;
	// ------------------------------------------------------------------------------------------------
?>
				<input type='hidden' id='hidden__install_OXID' name='hidden__install_OXID' value='<?php echo $i_install; ?>'>
				<input type='checkbox' id='install_OXID' name='install_OXID' value='1'<?php if ( $b_install ) echo " checked='checked'"; ?>>
				<label for='install_OXID'>install</label>
				<br>


				<label for="install_OXID__languages">Shop language:</label>
				<select id="install_OXID__languages" name="install_OXID__languages">
					<option></option>
					<?php
					foreach( $a_shopLanguages[ "aLanguages" ] as $s_lang => $s_lang_name ) {
						echo "<option value='" . $s_lang . "'";
						if ( $a_installOXID_defaults[ "languages" ] === $s_lang )
							echo " selected='selected'";
						echo ">" . $s_lang_name . "</option>\n";
					}
					?>
				</select>

				<br>


				<label for="">Your market:</label>
				<select id="install_OXID__location_countries" name="install_OXID__location_countries">
					<option></option>
					<?php
					foreach( $a_shopLanguages[ "aLocationCountries" ] as $oxid => $s_lang ) {
						echo "<option value='" . $oxid . "'";
						if ( $a_installOXID_defaults[ "location_countries" ] === $oxid )
							echo " selected='selected'";
						echo ">" . $s_lang . "</option>\n";
					}
					?>
				</select>

				<br>

				<label for="">Main delivery country:</label>
				<select id="install_OXID__countries" name="install_OXID__countries">
					<option></option>
					<?php
					foreach( $a_shopLanguages[ "aCountries" ] as $oxid => $s_lang ) {
						echo "<option value='" . $oxid . "'";
						if ( $a_installOXID_defaults[ "countries" ] === $oxid )
							echo " selected='selected'";
						echo ">" . $s_lang . "</option>\n";
					}
					?>
				</select>
				<br>



<?php
	// ------------------------------------------------------------------------------------------------
	// checkbox-Wert für connect_oxidserver und check_updates merken
	if ( isset( $_POST[ "hidden__install_OXID__connect_oxidserver" ] ) )
		$i_hidden__install_OXID__connect_oxidserver	= $_POST[ "hidden__install_OXID__connect_oxidserver" ];
	else
		$i_hidden__install_OXID__connect_oxidserver	= 0;
	( $i_hidden__install_OXID__connect_oxidserver ) ? $b_hidden__install_OXID__connect_oxidserver = true : $b_hidden__install_OXID__connect_oxidserver = false;

	if ( isset( $_POST[ "hidden__install_OXID__check_updates" ] ) )
		$i_hidden__install_OXID__check_updates	= $_POST[ "hidden__install_OXID__check_updates" ];
	else
		$i_hidden__install_OXID__check_updates	= 1;
	( $i_hidden__install_OXID__check_updates ) ? $b_hidden__install_OXID__check_updates = true : $b_hidden__install_OXID__check_updates = false;

	// ------------------------------------------------------------------------------------------------
?>

				<label for="install_OXID__connect_oxidserver">Enable connection with the OXID servers:</label>
				<input type='hidden' id='hidden__install_OXID__connect_oxidserver' name='hidden__install_OXID__connect_oxidserver' value='<?php echo $i_hidden__install_OXID__connect_oxidserver; ?>'>
				<input type="checkbox" id="install_OXID__connect_oxidserver" name="install_OXID__connect_oxidserver"
				 value="1"<?php if ( $b_hidden__install_OXID__connect_oxidserver ) echo " checked='checked'"; ?>>
				<br>


				<label for="install_OXID__check_updates">Check for available updates regularly:</label>
				<input type='hidden' id='hidden__install_OXID__check_updates' name='hidden__install_OXID__check_updates' value='<?php echo $i_hidden__install_OXID__check_updates; ?>'>
				<input type="checkbox" id="install_OXID__check_updates" name="install_OXID__check_updates"
				 value="1"<?php if ( $b_hidden__install_OXID__check_updates ) echo " checked='checked'"; ?>>
				<br>


				<label>Database:</label>
				<input type="text" id="install_OXID__database_hostname" name="install_OXID__database_hostname"
				 value="<?php echo $a_installOXID_defaults[ "database_hostname" ]; ?>" placeholder="Database hostname or IP">
				<input type="text" id="install_OXID__database_dbname" name="install_OXID__database_dbname"
				 value="<?php echo $a_installOXID_defaults[ "database_dbname" ]; ?>" placeholder="Database name">
				<input type="text" id="install_OXID__database_dbuser" name="install_OXID__database_dbuser"
				 value="<?php echo $a_installOXID_defaults[ "database_dbuser" ]; ?>" maxlength='14' placeholder="Database username">
				<input type="password" id="install_OXID__database_dbpass" name="install_OXID__database_dbpass"
				 value="<?php echo $a_installOXID_defaults[ "database_dbpass" ]; ?>" placeholder="Database password">
				<br>



				<label>Database root-user:</label>
				<input type="text" id="install_OXID__database_root_user" name="install_OXID__database_root_user"
				 value="<?php echo $a_installOXID_defaults[ "database_root_user" ]; ?>" placeholder="Database root user">
				<input type="password" id="install_OXID__database_root_pass" name="install_OXID__database_root_pass"
				 value="<?php echo $a_installOXID_defaults[ "database_root_pass" ]; ?>" placeholder="Database root password">
				<font class='hint'>( for create database / create database user )</font>
				<br>


<?php
	// ------------------------------------------------------------------------------------------------
	// checkbox-Wert für utf8 und demodata merken
	if ( isset( $_POST[ "hidden__install_OXID__database_utf8" ] ) )
		$i_hidden__install_OXID__database_utf8	= $_POST[ "hidden__install_OXID__database_utf8" ];
	else
		$i_hidden__install_OXID__database_utf8	= 1;
	( $i_hidden__install_OXID__database_utf8 ) ? $b_hidden__install_OXID__database_utf8 = true : $b_hidden__install_OXID__database_utf8 = false;

	if ( isset( $_POST[ "hidden__install_OXID__demodata" ] ) )
		$i_hidden__install_OXID__demodata	= $_POST[ "hidden__install_OXID__demodata" ];
	else
		$i_hidden__install_OXID__demodata	= 1;
	( $i_hidden__install_OXID__demodata ) ? $b_hidden__install_OXID__demodata = true : $b_hidden__install_OXID__demodata = false;

	// ------------------------------------------------------------------------------------------------
?>

				<label for="install_OXID__database_utf8">UTF-8 character encoding:</label>
				<input type='hidden' id='hidden__install_OXID__database_utf8' name='hidden__install_OXID__database_utf8' value='<?php echo $i_hidden__install_OXID__database_utf8; ?>'>
				<input type="checkbox" id="install_OXID__database_utf8" name="install_OXID__database_utf8" value=""<?php if ($b_hidden__install_OXID__database_utf8 ) echo " checked='checked'"; ?>>
				<br>
				<label for="install_OXID__demodata">Install demodata:</label>
				<input type='hidden' id='hidden__install_OXID__demodata' name='hidden__install_OXID__demodata' value='<?php echo $i_hidden__install_OXID__demodata; ?>'>
				<input type="checkbox" id="install_OXID__demodata" name="install_OXID__demodata" value="1"<?php if ( $b_hidden__install_OXID__demodata ) echo " checked='checked'"; ?>>
				<br>


				<label>Shop url:</label>
				<input type="text" id="" name="install_OXID__shop_url" value="<?php echo $a_installOXID_defaults[ "shop_url" ]; ?>" placeholder="http://">
				<br>



				<label>OXID Admin:</label>
				<input type="text" id="" name="install_OXID__admin_username" value="<?php echo $a_installOXID_defaults[ "admin_username" ]; ?>" placeholder="username">
				<input type="password" id="install_OXID__admin_password" name="install_OXID__admin_password"
				 value="<?php echo $a_installOXID_defaults[ "admin_password" ]; ?>" placeholder="password">
				<br>


			</td>
		</tr>


<?php
	}

	// ------------------------------------------------------------------------------------------------

	protected function _showMain__installModules() {
?>
		<tr>
			<td>
				Install Modules:
			</td>
			<td>


<?php
	// ------------------------------------------------------------------------------------------------
	// install-IOLY-checkbox
	if ( isset( $_POST[ "hidden__install_Module_ioly" ] ) )
		$i_install_Module_ioly				= $_POST[ "hidden__install_Module_ioly" ];
	else
		$i_install_Module_ioly				= 1;

	( $i_install_Module_ioly ) ? $b_install_Module_ioly = true : $b_install_Module_ioly = false;
	// ------------------------------------------------------------------------------------------------
?>
				<input type='hidden' id='hidden__install_Module_ioly' name='hidden__install_Module_ioly' value='<?php echo $i_install_Module_ioly; ?>'>
				<input type='checkbox' id='install_Module_ioly' name='install_Module_ioly' value='1'<?php if ( $b_install_Module_ioly ) echo " checked='checked'"; ?>>
				<label for='install_Module_ioly'>Ioly</label>
				<br>


			</td>
		</tr>

<?php
	}

	// ------------------------------------------------------------------------------------------------

	public function showHead() {
?>
<!doctype html>
<html>
	<head>
		<title><?php echo $this->s_title; ?></title>
		<?php $this->_showCSS(); ?>
	</head>
	<body>
<?php
	}

	// ------------------------------------------------------------------------------------------------

	protected function _showCSS() {
?>
	<style type='text/css'>

		body {
			font-family:		verdana, arial;
			font-size:			12px;
			padding:			50px;
			padding-top:		0px;
			margin:				50px;
			margin-top:			25px;
		}

		td.a4p_logo {
			vertical-align:		middle;
			text-align:			center;
		}

		label {
			width:				200px;
			display:			inline-block;
		}

		input.light {
			border:				1px solid lightgrey;
		}

		select {
			min-width:			100px;
		}

		.hint {
			color:				grey;
		}

		.foot_infos {
			font-size:			10px; {
		}

		/* --------------------------------------------------------------------------------------------- */
		/*	Setup																						 */
		.a4pInstaller_MainStatusDiv {
			width: 100%;
			height: 100%;
			background-color: whitesmoke;
		}

		.a4pInstaller_CheckTitle {
			font-weight: bold;
		}

		.a4pInstaller_CheckMessage {
			font-family: arial;
		}

		.a4pInstaller_CheckStatus {
			color: green;
			float: right;
		}
		/* --------------------------------------------------------------------------------------------- */

		/* --------------------------------------------------------------------------------------------- */

	</style>
<?php
	}

	// ------------------------------------------------------------------------------------------------

	protected function _showFoot_infos() {

		$this->_showFoot_scripts();

?>
		<br><hr><table class='foot_infos'>
		<tr><td>remote server:</td><td><?php echo $this->_getServerIP(); ?></td></tr>
		<tr><td>remote user:</td><td><?php echo `whoami`; ?></td></tr>
		</table>
		<hr>

<?php
	}

	protected function _showFoot_scripts() {

?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

<script type='text/javascript'>
<!--

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	var timer_filesize_OXID						= null;

	var b_get_filesize_OXID						= true;

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	$( document ).ready( function() {


		// ------------------------------------------------------------------------------------------------
		// start install OXID
		$( "body" ).on( "click", "#btn_start_inst_OXID", function() {

			start_download_OXID();
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// unzip-checkbox Wert merken
		$( "body" ).on( "change", "#unzip_file", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__unzip_file" ).val( 1 );
			else
				$( "#hidden__unzip_file" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// install-checkbox Wert merken
		$( "body" ).on( "change", "#install_OXID", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__install_OXID" ).val( 1 );
			else
				$( "#hidden__install_OXID" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// connect-oxid-checkbox Wert merken
		$( "body" ).on( "change", "#install_OXID__connect_oxidserver", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__install_OXID__connect_oxidserver" ).val( 1 );
			else
				$( "#hidden__install_OXID__connect_oxidserver" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// check_updates-checkbox Wert merken
		$( "body" ).on( "change", "#install_OXID__check_updates", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__install_OXID__check_updates" ).val( 1 );
			else
				$( "#hidden__install_OXID__check_updates" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// utf8-checkbox Wert merken
		$( "body" ).on( "change", "#install_OXID__database_utf8", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__install_OXID__database_utf8" ).val( 1 );
			else
				$( "#hidden__install_OXID__database_utf8" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// demodata-checkbox Wert merken
		$( "body" ).on( "change", "#install_OXID__demodata", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__install_OXID__demodata" ).val( 1 );
			else
				$( "#hidden__install_OXID__demodata" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// ioly-checkbox Wert merken
		$( "body" ).on( "change", "#install_Module_ioly", function() {
			if ( $( this ).attr( "checked" ) === "checked" )
				$( "#hidden__install_Module_ioly" ).val( 1 );
			else
				$( "#hidden__install_Module_ioly" ).val( 0 );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// radiobutton für OXID-Edition bei Versionswechsel selektieren
		$( "body" ).on( "change", "#ce_version, #pe_version, #ee_version", function() {

			// alle zurücksetzen
			$( "[name='oxid_version']" ).attr( "checked", "" );

			var this_id							= $( this ).attr( "id" );
			//console.log( "this_id", this_id );

			var s_oxid_edition					= $( this ).data( "oxid_edition" );
			//console.log( "s_oxid_edition", s_oxid_edition );
			
			$( "#oxid_" + s_oxid_edition ).attr( "checked", "checked" );
		});
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// bei Eingaben laden
		$( "body" ).on( "change", "#htaccess_user_pe, #htaccess_pass_pe, #htaccess_user_ee, #htaccess_pass_ee", function() {

			get_OXID_versions();

		});

		// ------------------------------------------------------------------------------------------------
		// bei Seitenaufruf laden
		get_OXID_versions();
		// ------------------------------------------------------------------------------------------------



	});

	// ------------------------------------------------------------------------------------------------

	function start_download_OXID() {

		//console.log( "start_download_OXID()" );

		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&downloadOXID=1";


		// ------------------------------------------------------------------------------------------------
		// Status-Ausgabe
		$( "#a4p_status_install_OXID" ).append( "starting download OXID ...<br>" );
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// Gesamtgröße des Downloads ermitteln
		get_filesize_OXID();
		// ------------------------------------------------------------------------------------------------
		

		// ------------------------------------------------------------------------------------------------
		// Status-Aktualisierung starten
		$( "#a4p_status_install_OXID" ).append( "<div id='status_filesize_OXID'></div>" );

		check_filesize_OXID();
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {


				//console.log( "data", data );


				// ------------------------------------------------------------------------------------------------
				// Status-Aktualisierung beenden
				b_get_filesize_OXID				= false;
				// ------------------------------------------------------------------------------------------------


				// ------------------------------------------------------------------------------------------------
				// Status-Ausgabe
				$( "#a4p_status_install_OXID" ).append( "OK<br>" );
				// ------------------------------------------------------------------------------------------------


				// ------------------------------------------------------------------------------------------------
				// evtl. entpacken
				if ( $( "#hidden__unzip_file" ).val() === "1" ) {

					start_unzip_OXID();
				}
				// ------------------------------------------------------------------------------------------------


			}

		});
		// ------------------------------------------------------------------------------------------------


	}

	// ------------------------------------------------------------------------------------------------

	function get_filesize_OXID() {

		//console.log( "get_filesize_OXID()" );

		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&getfilesize_downloadOXID=1";


		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {

				//console.log( "data", data );


				// ------------------------------------------------------------------------------------------------
				// Ausgabe
				var o_json						= jQuery.parseJSON( data );
				if ( o_json ) {

					$( "#a4p_status_install_OXID" ).append( "to download: " + o_json.s_size + "<br>" );
					//$( "#a4p_status_install_OXID" ).append( data );
					
				}
				// ------------------------------------------------------------------------------------------------

			}

		});

	}
	
	// ------------------------------------------------------------------------------------------------

	function check_filesize_OXID() {

		//console.log( "check_filesize_OXID()" );

		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&filesize_downloadOXID=1";


		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {

				//console.log( "data", data );


				// ------------------------------------------------------------------------------------------------
				// Ausgabe
				var o_json						= jQuery.parseJSON( data );
				if ( o_json ) {

					$( "#status_filesize_OXID" ).html( o_json.s_size );

				}
				// ------------------------------------------------------------------------------------------------


				// ------------------------------------------------------------------------------------------------
				// selbst aufrufen
				if ( b_get_filesize_OXID ) {

					timer_filesize_OXID			= window.setTimeout( "check_filesize_OXID()", 300 );
				}
				// ------------------------------------------------------------------------------------------------


			}

		});

	}

	// ------------------------------------------------------------------------------------------------

	function start_unzip_OXID() {

		//console.log( "start_unzip_OXID()" );


		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&unzipOXID=1";


		// ------------------------------------------------------------------------------------------------
		// Status-Ausgabe
		$( "#a4p_status_install_OXID" ).append( "extracting file ...<br>" );
		// ------------------------------------------------------------------------------------------------



		// ------------------------------------------------------------------------------------------------
		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {


				//console.log( "data", data );


				// ------------------------------------------------------------------------------------------------
				// Status-Ausgabe
				$( "#a4p_status_install_OXID" ).append( "OK<br>" );
				// ------------------------------------------------------------------------------------------------


				// ------------------------------------------------------------------------------------------------
				// evtl. installieren
				if ( $( "#hidden__install_OXID" ).val() === "1" ) {

					start_install_OXID();

				} else {


					// ------------------------------------------------------------------------------------------------
					// evtl. Module installieren
					if ( $( "#hidden__install_Module_ioly" ).val() === "1" ) {

						start_install_Modules();
						
					} else {

						// ------------------------------------------------------------------------------------------------
						// Ausgabe der Shop-Links
						show_shop_links();
						// ------------------------------------------------------------------------------------------------
										
					}
					// ------------------------------------------------------------------------------------------------

				}
				// ------------------------------------------------------------------------------------------------


			}

		});
		// ------------------------------------------------------------------------------------------------


	}

	// ------------------------------------------------------------------------------------------------

	function start_install_OXID() {

		//console.log( "start_install_OXID()" );


		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&installOXID=1";


		// ------------------------------------------------------------------------------------------------
		// Status-Ausgabe
		$( "#a4p_status_install_OXID" ).append( "install OXID ...<br>" );
		// ------------------------------------------------------------------------------------------------



		// ------------------------------------------------------------------------------------------------
		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {


				//console.log( "data", data );


				// ------------------------------------------------------------------------------------------------
				// Status-Ausgabe
				$( "#a4p_status_install_OXID" ).append( "OK<br>" );
				// ------------------------------------------------------------------------------------------------



				// ------------------------------------------------------------------------------------------------
				// evtl. Module installieren
				if ( $( "#hidden__install_Module_ioly" ).val() === "1" ) {

					start_install_Modules();
					
				} else {

					// ------------------------------------------------------------------------------------------------
					// Ausgabe der Shop-Links
					show_shop_links();
					// ------------------------------------------------------------------------------------------------
								
				}
				// ------------------------------------------------------------------------------------------------


			}

		});
		// ------------------------------------------------------------------------------------------------


	}

	// ------------------------------------------------------------------------------------------------

	function start_install_Modules() {

		//console.log( "start_install_Modules()" );


		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&installModule_ioly=1";


		// ------------------------------------------------------------------------------------------------
		// Status-Ausgabe
		$( "#a4p_status_install_OXID" ).append( "install Module ioly ...<br>" );
		// ------------------------------------------------------------------------------------------------



		// ------------------------------------------------------------------------------------------------
		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {

				//console.log( "data", data );

				// ------------------------------------------------------------------------------------------------
				// Status-Ausgabe
				$( "#a4p_status_install_OXID" ).append( "OK<br>" );
				// ------------------------------------------------------------------------------------------------


				// ------------------------------------------------------------------------------------------------
				// Ausgabe der Shop-Links
				show_shop_links();
				// ------------------------------------------------------------------------------------------------
				
			}

		});
		// ------------------------------------------------------------------------------------------------


	}

	// ------------------------------------------------------------------------------------------------

	function show_shop_links() {

		//console.log( "show_shop_links()" );


		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&getShopLinks=1";


		// ------------------------------------------------------------------------------------------------
		$.ajax({

			url: sUrl,

			type: "POST",

			data: $( "#a4p_form_install_OXID" ).serialize(),

			success: function( data ) {

				//console.log( "data", data );

				// ------------------------------------------------------------------------------------------------
				// Status-Ausgabe
				var o_json						= jQuery.parseJSON( data );
				if ( o_json ) {

					var s_link_shop				= "<a href='" + o_json.shop_url + "' target='_new'>" + o_json.shop_url + "</a><br>";
					var s_link_admin			= "<a href='" + o_json.admin_url + "' target='_new'>" + o_json.admin_url + "</a><br>";
					
					$( "#a4p_status_install_OXID" ).append( "Shop: " + s_link_shop );
					$( "#a4p_status_install_OXID" ).append( "Admin: " + s_link_admin );

					$( "#a4p_status_install_OXID" ).append( o_json.custom_output );
					
					
				}
				// ------------------------------------------------------------------------------------------------

			}

		});
		// ------------------------------------------------------------------------------------------------


	}

	// ------------------------------------------------------------------------------------------------

	function get_OXID_versions() {

		//console.log( "get_OXID_versions()" );


		var s_default_pe						= "<?php if ( isset( $_POST[ "pe_version" ] ) ) echo $_POST[ "pe_version" ]; ?>";
		var s_default_ee						= "<?php if ( isset( $_POST[ "ee_version" ] ) ) echo $_POST[ "ee_version" ]; ?>";
		

		_load_OXID_versions( "pe", s_default_pe );
		_load_OXID_versions( "ee", s_default_ee );

	}

	// ------------------------------------------------------------------------------------------------

	function _load_OXID_versions( s_OXID_edition, s_default ) {

		//console.log( "_load_OXID_versions( s_OXID_edition, s_default )", s_OXID_edition, s_default );
		
		var sUrl								= "?b_ajaxMode=1";
			sUrl								+= "&get_OXIDversions=1";
			sUrl								+= "&OXID_edition=" + s_OXID_edition;

		//console.log( "sUrl", sUrl );

		// ------------------------------------------------------------------------------------------------
		$.ajax({

			url: sUrl,
			type: "POST",
			data: $( "#a4p_form_install_OXID" ).serialize(),
			success: function( data ) {

				//console.log( "data", data );

				var o_json						= jQuery.parseJSON( data );
				//console.log( "o_json", o_json );

				var s_dropdown_id				= "#" + s_OXID_edition + "_version";

				$( s_dropdown_id ).html( "" );

				if ( o_json.length > 0 ) {

					// ------------------------------------------------------------------------------------------------
					// JSON-Inhalte als Options setzen
					$.each( o_json, function( key, value ) {

						if ( value === s_default )
							$( s_dropdown_id ).append( $( "<option></option>" ).attr( "value", value ).attr( "selected", "selected" ).text( value ) );
						else
							$( s_dropdown_id ).append( $( "<option></option>" ).attr( "value", value ).text( value ) );
						
					});
				}
			}
		});
		// ------------------------------------------------------------------------------------------------

	}

	// ------------------------------------------------------------------------------------------------

//-->
</script>
<?php
	}

	// ------------------------------------------------------------------------------------------------

	public function showFoot() {

		$this->_showFoot_infos();

?>

	</body>
</html>
<?php
	}

	// ------------------------------------------------------------------------------------------------

	function getTargetFolders( $sCurPath = false ) {


		$a_defaultFolders						= array();

		$vhostDir								= substr( $_SERVER[ "DOCUMENT_ROOT" ], 0, strrpos( $_SERVER[ "DOCUMENT_ROOT" ] , "/" ) );

		array_push( $a_defaultFolders, $vhostDir );
		array_push( $a_defaultFolders, $_SERVER[ "DOCUMENT_ROOT" ] );
		array_push( $a_defaultFolders, getcwd() );

		$a_vhostFolders								= $this->_getFolders( false, true, true );

		$a_targetFolders							= array_merge( $a_defaultFolders, $a_vhostFolders );


		if ( ( $sCurPath !== false ) && !in_array( $sCurPath, $a_targetFolders ) )
			array_push( $a_targetFolders, $sCurPath );

		sort( $a_targetFolders );


		return $a_targetFolders;
	}

	// ------------------------------------------------------------------------------------------------

	public function getSubfolders( $s_curPath = false ) {


		$aSubFolders = $this->_getFolders( $s_curPath, false, true );

		sort( $aSubFolders );

		return $aSubFolders;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _getFolders( $sCurPath = false, $bIncludePath = true, $b_skipTilde = true ) {


		$aFolders								= array();


		// ------------------------------------------------------------------------------------------------
		// vhosts auflisten
		$vhostDir								= substr( $_SERVER[ "DOCUMENT_ROOT" ], 0, strrpos( $_SERVER[ "DOCUMENT_ROOT" ] , "/" ) );

		if ( $sCurPath )
			$startDir							= $sCurPath;
		else
			$startDir							= $vhostDir;


		$dh										= opendir( $startDir );
		if ( $dh ) {
			$file = readdir( $dh );
			while ( $file !== false ) {
				if ( ( $file != "." ) && ( $file != ".." ) && is_dir( $startDir . "/" . $file ) ) {

					if ( !( ( substr( $file, 0, 1 ) === "~" ) && $b_skipTilde ) ) {

						if ( $bIncludePath )
							array_push( $aFolders, $startDir . "/" . $file );
						else
							array_push( $aFolders, $file );

					}

				}
				$file = readdir( $dh );
			}
			closedir( $dh );
		}



		return $aFolders;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _getServerIP() {

		$ret									= false;

		if ( $_SERVER[ "SERVER_NAME" ] === $_SERVER[ "SERVER_ADDR" ] )

			$ret								= gethostbyname( $_SERVER[ "HTTP_HOST" ] );

		else if ( ( $_SERVER[ "HTTP_HOST" ] === $_SERVER[ "SERVER_NAME" ] ) && ( substr( $_SERVER[ "HTTP_HOST" ], 0, 4 ) === "www." ) )

			$ret								= gethostbyname( $_SERVER[ "HTTP_HOST" ] );
		else

			$ret								= $_SERVER[ "SERVER_ADDR" ];

		return $ret;
	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class oxid_versions {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public $s_OXID_latestCE_DownloadUrl			= "http://www.download.oxid-esales.com.server675-han.de-nserver.de/ce/index.php";

	public $s_OXID_otherCE_DownloadUrl			= "http://wiki.oxidforge.org/download/OXID_ESHOP_CE_";

	public $a_OXID_CE_Versions = array (
		"4.0.0.0" => 12759,
		"4.0.0.0" => 12939,
		"4.0.0.0" => 13895,
		"4.0.0.0" => 13934,
		"4.0.0.1" => 14455,
		"4.0.0.2" => 14842,
		"4.0.0.2" => 14967,
		"4.0.1.0" => 15990,
		"4.1.0" => 17976,
		"4.1.1" => 18442,
		"4.1.2" => 18998,
		"4.1.3" => 19918,
		"4.1.4" => 21266,
		"4.1.5" => 21618,
		"4.1.6" => 22740,
		"4.2.0" => 23610,
		"4.3.0" => 26948,
		"4.3.1" => 27257,
		"4.3.2" => 27884,
		"4.4.0" => 28699,
		"4.4.1" => 28950,
		"4.4.2" => 29492,
		"4.4.3" => 30016,
		"4.4.4" => 30554,
		"4.4.5" => 31315,
		"4.4.6" => 32697,
		"4.4.7" => 33396,
		"4.4.8" => 34028,
		"4.5.0" => 34568,
		"4.5.0_beta3" => 33421,
		"4.5.0_beta4" => 34038,
		"4.5.1" => 38045,
		"4.5.11" => 46050,
		"4.5.2" => 38481,
		"4.5.3" => 39087,
		"4.5.4" => 39463,
		"4.5.5" => 40299,
		"4.5.6" => 40808,
		"4.5.7" => 41909,
		"4.5.8" => 42471,
		"4.5.9" => "_43186",
		"4.6.0" => "4.6.0_44406",
		"4.6.0_RC1" => "_43989",
		"4.6.0_RC2" => "_44219",
		"4.6.0_beta2" => "_40632",
		"4.6.1" => "4.6.1_45706",
		"4.6.2" => "4.6.2_46646",
		"4.6.3" => "4.6.3_47975",
		"4.6.4" => "4.6.4_49061",
		"4.6.5" => "4.6.5_49955",
		"4.6.6" => "4.6.6_54646",
		"4.6.7" => "4.6.7_64646",
		"4.6.8" => "4.6.8_88988",
		"4.7.0" => "4.7.0_51243",
		"4.7.1" => "4.7.1_52468",
		"4.7.2" => "4.7.2_53018",
		"4.7.3" => "4.7.3_54408",
		"4.7.4" => "4.7.4_57063",
		"4.7.5" => "",
		"4.7.6" => "4.7.6_40476",
		"4.7.7" => "4.7.7",
		"4.7.8" => "4.7.8",
		"4.7.9" => "4.7.9",
		"4.7.10" => "4.7.10",
		"4.7.11" => "4.7.11",
		"4.7.12" => "4.7.12",
		"4.7.13" => "4.7.13",
		"4.7.14" => "4.7.14",
		"4.8.0" => "4.8.0",
		"4.8.1" => "4.8.1",
		"4.8.2" => "4.8.2",
		"4.8.3" => "4.8.3",
		"4.8.4" => "4.8.4",
		"4.8.5" => "4.8.5",
		"4.8.6" => "4.8.6",
		"4.8.7" => "4.8.7",
		"4.8.8" => "4.8.8",
		"4.8.9" => "4.8.9",
		"4.9.0" => "4.9.0",
		"4.9.1" => "4.9.1",
		"4.9.2" => "4.9.2",
		"4.9.3" => "4.9.3"
	);


	public $s_OXID_PE_downloads_Url			= "https://support.oxid-esales.com/versions/PE/partner/";

	public $s_OXID_EE_downloads_Url			= "https://support.oxid-esales.com/versions/EE/partner/";


	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	/**
	 * 
	 * @return multitype: array|boolean
	 */
	public function get_OXID_latestCE_Version() {


		$ret									= @get_headers( $this->s_OXID_latestCE_DownloadUrl, true );
		if ( $ret !== false ) {

			if ( isset( $ret[ "Content-Disposition" ] ) ) {

				$content						= $ret[ "Content-Disposition" ];

				$aRet							= array();

				$filename						= substr( $content, strpos( $content, "=" ) + 1 );
				$filename						= str_replace( "\"", "", $filename );

				$aRet[ "filename" ]				= $filename;

				$version						= $aRet[ "filename" ];
				$version						= str_replace( "OXID_ESHOP_CE_", "", $version );
				$version						= str_replace( ".zip", "", $version );
				$version						= str_replace( "\"", "", $version );

				$aRet[ "version" ]				= $version;

				return $aRet;

			} else
				return false;

		} else
			return false;
	}

	// ------------------------------------------------------------------------------------------------

	public function getVersions_from_OXIDsupportPage( $s_OXID_edition, $s_htaccess_user, $s_htaccess_pass ) {


		if ( $s_OXID_edition === "pe" )
			$s_url								= $this->s_OXID_PE_downloads_Url;
		else if ( $s_OXID_edition === "ee" )
			$s_url								= $this->s_OXID_EE_downloads_Url;


		$ch										= curl_init( $s_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_USERPWD, $s_htaccess_user . ":" . $s_htaccess_pass );
		$s_ret									= curl_exec( $ch );

		$s_ret									= strip_tags( $s_ret );

		$a_ret									= explode( " - ", $s_ret );


		// ------------------------------------------------------------------------------------------------
		// skip first entry
		$a_versions								= array();
		for( $i = 1; $i < count( $a_ret ) - 1; $i++ ) {

			$s_version							= $a_ret[ $i ];

			$s_version							= trim( $s_version );
			// remove \n
			$s_version							= str_replace( "\n", "", $s_version );
			// remove /
			$s_version							= str_replace( "/", "", $s_version );
			array_push( $a_versions, $s_version );
		}


		$a_versions_rev							= array_reverse( $a_versions );


		return $a_versions_rev;
	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class downloader {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	protected $s_downloadUrl					= null;
	protected $s_targetFolder					= null;
	protected $s_targetFilename					= null;
	
	protected $s_htaccess_user					= null;
	protected $s_htaccess_pass					= null;

	protected $a_messages						= array();

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function setDownloadUrl( $s_downloadUrl ) {

		$this->s_downloadUrl					= $s_downloadUrl;

	}

	// ------------------------------------------------------------------------------------------------

	public function setTargetFolder( $s_targetFolder ) {

		$this->s_targetFolder					= $s_targetFolder;

		if ( $this->s_targetFolder[ strlen( $this->s_targetFolder ) - 1 ] !== DIRECTORY_SEPARATOR )
			$this->s_targetFolder				.= DIRECTORY_SEPARATOR;

		$this->_checkTargetFolder();

	}

	// ------------------------------------------------------------------------------------------------

	protected function _checkTargetFolder() {

		// ------------------------------------------------------------------------------------------------
		// TargetFolder anlegen
		$res									= true;
		if ( !file_exists( $this->s_targetFolder ) || !is_dir( $this->s_targetFolder ) ) {

			$res								= mkdir( $this->s_targetFolder, 0777, true );

			if ( !$res ) {
				array_push( $this->a_messages, "creating folder failed" );
				array_push( $this->a_messages, error_get_last() );
			}
		}

	}

	// ------------------------------------------------------------------------------------------------

	public function setTargetFilename( $s_targetFilename ) {

		$this->s_targetFilename					= $s_targetFilename;

	}

	// ------------------------------------------------------------------------------------------------

	public function setHtaccess( $s_htaccess_user, $s_htaccess_pass ) {

		$this->s_htaccess_user					= $s_htaccess_user;
		$this->s_htaccess_pass					= $s_htaccess_pass;
		
	}

	// ------------------------------------------------------------------------------------------------

	public function downloadFile() {


		if ( $this->s_downloadUrl && $this->s_targetFolder && $this->s_targetFilename ) {


			$this->_checkTargetFolder();



			// ------------------------------------------------------------------------------------------------
			// start curl-download
			$_ret__curl							= $this->_download_curl( $this->s_downloadUrl, $this->s_targetFolder . $this->s_targetFilename, $this->s_htaccess_user, $this->s_htaccess_pass );
			#var_dump( $_ret__curl );
			
			
			// TODO: not working
			#if ( !file_exists( $this->s_targetFilename ) || ( file_exists( $this->s_targetFilename ) && filesize( $this->s_targetFilename ) == 0 ) ) {
			
				// ------------------------------------------------------------------------------------------------
				// start wget-download
			#	$a_ret__wget					= $this->_download_wget( $this->s_downloadUrl, $this->s_targetFolder, $this->s_targetFilename, $this->s_htaccess_user, $this->s_htaccess_pass );
				#var_dump( $a_ret__wget );
			#}


		} else {

			return false;
		}

	}

	// ------------------------------------------------------------------------------------------------

	public function checkFileSize() {


		if ( $this->s_targetFolder && $this->s_targetFilename ) {


			$filename							=  $this->s_targetFolder . DIRECTORY_SEPARATOR . $this->s_targetFilename;


			$i_filesize							= 0;
			if ( file_exists( $filename ) )
				$i_filesize						= filesize( $filename );


			$a_ret								= array();
			$a_ret[ "i_size" ]					= $i_filesize;
			$a_ret[ "s_size" ]					= $this->echo_filesize( $i_filesize );

			return $a_ret;

		} else {

			return false;
		}

	}

	// ------------------------------------------------------------------------------------------------

	public function getFileSize() {


		if ( $this->s_downloadUrl ) {

			#_log( "\$this->s_downloadUrl", $this->s_downloadUrl, __FILE__, __FUNCTION__, __LINE__ );
			
			$a_ret								= get_headers( $this->s_downloadUrl, 1 );
		
			
			if ( isset( $a_ret[ "Content-Length" ] ) ) {
				
				$a_ret[ "formatted_size" ]		= $this->echo_filesize( $a_ret[ "Content-Length" ] );
			}
	
			#_log( "\$a_ret", $a_ret, __FILE__, __FUNCTION__, __LINE__ );

			return $a_ret;

		} else {

			return false;
		}

	}
	
	// ------------------------------------------------------------------------------------------------

	public function unzipFile() {


		$s_zipFileABS							= $this->s_targetFolder . $this->s_targetFilename;

		
		#$s_archivePassword						= "0x1d";
		$s_archivePassword						= $_POST[ "install_OXID__archive_password" ];
		
		
		return $this->_extractFile( $s_zipFileABS, $this->s_targetFolder, $s_archivePassword );
	}

	// ------------------------------------------------------------------------------------------------

	protected function _extractFile( $s_extractFileABS, $s_targetFolder, $s_archivePassword ) {

		
		$b_ret									= false;

		
		if ( file_exists( $s_extractFileABS ) ) {
			
			
			$s_fileext							= strtolower( pathinfo( $s_extractFileABS, PATHINFO_EXTENSION ) );
			
			
			// ZIP
			if ( $s_fileext === "zip" ) {
			
				
				$b_ret							= $this->_unzip_ziparchive( $s_extractFileABS, $s_targetFolder );
				
				if ( !$b_ret ) {
					
					
					$b_ret						= $this->_unzip_cmdline( $s_extractFileABS, $s_targetFolder );
				}
				
				
				
				
			// RAR
			} else if ( $s_fileext === "rar" ) {
				
				
				$b_ret							= $this->_unrar_cmdline( $s_extractFileABS, $s_targetFolder, $s_archivePassword );
				
			}
			
			
		}


		return $b_ret;
	}

	// ------------------------------------------------------------------------------------------------
	
	protected function _unzip_ziparchive( $s_zipFileABS, $s_targetFolder ) {
		
		
		$b_ret									= false;
		
		
		if ( class_exists( "ZipArchive" ) ) {
		
		
			$o_zip								= new ZipArchive;
			
			if ( $o_zip->open( $s_zipFileABS ) === true ) {
			
				$b_ret							= $o_zip->extractTo( $s_targetFolder );
				$o_zip->close();
				
			}
		
		}
		
		
		return $b_ret;
	}

	// ------------------------------------------------------------------------------------------------
	
	protected function _unzip_cmdline( $s_zipFileABS, $s_targetFolder ) {
		
		
		$b_ret									= false;
		
		
		$s_cmd									= "unzip -d " . $s_targetFolder . " " . $s_zipFileABS;
		
		$s_ret									= exec( $s_cmd, $a_output, $i_ret );
		
		
		if ( $i_ret === 0 )
			$b_ret								= true;
		
		
		return $b_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _unrar_cmdline( $s_rarFileABS, $s_targetFolder, $s_archivePassword = null ) {
		
		#  918  unrar x OXID_ESHOP_EE_5.1.0_for_PHP_5.3_SOURCE.rar ./EE_5.1.0
	

		$b_ret									= false;

		
		$s_cmd									= "unrar x " . $s_rarFileABS;

		if ( !is_null( $s_archivePassword ) )
			$s_cmd								.= " p " . $s_archivePassword;
		
		$s_cmd									.= " " . $s_targetFolder;

		#_log( "\$s_cmd", $s_cmd, __FILE__, __FUNCTION__, __LINE__ );
		
		$s_ret									= exec( $s_cmd, $a_output, $i_ret );
		
		
		if ( $i_ret === 0 )
			$b_ret								= true;
		
		
		return $b_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _download_wget( $s_downloadUrl, $s_targetFolder, $s_targetFilename = null, $s_htaccess_user = null, $s_htaccess_pass = null ) {


		$aRet									= array();

		$cmd									= "wget";

		if ( $s_targetFilename )
			$cmd								.= " -O \"" . $s_targetFolder . $s_targetFilename . "\"";
		else
			$cmd								.= " -P \"" . $s_targetFolder . "\"";

		// htaccess
		if ( !is_null( $s_htaccess_user ) && !is_null( $s_htaccess_pass ) )
			$cmd								.= " --user=" . $s_htaccess_user . " --password=" . $s_htaccess_pass;
		
		// quiet
		$cmd									.= " -q";
		$cmd									.= " " . $s_downloadUrl;

		$cmd									.= " 2>&1";

		$res									= exec( $cmd, $out, $ret );

		$aRet[ "CMD" ]							= $cmd;
		$aRet[ "RES" ]							= $res;
		$aRet[ "OUT" ]							= $out;
		$aRet[ "RET" ]							= $ret;

		return $aRet;
	}

	// ------------------------------------------------------------------------------------------------

	/**
	 * 
	 * @param unknown $s_downloadUrl
	 * @param unknown $s_targetFileABS
	 * 
	 * @return true on success | int errorcode on failure
	 */
	protected function _download_curl( $s_downloadUrl, $s_targetFileABS, $s_htaccess_user = null, $s_htaccess_pass = null ) {


		$b_ret									= false;


		$ch										= curl_init( $s_downloadUrl );


		$fp										= fopen( $s_targetFileABS, "wb" );

		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		if ( !is_null( $s_htaccess_user ) && !is_null( $s_htaccess_pass ) )
			curl_setopt( $ch, CURLOPT_USERPWD, $s_htaccess_user . ":" . $s_htaccess_pass );
		
		curl_exec( $ch );


		// Get the HTTP status code.
		$statusCode								= curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		// Close the cURL handler.
		curl_close( $ch );


		// When you are using CURLOPT_FILE to download directly into a file you must close the file handler after the curl_close()
		// otherwise the file will be incomplete and you will not be able to use it until the end of the execution of the php process.
		fclose( $fp );

		if ( $statusCode == 200 ) {
			$b_ret								= true;
		} else{
			$b_ret								= $statusCode;
		}


		return $b_ret;
	}

	// ------------------------------------------------------------------------------------------------

	public function echo_filesize( $size, $target_format = false ) {

		$a = array ( "Byte", "KB", "MB", "GB", "TB", "PB" );

		if ( $target_format ) {
			for( $i = 0, $pos = - 1; $i < count( $a ); $i ++, $pos ++ ) {
				trace( 0, strtoupper( $target_format ), strtoupper( $a[ $i ] ) );
				if ( strtoupper( $target_format ) != strtoupper( $a[ $i ] ) )
					$size /= 1024;
				else
					$i = count( $a );
			}
		} else {

			$pos = 0;
			// while ($size >= 1024) {
			while( $size >= 1000 ) {
				// size /= 1024;
				$size /= 1000;
				$pos ++;
			}
		}


		$s_size									= number_format( $size, 2 );
		#round

		return $s_size . " " . $a[ $pos ];
	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class oxid_download {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	protected $s_downloadUrl					= null;
	protected $s_targetFolder					= null;
	protected $s_targetFilename					= null;

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function initializeDownload() {


		$o_oxid_versions						= new oxid_versions();


		// ------------------------------------------------------------------------------------------------
		// OXID-Edition
		$s_oxid_version							= $_POST[ "oxid_version" ];


		// ------------------------------------------------------------------------------------------------
		// Download-URL
		if ( $s_oxid_version === "ce" ) {

			$this->s_downloadUrl				= $o_oxid_versions->s_OXID_latestCE_DownloadUrl;

		} else if ( $s_oxid_version === "ce_other" ) {

			$this->s_downloadUrl				= $o_oxid_versions->s_OXID_otherCE_DownloadUrl;

			// Version
			$s_ce_version						= $_POST[ "ce_version" ];

			$this->s_downloadUrl				.= $o_oxid_versions->a_OXID_CE_Versions[ $s_ce_version ] . ".zip";
			
		} else if ( $s_oxid_version === "pe" ) {
			
			$s_pe_version						= $_POST[ "pe_version" ];
			
			$this->s_downloadUrl				= $o_oxid_versions->s_OXID_PE_downloads_Url;
			$this->s_downloadUrl				.= $s_pe_version . DIRECTORY_SEPARATOR;
			$this->s_downloadUrl				.= "OXID_ESHOP_PE_" . $s_pe_version;
			$this->s_downloadUrl				.= $this->_get_pe_ee_downloadFilename();
			
		} else if ( $s_oxid_version === "ee" ) {
			
			$s_ee_version						= $_POST[ "ee_version" ];
			
			$this->s_downloadUrl				= $o_oxid_versions->s_OXID_EE_downloads_Url;
			$this->s_downloadUrl				.= $s_ee_version . DIRECTORY_SEPARATOR;
			$this->s_downloadUrl				.= "OXID_ESHOP_EE_" . $s_ee_version;
			$this->s_downloadUrl				.= $this->_get_pe_ee_downloadFilename();
				
		}

		

		// ------------------------------------------------------------------------------------------------
		// local folder
		$this->s_targetFolder					= $_POST[ "targetfolder" ] . DIRECTORY_SEPARATOR;

		if ( $_POST[ "new_subfolder" ] )
			$this->s_targetFolder				.= $_POST[ "new_subfolder" ] . DIRECTORY_SEPARATOR;


		// ------------------------------------------------------------------------------------------------
		// local filename
		$this->s_targetFilename					= "OXID_";
		if ( $s_oxid_version === "ce" ) {

			$a_OXID_latestCE_Version			= $o_oxid_versions->get_OXID_latestCE_Version();
			$this->s_targetFilename				.= "CE_" . $a_OXID_latestCE_Version[ "version" ];
			$this->s_targetFilename				.= ".zip";
				
		} else if ( $s_oxid_version === "ce_other" ) {

			$this->s_targetFilename				.= "CE_" . $s_ce_version;
			$this->s_targetFilename				.= ".zip";
				
		} else if ( $s_oxid_version === "pe" ) {
		
			$s_pe_version						= $_POST[ "pe_version" ];
			$this->s_targetFilename				.= "PE_" . $s_pe_version;
			$this->s_targetFilename				.= ".rar";
				
		} else if ( $s_oxid_version === "ee" ) {
		
			$s_ee_version						= $_POST[ "ee_version" ];
			$this->s_targetFilename				.= "EE_" . $s_ee_version;
			$this->s_targetFilename				.= ".rar";
		}



		// ------------------------------------------------------------------------------------------------
		// debug
		//		
		/*
		echo "s_downloadUrl ";	var_dump( $this->s_downloadUrl );		echo "<hr>\n";
		echo "s_targetFolder ";	var_dump( $this->s_targetFolder );		echo "<hr>\n";
		echo "s_targetFilename ";	var_dump( $this->s_targetFilename );	echo "<hr>\n";
		//*/
		// ------------------------------------------------------------------------------------------------


	}
	
	// ------------------------------------------------------------------------------------------------
	
	/**
	 * 
	 * @return _for_PHP_5.4_SOURCE.rar or _for_PHP_5.3_SOURCE.rar or _SOURCE.rar
	 * 
	 */
	protected function _get_pe_ee_downloadFilename() {
		
		
		#OXID_ESHOP_EE_5.0.9_for_PHP_5.3_SOURCE.rar
		
		
		$s_filename								= "";
		
		
		$s_php_version_oxid						= false;
		if ( substr( phpversion(), 0, 3 ) == "5.3" )
			$s_php_version_oxid					= "5.3";
		else if ( substr( phpversion(), 0, 3 ) == "5.4" )
			$s_php_version_oxid					= "5.4";
		
		if ( $s_php_version_oxid)
			$s_filename							.= "_for_PHP_" . $s_php_version_oxid;
		
		$s_filename								.= "_SOURCE.rar"; 
		
		return $s_filename;
	}

	// ------------------------------------------------------------------------------------------------

	public function startDownload() {

		$o_downloader							= new downloader();
		$o_downloader->setTargetFolder( $this->s_targetFolder );
		$o_downloader->setDownloadUrl( $this->s_downloadUrl );
		$o_downloader->setTargetFilename( $this->s_targetFilename );
		
		$s_oxid_version							= $_POST[ "oxid_version" ];
		if ( ( $s_oxid_version === "pe" ) || ( $s_oxid_version === "ee" ) ) {
			
			if ( isset( $_POST[ "htaccess_user_" . $s_oxid_version ] ) && isset( $_POST[ "htaccess_user_" . $s_oxid_version ] ) ) {
				
				$s_htaccess_user				= $_POST[ "htaccess_user_" . $s_oxid_version ];
				$s_htaccess_pass				= $_POST[ "htaccess_pass_" . $s_oxid_version ];
				
				$o_downloader->setHtaccess( $s_htaccess_user, $s_htaccess_pass );
			}
		}
		
		$o_downloader->downloadFile();


	}

	// ------------------------------------------------------------------------------------------------

	public function checkFileSize() {

		$o_downloader							= new downloader();
		$o_downloader->setTargetFolder( $this->s_targetFolder );
		$o_downloader->setDownloadUrl( $this->s_downloadUrl );
		$o_downloader->setTargetFilename( $this->s_targetFilename );
		return $o_downloader->checkFileSize();

	}

	// ------------------------------------------------------------------------------------------------

	public function getFileSize() {

		$o_downloader							= new downloader();
		$o_downloader->setDownloadUrl( $this->s_downloadUrl );
		
		#Content-Length":"63311181"
				
		$a_ret									= $o_downloader->getFileSize();
		if ( isset( $a_ret[ "formatted_size" ] ) ) {
			
			return array( "s_size" => $a_ret[ "formatted_size" ] );
		}
			
	}

	// ------------------------------------------------------------------------------------------------

	public function unzipDownload() {

		$o_downloader							= new downloader();
		$o_downloader->setTargetFolder( $this->s_targetFolder );
		$o_downloader->setDownloadUrl( $this->s_downloadUrl );
		$o_downloader->setTargetFilename( $this->s_targetFilename );
		$o_downloader->unzipFile();

	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class oxid_install {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	protected $a_defaultValues					= null;

	protected $s_shopInstallDir					= null;

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function initDefaults() {


		$this->a_defaultValues							= array();

		$this->a_defaultValues[ "languages" ]			= null;
		$this->a_defaultValues[ "location_countries" ]	= null;
		$this->a_defaultValues[ "countries" ]			= null;

		$this->a_defaultValues[ "database_hostname" ]	= null;
		$this->a_defaultValues[ "database_dbname" ]		= null;
		$this->a_defaultValues[ "database_dbuser" ]		= null;
		$this->a_defaultValues[ "database_dbpass" ]		= null;
		$this->a_defaultValues[ "database_utf8" ]		= null;

		$this->a_defaultValues[ "database_root_user" ]	= null;
		$this->a_defaultValues[ "database_root_pass" ]	= null;

		$this->a_defaultValues[ "demodata" ]			= null;
		$this->a_defaultValues[ "check_updates" ]		= null;
		$this->a_defaultValues[ "connect_oxidserver" ]	= null;

		$this->a_defaultValues[ "admin_username" ]		= null;
		$this->a_defaultValues[ "admin_password" ]		= null;

		$this->a_defaultValues[ "custom_output_at_end" ]= null;

	}

	// ------------------------------------------------------------------------------------------------

	public function getDefaults() {


		if ( is_null( $this->a_defaultValues ) )
			$this->initDefaults();


		return $this->a_defaultValues;
	}

	// ------------------------------------------------------------------------------------------------

	public function setDefaults( $a_newDefaults ) {


		$this->a_defaultValues					= array_merge( $this->a_defaultValues, $a_newDefaults );

	}

	// ------------------------------------------------------------------------------------------------

	public function setDefault( $s_name, $s_value ) {


		$this->a_defaultValues[ $s_name ]		= $s_value;

	}

	// ------------------------------------------------------------------------------------------------

	public function getShopLanguages() {


		//shop location countries - used when loading dynamic content from oxid servers
		$aLanguages = array(

			'en' => 'English',
			'de' => 'Deutsch',

		);

		$aLocationCountries['en'] = array(

			'de' => 'Germany, Austria, Switzerland',
			'en' => 'Any other',

		);

		$aLocationCountries['de'] = array(

			'de' => 'Deutschland, &Ouml;sterreich, Schweiz',
			'en' => 'Andere Region',

		);


		$aCountries['en'] = array(

			"a7c40f6320aeb2ec2.72885259" => "Austria",
			"a7c40f63272a57296.32117580" => "France",
			"a7c40f631fc920687.20179984" => "Germany",
			"a7c40f632a0804ab5.18804076" => "United Kingdom",
			"8f241f11096877ac0.98748826" => "United States",
			"a7c40f6321c6f6109.43859248" => "Switzerland",

		);

		$aCountries['de'] = array(

			"a7c40f6320aeb2ec2.72885259" => "Österreich",
			"a7c40f63272a57296.32117580" => "Frankreich",
			"a7c40f631fc920687.20179984" => "Deutschland",
			"a7c40f6321c6f6109.43859248" => "Schweiz",
			"a7c40f632a0804ab5.18804076" => "Vereinigtes Königreich",
			"8f241f11096877ac0.98748826" => "Vereinigte Staaten von Amerika",

		);


		$a_ret									= array();
		$a_ret[ "aLanguages" ]					= $aLanguages;
		$a_ret[ "aLocationCountries" ]			= $aLocationCountries[ "en" ];
		$a_ret[ "aCountries" ]					= $aCountries[ "en" ];

		return $a_ret;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _setShopInstallDir() {


		$s_shopFolderABS						= $_POST[ "targetfolder" ] . DIRECTORY_SEPARATOR;

		if ( $_POST[ "new_subfolder" ] )
			$s_shopFolderABS					.= $_POST[ "new_subfolder" ] . DIRECTORY_SEPARATOR;


		$this->s_shopInstallDir					= $s_shopFolderABS;
	}

	// ------------------------------------------------------------------------------------------------

	public function installOXID() {


		$this->_setShopInstallDir();


		$this->_installOXID__database();


		$this->_installOXID__config();

				
		$this->_installOXID__change_htaccess();
		
		
		$this->_installOXID__setAdmin();
		

		$this->_installOXID__setConfigSettings();
		
		
		$this->_installOXID__setFolderRights();
		
		
		$this->_installOXID__removeSetupDir();
		
	}

	// ------------------------------------------------------------------------------------------------

	protected function _installOXID__database() {



		$s_db_name								= $_POST[ "install_OXID__database_dbname" ];
		$s_db_user								= $_POST[ "install_OXID__database_dbuser" ];
		$s_db_password							= $_POST[ "install_OXID__database_dbpass" ];

		$s_sqlDir								= $this->s_shopInstallDir . "setup" . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR;


		// ------------------------------------------------------------------------------------------------
		// Create Database
		$this->_installOXID__checkDatabaseExists( $s_db_name );
		// ------------------------------------------------------------------------------------------------
		
		
		// ------------------------------------------------------------------------------------------------
		// Create User
		$this->_installOXID__createDatabaseUser( $s_db_name, $s_db_user, $s_db_password );
		// ------------------------------------------------------------------------------------------------
		

		// ------------------------------------------------------------------------------------------------
		// Shop-Datenbank
		$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_sqlDir . "database.sql" );


		// ------------------------------------------------------------------------------------------------
		// Demodata
		if ( isset( $_POST[ "install_OXID__demodata" ] ) && ( $_POST[ "install_OXID__demodata" ] == 1 ) )
			$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_sqlDir . "demodata.sql" );


		// ------------------------------------------------------------------------------------------------
		// Shop englisch
		if ( $_POST[ "install_OXID__languages" ] === "en" )
			$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_sqlDir . "en.sql" );


		// ------------------------------------------------------------------------------------------------
		// UTF-8
		if ( isset( $_POST[ "install_OXID__database_utf8" ] ) && ( $_POST[ "install_OXID__database_utf8" ] == 1 ) )
			$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_sqlDir . "latin1_to_utf8.sql" );


	}

	// ------------------------------------------------------------------------------------------------

	protected function _installOXID__setAdmin() {

		
		if ( isset( $_POST[ "install_OXID__admin_username" ] ) && isset( $_POST[ "install_OXID__admin_password" ] ) && ( $_POST[ "install_OXID__admin_username" ] ) && ( $_POST[ "install_OXID__admin_password" ] ) ) {
		
			
			$sLoginName							= $_POST[ "install_OXID__admin_username" ];
			$sPassword							= $_POST[ "install_OXID__admin_password" ];
			
			
			$s_boostrap_file					= $this->s_shopInstallDir . "bootstrap.php";
			
			#_log( "\$s_boostrap_file", $s_boostrap_file, __FILE__, __FUNCTION__, __LINE__ );
			
	
			include_once $s_boostrap_file;
			
			
			$sPassSalt							= oxUtilsObject::getInstance()->generateUID();
			$sPassword							= hash( "sha512", $sPassword . $sPassSalt );
		
			#_log( "\$sPassSalt", $sPassSalt, __FILE__, __FUNCTION__, __LINE__ );
			#_log( "\$sPassword", $sPassword, __FILE__, __FUNCTION__, __LINE__ );
			

			$sQ = "update oxuser set oxusername='{$sLoginName}', oxpassword='{$sPassword}', oxpasssalt='{$sPassSalt}' where oxid='oxdefaultadmin'";
			oxDb::getDb()->Execute( $sQ );
			
			#_log( "\$sQ", $sQ, __FILE__, __FUNCTION__, __LINE__ );
			
			$sQ = "update oxnewssubscribed set oxemail='{$sLoginName}' where oxuserid='oxdefaultadmin'";
			oxDb::getDb()->Execute( $sQ );
			
			#_log( "\$sQ", $sQ, __FILE__, __FUNCTION__, __LINE__ );
				
		}
		
	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _installOXID__setConfigSettings() {

		
		// ------------------------------------------------------------------------------------------------
		// initialisieren
		$sUid									= $this->_generateUid();
		
		$sBaseShopId							= "oxbaseshop";
		if ( $_POST[ "oxid_version" ] === "ee" )
			$sBaseShopId						= "1";
		
		$s_configKey							= $this->_getConfigKey();		#$oConfk->sConfigKey
		

		if ( isset( $_POST[ "install_OXID__check_updates" ] ) && ( $_POST[ "install_OXID__check_updates" ] == 1 ) )
			$b_check_updates					= 1;
		else
			$b_check_updates					= 0;
		
		if ( isset( $_POST[ "install_OXID__connect_oxidserver" ] ) && ( $_POST[ "install_OXID__connect_oxidserver" ] == 1 ) )
			$b_connect_oxidserver				= 1;
		else
			$b_connect_oxidserver				= 0;		
		
		
		// ------------------------------------------------------------------------------------------------
		// SQL
		$s_sql									.= "delete from oxconfig where oxvarname = 'blLoadDynContents';\n";
		$s_sql									.= "delete from oxconfig where oxvarname = 'sShopCountry';\n";
		$s_sql									.= "delete from oxconfig where oxvarname = 'blCheckForUpdates';\n";
		 // $this->execSql( "delete from oxconfig where oxvarname = 'aLanguageParams'" );
		$s_sql									.= "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue)";
		$s_sql									.= " values('" . $sUid . "', '" . $sBaseShopId . "', 'blCheckForUpdates', 'bool', ENCODE( '" . $b_check_updates . "', '" . $s_configKey . "'));\n";
		$sUid									= $this->_generateUid();
		$s_sql									.= "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue)";
		$s_sql									.= " values('" . $sUid . "', '" . $sBaseShopId . "', 'blLoadDynContents', 'bool', ENCODE( '" . $b_connect_oxidserver . "', '" . $s_configKey . "'));\n";
		
		
		
		$blUseDynPages							= "";
		$sCountryLang							= $_POST[ "install_OXID__countries" ];
		$sLocationLang							= $_POST[ "install_OXID__location_countries" ];
		$blSendShopDataToOxid					= 0;		 // TODO: ???
		
		$s_sql									.= "update oxcountry set oxactive = '0';\n";
		$s_sql									.= "update oxcountry set oxactive = '1' where oxid = '" . $sCountryLang . "';\n";
		

		// if it is international eshop, setting admin user country to selected one
		if ( $sLocationLang != "de" ) {
			$s_sql								.= "update oxuser SET oxcountryid = '$sCountryLang' where oxid='oxdefaultadmin';\n";
		}
		
		

		
		$sUid									= $this->_generateUid();
		$s_sql									.= "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue)";
		$s_sql									.= " values('" . $sUid . "', '" . $sBaseShopId . "', 'sShopCountry', 'str', ENCODE( '" . $sLocationLang . "', '" . $s_configKey . "'));\n";

		$sUid									= $this->_generateUid();
		$s_sql									.= "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue)";
		$s_sql									.= " values('" . $sUid . "', '" . $sBaseShopId . "', 'blSendShopDataToOxid', 'bool', ENCODE( '" . $blSendShopDataToOxid . "', '" . $s_configKey . "'));\n";


		
		// ------------------------------------------------------------------------------------------------
		// SQL-Datei
		$s_tmpFile								= $this->s_shopInstallDir . "__a4p_database__change_config.tmp.sql";
		
		file_put_contents( $s_tmpFile, $s_sql );
		
		
		$s_db_name								= $_POST[ "install_OXID__database_dbname" ];
		$s_db_user								= $_POST[ "install_OXID__database_dbuser" ];
		$s_db_password							= $_POST[ "install_OXID__database_dbpass" ];
		
		
		#_log( "\$_POST", $_POST, __FILE__, __FUNCTION__, __LINE__ );
		
		
		$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_tmpFile );
		
		
		unlink( $s_tmpFile );
		

		
		// ------------------------------------------------------------------------------------------------
		#$sShopLang		== "Sprache für Shop" [Deutsch|English]		^= install_OXID__languages
		$sShopLang								= $_POST[ "install_OXID__languages" ];
		
		
		$s_boostrap_file						= $this->s_shopInstallDir . "bootstrap.php";
		
		#_log( "\$s_boostrap_file", $s_boostrap_file, __FILE__, __FUNCTION__, __LINE__ );
		
		include_once $s_boostrap_file;
		
		
		$s_sql									= "select oxvarname, oxvartype, DECODE( oxvarvalue, '" . $s_configKey . "') AS oxvarvalue";
		$s_sql									.= " from oxconfig where oxvarname='aLanguageParams'";
		
		#_log( "\$s_sql", $s_sql, __FILE__, __FUNCTION__, __LINE__ );
		
		$aRes									= oxDb::getDb( 2 )->Execute( $s_sql );
		$aRow									= $o_res->fields;
		#_log( "\$aRow", $aRow, __FILE__, __FUNCTION__, __LINE__ );
		
		if ( $aRes ) {
			
			if ($aRow['oxvartype'] == 'arr' || $aRow['oxvartype'] == 'aarr') {
				$aRow['oxvarvalue']				= unserialize($aRow['oxvarvalue']);
			}
			#_log( "\$aRow", $aRow, __FILE__, __FUNCTION__, __LINE__ );
			
			$aLanguageParams					= $aRow['oxvarvalue'];
			foreach( $aLanguageParams as $sKey => $aLang ) {
				$aLanguageParams[ $sKey ][ "active" ]		= "0";
			}
			$aLanguageParams[ $sShopLang ][ "active" ]		= "1";
			#_log( "\$aLanguageParams", $aLanguageParams, __FILE__, __FUNCTION__, __LINE__ );
				
			$sValue								= serialize($aLanguageParams);
			$sUid								= $this->_generateUid();
			
			$s_sql								= "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue)";
			$s_sql								.= " values('" . $sUid . "', '" . $sBaseShopId . "', 'aLanguageParams', 'aarr',";
			$s_sql								.= " ENCODE( '$sValue', '" . $s_configKey . "'))";
			#_log( "\$s_sql", $s_sql, __FILE__, __FUNCTION__, __LINE__ );
			
			$_res								= oxDb::getDb( 2 )->Execute( $s_sql );
		}
		// ------------------------------------------------------------------------------------------------
		
		
		
	}

	// ------------------------------------------------------------------------------------------------
	
	protected function _installOXID__change_htaccess() {
	
		#	RewriteBase /
		
		#		$sHtaccessFile = preg_replace("/RewriteBase.*/, "RewriteBase " . $aParams["sBaseUrlPath"], $sHtaccessFile);
		
		
		$s_htaccess_file						= $this->s_shopInstallDir . ".htaccess";
		
		$s_shopSubfolder						= false;
		if ( isset( $_POST[ "new_subfolder" ] ) && $_POST[ "new_subfolder" ] )
			$s_shopSubfolder					= $_POST[ "new_subfolder" ];
		
		if ( $s_shopSubfolder && file_exists( $s_htaccess_file ) ) {
		
			$s_fileContent						= file_get_contents( $s_htaccess_file );
			
			$s_rewriteBase						= "/" . $s_shopSubfolder;
			
			$s_search							= "/RewriteBase.*/";
			$s_replace							= "RewriteBase " . $s_rewriteBase;
		
		}

		
		if ( file_exists( $s_htaccess_file ) ) {
			
			chmod( $s_htaccess_file, 0555 );
		}
		
	}
	
	// ------------------------------------------------------------------------------------------------

	/**
	 * exec CREATE DATABASE
	 * 
	 * @param unknown $s_database_name
	 */
	protected function _installOXID__checkDatabaseExists( $s_database_name ) {



		$s_SQL									= "CREATE DATABASE IF NOT EXISTS `" . $s_database_name . "`";
		
		if ( isset( $_POST[ "install_OXID__database_utf8" ] ) && ( $_POST[ "install_OXID__database_utf8" ] == 1 ) ) {
			
			$s_SQL								.= " CHARACTER SET utf8 COLLATE utf8_general_ci;";
			
			$s_SQL								.= "USE `" . $s_database_name . "`;\n";
			
			$s_SQL								.= "set names 'utf8';\n";
			$s_SQL								.= "set character_set_database=utf8;\n";
			$s_SQL								.= "SET CHARACTER SET latin1;\n";
			$s_SQL								.= "SET CHARACTER_SET_CONNECTION = utf8;\n";
			$s_SQL								.= "SET character_set_results = utf8;\n";
			$s_SQL								.= "SET character_set_server = utf8;\n";
			
		} else {
			
			$s_SQL								.= " CHARACTER SET latin1 COLLATE latin1_general_ci;";
			
			$s_SQL								.= "USE `" . $s_database_name . "`;\n";
			
			$s_SQL								.= "SET CHARACTER SET latin1;\n";
				
		}
		
		if ( isset( $_POST[ "install_OXID__database_utf8" ] ) && ( $_POST[ "install_OXID__database_utf8" ] == 1 ) )
			$iUtfMode							= 1;
		else
			$iUtfMode							= 0;
		$sBaseShopId							= "oxbaseshop";
		if ( $_POST[ "oxid_version" ] === "ee" )
			$sBaseShopId						= "1";
		$s_ConfigKey							= $this->_getConfigKey();		#$oConfk->sConfigKey
		
		
		$s_SQL									.= "delete from oxconfig where oxvarname = 'iSetUtfMode';\n";
		
		$s_SQL									.= "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue)";
		$s_SQL									.= "values('iSetUtfMode', '" . $sBaseShopId . "', 'iSetUtfMode', 'str',";
		$s_SQL									.= " ENCODE( '{$iUtfMode}', '" . $s_ConfigKey . "') )";
		#$sQ = "insert into oxconfig (oxid, oxshopid, oxvarname, oxvartype, oxvarvalue) values('iSetUtfMode', '$sBaseShopId', 'iSetUtfMode', 'str',
		# ENCODE( '{$iUtfMode}', '" . $oConfk->sConfigKey . "') )";
		
		
		

		$s_tmpFile								= $this->s_shopInstallDir . "__a4p_create_database.tmp.sql";

		file_put_contents( $s_tmpFile, $s_SQL );


		$s_db_name								= false;
		$s_db_user								= $_POST[ "install_OXID__database_dbuser" ];
		$s_db_password							= $_POST[ "install_OXID__database_dbpass" ];

		// ------------------------------------------------------------------------------------------------
		// use mysql root user
		if ( isset( $_POST[ "install_OXID__database_root_user" ] ) && ( $_POST[ "install_OXID__database_root_user" ] != "" ) )
			$s_db_user								= $_POST[ "install_OXID__database_root_user" ];
		if ( isset( $_POST[ "install_OXID__database_root_pass" ] ) && ( $_POST[ "install_OXID__database_root_pass" ] != "" ) )
			$s_db_password							= $_POST[ "install_OXID__database_root_pass" ];
		// ------------------------------------------------------------------------------------------------
		
		#_log( "\$_POST", $_POST, __FILE__, __FUNCTION__, __LINE__ );
		
		
		$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_tmpFile );

		
		unlink( $s_tmpFile );

	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _installOXID__createDatabaseUser( $s_db_name, $s_db_user, $s_db_password ) {

		
		#	CREATE USER 'asd'@'localhost' IDENTIFIED BY 'password';
		#	GRANT USAGE ON *.* TO 'asd'@'localhost' IDENTIFIED BY 'password' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
		#	GRANT ALL PRIVILEGES ON `asd\_%`.* TO 'asd'@'localhost';
		
		
		
		if ( isset( $_POST[ "install_OXID__database_root_user" ] ) && ( $_POST[ "install_OXID__database_root_user" ] != "" ) && isset( $_POST[ "install_OXID__database_root_pass" ] ) && ( $_POST[ "install_OXID__database_root_pass" ] != "" ) ) {
		
		
			$s_db_password						= str_replace( "'", "\'", $s_db_password );
					
			
			$s_SQL								= "CREATE USER '" . $s_db_user . "'@'localhost' IDENTIFIED BY '" . $s_db_password . "';\n";
			$s_SQL								.= "GRANT USAGE ON *.* TO '" . $s_db_user . "'@'localhost' IDENTIFIED BY '" . $s_db_password . "' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;\n";
			$s_SQL								.= "GRANT ALL PRIVILEGES ON `" . $s_db_name . "`.* TO '" . $s_db_user . "'@'localhost';\n";
			
			
	
			$s_tmpFile							= $this->s_shopInstallDir . "__a4p_create_database_user.tmp.sql";
			
			file_put_contents( $s_tmpFile, $s_SQL );
			
			
			$s_db_name							= false;
				
			// ------------------------------------------------------------------------------------------------
			// use mysql root user
			$s_db_user							= $_POST[ "install_OXID__database_root_user" ];
			$s_db_password						= $_POST[ "install_OXID__database_root_pass" ];
			// ------------------------------------------------------------------------------------------------
			
			#_log( "\$_POST", $_POST, __FILE__, __FUNCTION__, __LINE__ );
			
		
			$this->_exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_tmpFile );
		
		
			unlink( $s_tmpFile );
		
		
		}
		
	}

	// ------------------------------------------------------------------------------------------------
	
	protected function _getConfigKey() {
		
		
		#include getShopBasePath() . "core/oxconfk.php";
		
		#$s_boostrap_file					= $this->s_shopInstallDir . "bootstrap.php";
			
		#_log( "\$s_boostrap_file", $s_boostrap_file, __FILE__, __FUNCTION__, __LINE__ );
			
		
		#"core/oxconfk.php
		
		#$oConfk->sConfigKey
		
		$s_oxconfk_file							= $this->s_shopInstallDir . "core/oxconfk.php";
		
		$oConfk									= new install_OXID_Confk( $s_oxconfk_file );
		$s_conf_key								= $oConfk->sConfigKey;
		
		#_log( "\$s_conf_key", $s_conf_key, __FILE__, __FUNCTION__, __LINE__ );
		
		
		return $s_conf_key;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function _generateUID() {
		
		return md5( uniqid( rand(), true ) );
	}
	
	// ------------------------------------------------------------------------------------------------

	/**
	 * 
	 * @param string $s_db_name
	 * @param string $s_db_user
	 * @param string $s_db_password
	 * @param string $s_sqlFile
	 * 
	 * @return boolean true on success | boolean false on missing user/pass/sqlfile | int on error
	 */
	protected function _exec_mysql( $s_db_name, $s_db_user, $s_db_password, $s_sqlFile ) {


		if ( $s_db_user && $s_db_password && $s_sqlFile ) {

			$s_cmd								= "mysql";

			$s_cmd								.= " -u " . $s_db_user;
			$s_cmd								.= " --password=" . $s_db_password;

			if ( $s_db_name )
				$s_cmd							.= " " . $s_db_name;

			$s_cmd								.= " < " . $s_sqlFile;

			#var_dump( $s_cmd );


			$res								= exec( $s_cmd, $aOutput, $i_ret );

			//	
					/*
			#_log( "\$s_cmd", $s_cmd, __FILE__, __FUNCTION__, __LINE__ );
			#_log( "\$res", $res, __FILE__, __FUNCTION__, __LINE__ );
			#_log( "\$aOutput", $aOutput, __FILE__, __FUNCTION__, __LINE__ );
			#_log( "\$i_ret", $i_ret, __FILE__, __FUNCTION__, __LINE__ );
			//*/
			
			if ( $i_ret === 0 )
				return true;
			else
				return $i_ret;
			

		} else
			return false;

	}

	// ------------------------------------------------------------------------------------------------

	protected function _installOXID__config() {

		$o_a4p_tools							= new a4p_tools();

		$s_shopFolderABS						= $o_a4p_tools->a4p_appendSlash( $_POST[ "targetfolder" ] );

		if ( isset( $_POST[ "new_subfolder" ] ) && $_POST[ "new_subfolder" ] )
			$s_shopFolderABS					.= $_POST[ "new_subfolder" ] . DIRECTORY_SEPARATOR;

		$s_configFileABS						= $s_shopFolderABS . "config.inc.php";


		$s_edition								= $_POST[ "oxid_version" ];
		if ( $s_edition === "ce_other" )
			$s_edition							= "ce";

		
		// config-Werte festlegen
		$s_shopUrl								= $o_a4p_tools->a4p_appendSlash( $_POST[ "install_OXID__shop_url" ] );
		if ( isset( $_POST[ "new_subfolder" ] ) && $_POST[ "new_subfolder" ] )
			$s_shopUrl							.= $_POST[ "new_subfolder" ] . DIRECTORY_SEPARATOR;
		
		$s_shopDir								= $s_shopFolderABS;
		
		
		// ------------------------------------------------------------------------------------------------
		// suchen und ersetzen
		$a_search_replace										= array();
		$a_search_replace[ "<dbHost_" . $s_edition . ">" ]		= $_POST[ "install_OXID__database_hostname" ];
		$a_search_replace[ "<dbName_" . $s_edition . ">" ]		= $_POST[ "install_OXID__database_dbname" ];
		$a_search_replace[ "<dbUser_" . $s_edition . ">" ]		= $_POST[ "install_OXID__database_dbuser" ];
		$a_search_replace[ "<dbPwd_" . $s_edition . ">" ]		= $_POST[ "install_OXID__database_dbpass" ];
		$a_search_replace[ "<sShopURL_" . $s_edition . ">" ]	= $s_shopUrl;
		$a_search_replace[ "<sShopDir_" . $s_edition . ">" ]	= $s_shopDir;
		$a_search_replace[ "<sCompileDir_" . $s_edition . ">" ]	= $s_shopFolderABS . "tmp";
		$a_search_replace[ "<iUtfMode>" ]						= (int)$_POST[ "install_OXID__database_utf8" ];
		// ------------------------------------------------------------------------------------------------

		if ( file_exists( $s_configFileABS ) ) {

			// ------------------------------------------------------------------------------------------------
			// Datei auslesen und Werte ändern
			$a_configFile__content				= file( $s_configFileABS );

			$a_configFile__content_new			= array();
			foreach( $a_configFile__content as $key => $s_line ) {

				foreach( $a_search_replace as $s_search => $s_replace ) {

					$s_line						= str_replace( $s_search, $s_replace, $s_line );
				}
				array_push( $a_configFile__content_new, $s_line );

			}
#var_dump( $a_configFile__content_new );


			// ------------------------------------------------------------------------------------------------
			// Rechte auf schreiben setzen
			$resChmod							= chmod( $s_configFileABS, 0777 );


			// ------------------------------------------------------------------------------------------------
			// Datei schreiben
			$b_res								= file_put_contents( $s_configFileABS, $a_configFile__content_new );


			// ------------------------------------------------------------------------------------------------
			// Rechte auf nicht schreiben setzen
			$resChmod							= chmod( $s_configFileABS, 0555 );


		} else
			return false;

	}

	// ------------------------------------------------------------------------------------------------
	
	protected function _installOXID__setFolderRights() {
		
		
		$o_a4p_tools							= new a4p_tools();
		
		
		$a_setFolders							= array();
		$a_setFolders[ "export" ]				= 0777;
		$a_setFolders[ "log" ]					= 0777;
		$a_setFolders[ "out" ]					= 0777;
		$a_setFolders[ "tmp" ]					= 0777;
		
		
		foreach( $a_setFolders as $s_folderName => $i_mode ) {
			
			$s_curDir							= $this->s_shopInstallDir . $s_folderName;
			
			#_log( "\$s_curDir", $s_curDir, __FILE__, __FUNCTION__, __LINE__ );
			
			if ( file_exists( $s_curDir ) ) {
				$o_a4p_tools->a4p_chmodFolderRec( $s_curDir, $i_mode );
			}
		}
		
	}
	
	// ------------------------------------------------------------------------------------------------
	
	protected function _installOXID__removeSetupDir() {
		
		
		$s_setupDir								= $this->s_shopInstallDir . "setup";
		
		if ( file_exists( $s_setupDir ) ) {
			
			
			$o_a4p_tools						= new a4p_tools();
			$o_a4p_tools->a4p_deleteFolderRec( $s_setupDir );
			
		}
		
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function get_shop_links() {
		
	
		// ------------------------------------------------------------------------------------------------
		$o_a4p_tools							= new a4p_tools();
		$s_shopUrl								= $o_a4p_tools->a4p_appendSlash( $_POST[ "install_OXID__shop_url" ] );
		if ( isset( $_POST[ "new_subfolder" ] ) && $_POST[ "new_subfolder" ] )
			$s_shopUrl							.= $_POST[ "new_subfolder" ] . DIRECTORY_SEPARATOR;
		
		$s_adminUrl								= $s_shopUrl . "admin" . DIRECTORY_SEPARATOR;
		
		$a_ret									= array();
		$a_ret[ "shop_url" ]					= $s_shopUrl;
		$a_ret[ "admin_url" ]					= $s_adminUrl;
		
		return $a_ret;
	}
	
	// ------------------------------------------------------------------------------------------------
	
}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class module_download {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	#protected $s_moduleDownloadUrl__ioly		= "https://github.com/ioly/ioly/archive/ioly_connector-oxid_v1.2.2.zip";
	protected $s_moduleDownloadUrl__ioly		= "https://github.com/ioly/ioly/archive/connector-oxid.zip";

	protected $s_unzippedFilename__ioly			= "ioly-connector-oxid";

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function getModulesDir() {


		$s_shop_modulesFolderABS				= $_POST[ "targetfolder" ] . DIRECTORY_SEPARATOR;

		if ( $_POST[ "new_subfolder" ] )
			$s_shop_modulesFolderABS			.= $_POST[ "new_subfolder" ] . DIRECTORY_SEPARATOR;

		$s_shop_modulesFolderABS				.= "modules" . DIRECTORY_SEPARATOR;

		return $s_shop_modulesFolderABS;
	}

	// ------------------------------------------------------------------------------------------------

	public function downloadModule_ioly() {


		$o_downloader							= new downloader();
		$o_downloader->setDownloadUrl( $this->s_moduleDownloadUrl__ioly );
		$o_downloader->setTargetFolder( $this->getModulesDir() );
		$o_downloader->setTargetFilename( pathinfo( $this->s_moduleDownloadUrl__ioly, PATHINFO_BASENAME ) );
		$o_downloader->downloadFile();

		$o_downloader->unzipFile();

	}

	// ------------------------------------------------------------------------------------------------

	public function getDownloadUrl_ioly() {

		return $this->s_moduleDownloadUrl__ioly;
	}

	// ------------------------------------------------------------------------------------------------

	public function getUnzippedFilename_ioly() {

		return $this->s_unzippedFilename__ioly;
	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class module_install {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------


	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function installModule_ioly() {


		// mv modules/ioly-ioly_connector-oxid_v1.2.2/modules		=> $s_moduleDir

		// rm modules/ioly-ioly_connector-oxid_v1.2.2


		$o_module_download						= new module_download();

		$s_downloadUrl							= $o_module_download->getDownloadUrl_ioly();

		$s_downloadFilename						= $o_module_download->getUnzippedFilename_ioly();

		$s_moduleFolder							= $o_module_download->getModulesDir();


		$s_iolyModuleFolder						= $s_moduleFolder . $s_downloadFilename . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "ioly";
		#var_dump( $s_iolyModuleFolder );

		rename( $s_iolyModuleFolder, $s_moduleFolder . DIRECTORY_SEPARATOR . "ioly" );

		
		$o_a4p_tools							= new a4p_tools();
		$o_a4p_tools->a4p_deleteFolderRec( $s_moduleFolder . $s_downloadFilename );

	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

/**
 * 
 * @author ASD
 * 
 * class for include "OXID/core/oxconfk.php" -> sConfigKey

 *
 */
class install_OXID_Confk {
		
	public function __construct( $s_oxconfk_file ) {
		
		if ( $s_oxconfk_file )
			include $s_oxconfk_file;
		
	}
}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

/**
 *
 * @param string $s_text
 * @param unknown $_var			Ausgabe per var_dump(); wird bei Wert "null" nicht ausgegeben
 * @param string $s__FILE__
 * @param string $s__FUNCTION__
 * @param int $i__LINE__
 */
function _log( $s_text, $_var, $s__FILE__ = null, $s__FUNCTION__ = null, $i__LINE__ = null, $b_with_backtrace = false ) {

	
	$s_logFileABS								= "./__a4pInstallClient2-log.txt";
	
	if ( !is_null( $s_logFileABS ) ) {
			

		// ------------------------------------------------------------------------------------------------
		// Log-Ausgabe
		ob_start();
			
		#echo "\n";
		echo "\n";

		echo str_repeat( "=", 100 );
			
		// Datum
		echo date( "d-m-Y H:i:s" );
		echo "\n";
			
		// __FILE__ | __FUNCTION | __LINE__
		echo $s__FILE__ . " | " . $s__FUNCTION__ . " | " . $i__LINE__;
		echo "\n";

		// Text
		echo $s_text;
		echo "\n";

		// Variable
		if ( $_var !== "null" ) {
			var_dump( $_var );
			echo "\n";
		}
			
		// ------------------------------------------------------------------------------------------------
		// backtrace
		if ( $b_with_backtrace )
			debug_print_backtrace();
			
		$content								= ob_get_contents();
		ob_end_clean();
			
		// ------------------------------------------------------------------------------------------------
		// Log schreiben
		file_put_contents( $s_logFileABS, $content, FILE_APPEND );
			
	}

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_tools {
	
	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------
	
	
	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	/**
	 * @desc	Slash am Ende eines Pfades/Strings anhängen, falls nicht vorhanden
	 *
	 * @return	string mit Slash am Ende
	 */
	function a4p_appendSlash( $s_path ) {
	
		if ( substr( $s_path, strlen( $s_path ) - 1 ) !== DIRECTORY_SEPARATOR )
			$s_path								.= DIRECTORY_SEPARATOR;
	
		return $s_path;
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function a4p_deleteFolderRec( $s_curFolderABS ) {

		
		if ( file_exists( $s_curFolderABS ) ) {
	
			$dh									= opendir( $s_curFolderABS );
			if ( $dh !== false ) {
	
				$s_curFileNAME					= readdir( $dh );
				while ( $s_curFileNAME !== false ) {
	
	
					$s_curFileABS				= $s_curFolderABS;
					if ( substr( $s_curFileABS, strlen( $s_curFileABS ) - 1 ) !== DIRECTORY_SEPARATOR )
						$s_curFileABS			.= DIRECTORY_SEPARATOR;
					$s_curFileABS				.= $s_curFileNAME;
	
	
					if ( ( $s_curFileNAME != "." ) && ( $s_curFileNAME !== ".." ) ) {
	
	
						if ( is_dir( $s_curFileABS ) ) {
	
							// ------------------------------------------------------------------------------------------------
							// rekursiv aufrufen
							$this->a4p_deleteFolderRec( $s_curFileABS );
	
						} else {
	
							// ------------------------------------------------------------------------------------------------
							// alle Dateien löschen
							unlink( $s_curFileABS );
						}
					}
	
					$s_curFileNAME				= readdir( $dh );
				}
	
				closedir( $dh );
			}
	
			// ------------------------------------------------------------------------------------------------
			// jetzt leeren Ordner löschen
			rmdir( $s_curFolderABS );
		}
	
	}
	
	// ------------------------------------------------------------------------------------------------
	
	public function a4p_chmodFolderRec( $s_curFolderABS, $i_mode ) {
		
		
		if ( file_exists( $s_curFolderABS ) ) {
		
			$dh									= opendir( $s_curFolderABS );
			if ( $dh !== false ) {
		
				$s_curFileNAME					= readdir( $dh );
				while ( $s_curFileNAME !== false ) {
		
		
					$s_curFileABS				= $s_curFolderABS;
					if ( substr( $s_curFileABS, strlen( $s_curFileABS ) - 1 ) !== DIRECTORY_SEPARATOR )
						$s_curFileABS			.= DIRECTORY_SEPARATOR;
					$s_curFileABS				.= $s_curFileNAME;
		
		
					if ( ( $s_curFileNAME != "." ) && ( $s_curFileNAME !== ".." ) ) {
		
		
						if ( is_dir( $s_curFileABS ) ) {
		
							
							// ------------------------------------------------------------------------------------------------
							// Rechte für aktuellen Ordner setzen
							chmod( $s_curFileABS, $i_mode );
							#_log( "Ordner \$s_curFileABS", $s_curFileABS, __FILE__, __FUNCTION__, __LINE__ );
							
							
							// ------------------------------------------------------------------------------------------------
							// rekursiv aufrufen
							$this->a4p_chmodFolderRec( $s_curFileABS, $i_mode );
		
						} else {
		
							// ------------------------------------------------------------------------------------------------
							// Rechte für aktuelle Dateien setzen
							chmod( $s_curFileABS, $i_mode );
							#_log( "Dateien \$s_curFileABS", $s_curFileABS, __FILE__, __FUNCTION__, __LINE__ );
								
						}
					}
		
					$s_curFileNAME				= readdir( $dh );
				}
		
				closedir( $dh );
				
				
				// ------------------------------------------------------------------------------------------------
				// Rechte für Ordner selbst setzen
				$b_res							= chmod( $s_curFolderABS, $i_mode );
				#_log( "Ordner \$s_curFolderABS", $s_curFolderABS, __FILE__, __FUNCTION__, __LINE__ );
				#_log( "\$b_res", $b_res, __FILE__, __FUNCTION__, __LINE__ );
				
				
			}
		
		}
		
		
	}
	
	// ------------------------------------------------------------------------------------------------
	
}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
