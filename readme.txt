iDeal payment module for Ubercart

PREREQUISITES

- Drupal 5.X

INSTALLATION

Install and activate this module like every other Drupal
module.
This module needs openssl-extension for PHP enabled.
!!IMPORTANT!!
-Download the "thinmpi" or "iDEALConnector" PHP librairy from the iDEAL controlpanel/dashboard, available to you when you have an iDEAL account. This can be inside a "PHP programming example" archive in the documents section of the iDEAL dashboard.
Due to licencing restriction this code can not be included on Drupal.org.
-ThinMPI:
  -Copy the contents of the entire extracted thinmpi directory to the module directory "uc_ideal_pro/lib". The directorystructure should be like "uc_ideal_pro/lib/ThinMPI.php"
  -Open "LoadConf.php" file for editing and change the function name "LoadConfiguration()" to something else like "donotLoadConfiguration()". Failing to do so will hang your site upon module installation.
  -Copy private key + cert to "lib/security/" directory.
-iDEALConnector:
  -Copy the contents of the entire extracted connector directory to the module directory "uc_ideal_pro/lib".  The directorystructure should be like "uc_ideal_pro/lib/iDEALConnector.php"
  -Open "iDEALConnector.php" file for editing and add this line  "    return LoadConfiguration(); //Qrios hack for Ubercart" just below "	function loadConfig()" on line 722 on this moment. Make sure to add this after the opening bracket "{". If you don't do this the "includes/security/config.conf" file will be used for settings, the Ubercart iDEAL settings will be discarded.
  -Open "iDEALConnector_config.inc.php" file for editing and replace the line  "define( "SECURE_PATH"...." on line 10 on this moment for "define( "SECURE_PATH", $_SERVER['DOCUMENT_ROOT'].'/'.drupal_get_path('module', 'uc_ideal_pro_payment')."/lib/includes/security");". If you don't do this you can configure this setting manually. This will give you the opportunity to locate this directory outside the web root, though the contents are protected by a .htaccess file.
  -Copy private key + cert to "lib/includes/security/" directory.
-Thats it, see docs + FAQ in iDEAL dashboard for more info.

DESCRIPTION

Receive payments through checkout via Ideal ING/Postbank Advanced or Rabo Professional.


TOUBLESHOOTING

-Blank page when trying to submit an order: Review Drupal log for "Cannot modify header information - headers already sent by..." messages, remove empty lines after closing PHP tag "?>" in the file mentioned in the log message.

AUTHOR

C. Kodde
Qrios Webdiensten
http://qrios.nl
c.kodde NOatSPAM qrios dot nl

SPONSOR
Portecenter (Initial development)
Synetic (ING iDEALConnector support)

