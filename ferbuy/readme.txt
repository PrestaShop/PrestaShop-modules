//-----------------------------------------------------------------
// PrestaShop FerBuy payment extension
// Version: 1.3.0 for Prestashop 1.4+

// Email: info@ferbuy.com
// Web: http://www.ferbuy.com
//------------------------------------------------------------------

PLEASE NOTE: THIS VERSION IS NOT COMPATIBLE WITH PRESTASHOP 1.3.x AND LOWER.
This version only works with PrestaShop 1.4 or higher.

The author of this plugin can NEVER be held responsible for this software.
There is no warranty whatsoever. You accept this by using this software.


Changelog
=========

1.3.0 - Added Polish, Czech, Dutch and Spanish languages
        Tested on PrestaShop 1.5.6.0

1.2.0 - Added language

1.1.1 - Bugfix verification url in multi-store

1.1.0 - Added Shoppping Cart.

1.0.1 - Added possibility to restore previous order on failed transaction

1.0.0 - Initial version
        Tested on PrestaShop 1.5.4.1


Installation
============

1. Log your self to the FerBuy back office at https://my.ferbuy.com and do the following:
   - Navigate to `Websites` and select your Site by clicking edit icon near your site
   - In the section `Site configuration` set the `Verification URL` to http://www.yoursite.com/modules/ferbuy/validation.php
     (don't forget to replace the www.yoursite.com with your domain name)
   - Fill in the shared `Secret` field
   - Update the changes!

2. Upload the plug-in content files to their equivalent directories in your Prestashop root installation

3. Install the extension in the PrestaShop admin panel modules section. Search for FerBuy payments

4. Ensure module status is enabled and test mode is set correctly. To avoid common problems:
   - Please enter your `Site ID` (only digits) and your `Secret`, you can find your `Site ID` and `Secret` at the merchant's back office!
   - If you want to process real payments make sure to switch to `Live Mode`

5. All DONE!


NOTES
============

 - Transaction Initialized status indicates that the customer was redirected to the payment gateway and finishing his payment
 - Transaction Complete status indicates that the payment was processed and successful
 - Transaction Failed status indicates that the payment was unsuccessful or the customer canceled the payment
