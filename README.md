Gasemon is a game server monitor that can be used to send out notifications when
there are players on servers.

Uses Austinb/GameQ for game server queries and jublonet/codebird-php for Twitter
connection.

Coded with love for the Action Half-Life community.

Includes Twitter connector for Action Half-Life. Other type of processors can be
created by implementing ServerProcessorInterface and its methods.

Requires at least PHP 5.4 with SSL and SQLite3 support, and Composer.

**TIP!** On CentOS 6 PHP prerequisites can be installed by running:
`yum install php56-php php56-php-cli php56-php-common php56-php-pdo`

Note that you'll then have to command *php56* instead of *php*

###Usage###

* Run *php composer.phar install* or *composer install* (depending on your
  Composer installation)
* Run *php setup.php*
* Configure cooldown period and add your Twitter keys and secrets into
  *config.ini* if you're using the default TweetBot as server list processor

-tsuriga, 2015
