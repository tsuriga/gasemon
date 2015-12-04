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
    const MAPNAME_MAXLENGTH = 34;

    /**
     * @var int Maximum length for server's name in a post
     */
    const HOSTNAME_MAXLENGTH = 56;

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
        if (count($servers) < 1) {
            return;
        }

        $server = reset($servers);

        $totalPlayerCount = 0;
        $activeServerCount = 0;

        foreach ($servers as $aServer) {
            $aServerPlayerCount =
                @$aServer['num_players'] - @$aServer['num_bots'];

            $totalPlayerCount += $aServerPlayerCount;

            if ($aServerPlayerCount > 0) {
                $activeServerCount++;
            }

            if (@$aServer['num_players'] > @$server['num_players']) {
                $server = $aServer;
            }
        }

        $serverPlayerCount = @$server['num_players'] - @$server['num_bots'];

        if ($totalPlayerCount === 0 ||
            $serverPlayerCount < $this->minimumPlayerCount
        ) {
            return;
        }

        $msg = sprintf(
            "%d players on %d servers. %d/%d players on '%s' (%s)",
            $totalPlayerCount,
            $activeServerCount,
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
