==== Stripe payments for Zencart ======

Full details in the /docs/ folder

Online forum

http://www.zen-cart.com/showthread.php?201999-Stripe-com-payment-integration-module

==== v1.1 ====

Introduces a new admin control whether to create Customer Objects - also a warning to the customer on the front end that they cannot checkout if javascript is disabled in their browser.

==== v1.2 ====

Takes the above mentioned javascript text out of the file as it was being appended to the 'success' emails.

==== v 1.3 =====

Added in EUR and GBP as currency choices
Edited CAN to CAD to allow CAD currency to work
Fixed error in billing address (previously was using shipping)

==== v 1.3.1 =====

fixed commenting error in dropdown thanks to fretsbr549
added floor() to all instances of ($order->info['total']) * 100   - thanks to filmgirl
