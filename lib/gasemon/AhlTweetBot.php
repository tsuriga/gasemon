<?php

namespace gasemon;

use \Codebird\Codebird;

/**
 * Tweets information on AHL server statuses
 */
class AhlTweetBot implements ServerProcessorInterface
{
    const MAPNAME_MAXLENGTH = 34;
    const HOSTNAME_MAXLENGTH = 56;

    /**
     * @var int
     */
    private $cooldownSeconds;

    /**
     * @var Codebird
     */
    private $cb;

    /**
     * @param int $cooldownMinutes
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $accessToken
     * @param string $accessTokenSecret
     */
    public function __construct(
        $cooldownMinutes,
        $consumerKey,
        $consumerSecret,
        $accessToken,
        $accessTokenSecret
    ) {
        $this->cooldownSeconds = $cooldownMinutes * 60;

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

        $playerCount = 0;
        $activeServerCount = 0;

        foreach ($servers as $aServer) {
            $playerCount += @$aServer['num_players'] - @$aServer['num_bots'];

            if (@$aServer['num_players'] > 0) {
                $activeServerCount++;
            }

            if (@$aServer['num_players'] > @$server['num_players']) {
                $server = $aServer;
            }
        }

        if ($playerCount === 0) {
            return;
        }

        $msg = sprintf(
            "%d players on %d servers. %d/%d players on '%s' (%s)",
            $playerCount,
            $activeServerCount,
            $server['num_players'] - $server['num_bots'],
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
