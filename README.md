# MGVO-API
Homepage API for MGVO.

See [Here](https://info.mgvo.de)

##API

[ext_mod_hp](ext_mod_hp.php)  
Library for exchanging data from and to an MGVO account.

[mgvo_sniplets](mgvo_sniplets.php)  
Library to generate HTML-Code out of the received data as a basic implementation for any CMS plugin

[mgvo_generic_sniplets](mgvo_generic_sniplets.php)  
Library to generate generic HTML-Code (extends normal mgvo_snipets.php)

##Tests

[test/ext_mod_hp_tst](test/ext_mod_hp_tst.php)  
PHP program to test the MGVO API (class MGVO_HPAPI)

[test/mgvo_sniplet_tst](test/mgvo_sniplets_tst.php)  
PHP program to test the sniplet library (class MGVO_SNIPLET)

##Includes

[includes/ext_hlpfkt](includes/ext_hlpfkt.php)  
Several PHP help functions

[includes/ext_cipher](includes/ext_cipher.php)  
Cryptographic PHP function to encrypt and decrypt data
