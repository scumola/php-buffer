php-buffer
==========

Like http://bufferapp.com but uses rabbitmq and php and is bit.ly-aware.  Stupid-simple.

Spin up a rabbitmq server and a LAMP stack on a machine somewhere.  Edit config.php to point to it.  Get some OAuth tokens and bit.ly tokens and fill in where appropriate (search for 'xxxx' and replace appropriately).

Then, run 'tweeter.php' and it'll take one tweet off the queue every hour during daylight hours and push it to twitter.  Tested with the new v1.1 Twitter API.

You can point your Google Reader urls at the submit.php url to auto-submit from Google Reader or any other reader that accepts urls.  An example url would be:

http://hostname/submit.php?source=${source}&title=${title}&url=${url}&shorturl=${short-url}
