<?php

namespace gasemon;

use \Codebird\Codebird;

/**
 * Tweets information on AHL server statuses
 */
class AhlTweetBot implements ServerProcessorInterface
{
    /**
     * @var int Maximum length for the map name in a post
     */
    const MAPNAME_MAXLENGTH = 46;

    /**
     * @var int Maximum length for server's name in a post
     */
    const HOSTNAME_MAXLENGTH = 70;

    /**
     * @var int
     */
    private $cooldownSeconds;

    /**
     * @var int Minimum player count required for posting
     */
    private $minimumPlayerCount;

    /**
     * @var Codebird
     */
    private $cb;

    /**
     * @param int $cooldownMinutes
     * @param int $minimumPlayerCount
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $accessToken
     * @param string $accessTokenSecret
     */
    public function __construct(
        $cooldownMinutes,
        $minimumPlayerCount,
        $consumerKey,
        $consumerSecret,
        $accessToken,
        $accessTokenSecret
    ) {
        $this->cooldownSeconds = $cooldownMinutes * 60;
        $this->minimumPlayerCount = $minimumPlayerCount;

        $this->cb = new Codebird();
        $this->cb->setConsumerKey($consumerKey, $consumerSecret);
        $this->cb->setToken($accessToken, $accessTokenSecret);
    }

    /**
     * @param array $servers
     */
    public function process(array $servers)
    {
        $server = reset($servers);

        foreach ($servers as $aServer) {
            $aServerPlayerCount =
                @$aServer['num_players'] - @$aServer['num_bots'];
            $serverPlayerCount =
                @$server['num_players'] - @$server['num_bots'];

            if ($aServerPlayerCount > $serverPlayerCount) {
                $server = $aServer;
            }
        }

        $serverPlayerCount = @$server['num_players'] - @$server['num_bots'];

        if ($serverPlayerCount < $this->minimumPlayerCount) {
            return;
        }

        $msg = sprintf(
            "%d/%d players on '%s' (%s)",
            $serverPlayerCount,
            $server['max_players'],
            $this->abbreviate($server['hostname'], self::HOSTNAME_MAXLENGTH),
            $this->abbreviate($server['map'], self::MAPNAME_MAXLENGTH)
        );

        $this->cb->statuses_update(['status' => $msg]);

        $this->activateCooldownPeriod();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return !$this->isInCooldownPeriod();
    }

    /**
     * @param string $str
     * @param int $maxLength
     * @return string
     */
    private function abbreviate($str, $maxLength)
    {
        return strlen($str) < $maxLength ?
            $str : substr($str, 0, $maxLength - 3) . '...';
    }

    /**
     * @return bool
     */
    private function isInCooldownPeriod()
    {
        if (!file_exists('data/tweet.lock')) {
            return false;
        }

        return time() - file_get_contents('data/tweet.lock') <
            $this->cooldownSeconds;
    }

    private function activateCooldownPeriod()
    {
        file_put_contents('data/tweet.lock', time());
    }
}
