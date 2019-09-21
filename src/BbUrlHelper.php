<?php

namespace App;

class BbUrlHelper
{
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * https://oldschool.co.nz/index.php?/topic/59407-looking-for-mustang-specialist-in-auckland/&do=findComment&comment=2022877
     * =>
     * https://oldschool.co.nz/index.php?/topic/29806-mikeys-72-datsun-240z/page/22/&do=editComment&comment=2142277
     *
     * @param string $topicUrl
     * @return string
     */
    public function convertToEditPost(string $topicUrl): string
    {
        return $this->baseUrl .
            preg_replace('~.*/topic/([^/]+).*comment=(\d+).*~',
                '/topic/\1/&do=editComment&comment=\2',
                $topicUrl);
    }

    public function isCommentUrl(string $url): bool
    {
        return preg_match('~topic.*comment=~', $url);
    }
}
