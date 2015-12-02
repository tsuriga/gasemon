<?php

namespace gasemon;

use \Codebird\Codebird;

/**
 * Tweets information on server statuses
 */
class TweetBot implements ServerProcessorInterface
{
    const MAPNAME_MAXLENGTH = 24
    const HOSTNAME_MAXLENGTH = 36;

    /**
     * @var int
     */
    private $cooldownSeconds;

    /**
     * @var Codebird
     */
    private $cb;

    /**
     * @param int $cooldownSeconds
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $accessToken
     * @param string $accessTokenSecret
     */
    public function __construct(
        $cooldownTime,
        $consumerKey,
        $consumerSecret,
        $accessToken,
        $accessTokenSecret
    ) {
        $this->cooldownSeconds = $cooldownTime * 60;

        $this->cb = new Codebird();
        $this->cb->setConsumerKey($consumerKey, $consumerSecret);
        $this->cb->setToken($accessToken, $accessTokenSecret);
    }

    /**
     * @param array $servers
     */
    public function process(array $servers)
    {
        if ($this->cooldownIsActive()) {
            return;
        }

        $server = $servers[0];
        $playerCount = 0;

        foreach ($servers as $aServer) {
            $playerCount += $aServer['num_players'];

            if ($aServer['num_players'] > $server['num_players']) {
                $server = $aServer;
            }
        }

        $msg = sprintf(
            "%d players on %d servers. %d/%d players on '%s' (%s). Join: %s",
            $playerCount,
            count($servers),
            $server['num_players'],
            $server['max_players'],
            $this->abbreviate($server['hostname'], self::HOSTNAME_MAXLENGTH),
            $this->abbreviate(server['map'], self::MAPNAME_MAXLENGTH),
            $server['gq_joinlink']
        );

        $this->cb->statuses_update(['status' => $msg]);
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
    private function cooldownIsActive()
    {
        if (!file_exists('data/tweet.lock')) {
            file_put_contents('data/tweet.lock', time());

            return false;
        }

        return time() - file_get_contents('data/tweet.lock') >
            $this->cooldownSeconds;
    }
}
