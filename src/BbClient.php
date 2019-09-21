<?php

namespace App;

use Goutte\Client;
use Monolog\Logger;
use Symfony\Component\DomCrawler\Crawler;

class BbClient
{
    /** @var Client */
    private $client;

    /** @var BbUrlHelper */
    private $bbUrlHelper;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    public function __construct(Container $container, string $baseUrl, string $username, string $password)
    {
        $this->client = $container->get('client');
        $this->bbUrlHelper = $container->get('bbUrlHelper');
        $this->logger = $container->get('logger');
        $this->baseUrl = $baseUrl;
        $this->password = $password;
        $this->username = $username;
    }

    public function replaceInPosts($searchRegex, $replace): void
    {
        $this->logger->info('Searching for posts');
        $posts = $this->getPostEditUrls();
        $this->logger->info(sprintf('Found %d posts to update', count($posts)));

        foreach($posts as $postUrl) {
            $this->updatePost($postUrl, $searchRegex, $replace);
        }
    }

    private function updatePost(string $postUrl, string $searchRegex, string $replace): void
    {
        $editPage = $this->client->request('GET', $postUrl);

        $form = $editPage
            ->selectButton('Save')
            ->form();

        $content = $form->get('comment_value_noscript')->getValue();
        $content = preg_replace('~'.$searchRegex.'~', $replace, $content, -1, $replacementCount);

        if (!$replacementCount) {
            $this->logger->info(sprintf('Not updating non-matching post `%s`'
                , $editPage->filterXPath('//title')->text()));
            return;
        }

        // Annoyingly PHPBB is adding newlines when I resubmit, try and remove them.
        $content = preg_replace("/(\r?\n){2,}/", "\n\n", $content);

        $form->get('comment_value_noscript')->setValue($content);
        $this->logger->info(sprintf('Updated post `%s`' , $editPage->filterXPath('//title')->text()));
        $this->client->submit($form);
    }

    private function getPostEditUrls(): array
    {
        $crawler = $this->login();
        $link = $crawler->selectLink('Profile')->link();
        $crawler = $this->client->click($link);
        $crawler = $this->client->request('GET', $crawler->getUri() . '/content');
        $link = $crawler->selectLink('Posts')->link();
        $crawler = $this->client->click($link);


        $urls = $crawler->filter('[data-role="tablePagination"] a')
            ->each(function (Crawler $node) {
                return $this->extractPostEditUrls($node->link()->getUri());
            });

        return array_unique(array_merge(...$urls));
    }

    private function extractPostEditUrls($pageUrl)
    {
        return $this->client->request('GET', $pageUrl)
            ->filter('h3 a')
            ->reduce(function (Crawler $node): bool {
                return $this->bbUrlHelper->isCommentUrl($node->link()->getUri());
            })
            ->each(function (Crawler $node) {
                return $this->bbUrlHelper->convertToEditPost($node->link()->getUri());
            });
    }

    public function login(): Crawler
    {
        $this->logger->info(sprintf('Logging in as `%s`, `%s`', $this->username, $this->baseUrl));
        $crawler = $this->client->request('GET', $this->baseUrl.'/login');
        $form = $crawler->selectButton('_processLogin')->form();
        $crawler = $this->client->submit($form, array('auth' => $this->username, 'password' => $this->password));

        try {
            $crawler->selectLink('Profile')->link();
        } catch(\InvalidArgumentException $e) {
            $this->logger->error('Login failed');
            throw new \RuntimeException('Login failed.');
        }

        return $crawler;
    }
}