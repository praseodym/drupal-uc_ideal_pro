iDeal payment module for Ubercart

PREREQUISITES

- Drupal 5.X

INSTALLATION

Install and activate this module like every other Drupal
module.
This module needs openssl-extension for PHP enabled.
!!IMPORTANT!!
-Download the "thinmpi" PHP librairy from the iDEAL controlpanel/dashboard, available to you when you have an iDEAL account. Due to licencing restriction this code can not be included on Drupal.org.
-Copy the entire extracted thinmpi directory to the module directory (uc_ideal_pro/thinmpi).
-Open "LoadConf.php" file for editing and change the function name "LoadConfiguration()" to something else like "donotLoadConfiguration()". Failing to do so will hang your site upon module installation.
-Thats it

DESCRIPTION

Receive payments through checkout via Ideal ING/Postbank Advanced.

AUTHOR

C. Kodde
Qrios Webdiensten
http://qrios.nl
c.kodde NOatSPAM qrios dot nl

SPONSOR


