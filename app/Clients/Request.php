<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\ResponseInterface;

abstract class Request
{

    public $account = [];
    public $baseUrl = '';
    public $type = '';
    public $reportUrl = '';

    public $client = null;
    public $jar = null;

    abstract function login(): bool;

    abstract function isLogin(): bool;

    abstract function loginStatus(): bool;

    public function __construct($account, $url)
    {
        $this->account = $account;
        $this->type = filter_filename($url);
        $this->baseUrl = $url;

        if (!$this->loginStatus())
            throw new \Exception("登录失败");
    }


    public function cookiePath($name = null): string
    {
        $name = $name ?: $this->account['username'];
        if (!$name)
            throw new \Exception("没有找到对应的用户名");

        return Storage::disk('public')->path("crm_cookie/{$this->type}-{$name}-cookies.json");
    }

    public function getAccountCookie($name = null)
    {
        if (!$this->jar) {
            $path = $this->cookiePath($name);

            if ($path) {
                $this->jar = new FileCookieJar($path, true);
            } else {
                return false;
            }
        }
        return $this->jar;
    }

    public function createClient($jar = true): Client
    {
        $ip = long2ip(mt_rand());
        $urlArr = parse_url($this->baseUrl);

        return new Client([
            'cookies' => $jar,
            'base_uri' => $this->baseUrl,
            'verify' => false,
            'http_errors' => false,
            'timeout' => 60,
            'read_timeout' => 60,
            'connect_timeout' => 60,
            'headers' => [
                'Host' => $urlArr['host'],
                'CLIENT-IP' => $ip,
                'X-FORWARDED-FOR' => $ip,
                'Origin' => $this->baseUrl,
                'Referer' => $this->baseUrl,
                'X-Requested-With' => 'XMLHttpRequest',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36',
            ],
        ]);
    }

    /**
     * 获取 Client
     * @return Client
     */
    public function getClient($name = null): Client
    {
        if (!$this->client) {
            $this->client = $this->createClient($this->getAccountCookie($name));
        }

        return $this->client;
    }

    /**
     * @throws GuzzleException
     */
    public function get($url, $options = []): ResponseInterface
    {
        $client = $this->getClient();
        return $client->get($url, $options);
    }

    /**
     * @throws GuzzleException
     */
    public function post($url, $options): ResponseInterface
    {
        $client = $this->getClient();
        return $client->post($url, $options);
    }


}
