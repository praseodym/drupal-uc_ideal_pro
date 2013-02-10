Drupal Ubercart payment provider for ING iDEAL Advanced, Rabobank iDEAL Professional and ABN iDEAL Zelfbouw.

This module has been written from the ground up and has currently only been tested against ideal-simulator.nl and ING iDEAL Advanced.

Upgrading from 1.x:
- Backup your certificate, private key and password;
- Uninstall the old module via the modules page;
- Install this version as stated below.

Installation:
- Move the 'ssl' directory outside your wwwroot;
- Copy acquirer public certificate (for decoding xml messages) to the 'ssl' directory (rename file to naming convention described below);
- Copy your generated certificate and private key into this 'ssl' directory;
- Enter the generated certificate filename, private key paths and password in the module settings page;
- Enable the module and configure it on the Ubercart payment method settings page.

Multiple certificates are allowed. Place them in the ssl directory with names like: '[name]-[x].cer' and '[name].test-[x].cer', where [name] is the name of the bank and [x] is a number from 0 to 9.

Author:
Mark Janssen (praseodym)

Thanks to:
- Qrios Webdevelopment
- Martijn Wieringa, PHP Solutions
- All contributers that submitted patches through the issue queue :)