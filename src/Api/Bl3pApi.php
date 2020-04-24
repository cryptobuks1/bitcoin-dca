<?php

declare(strict_types=1);

namespace Jorijn\Bl3pDca\Api;

/**
 * @source https://github.com/BitonicNL/bl3p-api/blob/master/examples/php/example.php
 */
class Bl3pApi
{
    /** @var string */
    private $publicKey;
    /** @var string */
    private $privateKey;
    /** @var string */
    private $url;

    /**
     * Set the url to call, the public key and the private key.
     *
     * @param string $url        Url to call (https://api.bl3p.eu)
     * @param string $publicKey  Your Public API key
     * @param string $privateKey Your Private API key
     */
    public function __construct(string $url, string $publicKey, string $privateKey)
    {
        $this->url = $url;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * To make a call to BL3P API.
     *
     * @param string $path   path to call
     * @param array  $params parameters to add to the call
     *
     * @return array result of call
     *
     * @throws \Exception
     */
    public function apiCall($path, $params = []): array
    {
        // generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
        $mt = explode(' ', microtime());
        $params['nonce'] = $mt[1].substr($mt[0], 2, 6);

        // generate the POST data string
        $post_data = http_build_query($params, '', '&');
        $body = $path.chr(0).$post_data;

        // build signature for Rest-Sign
        $sign = base64_encode(hash_hmac('sha512', $body, base64_decode($this->privateKey), true));

        // combine the url and the desired path
        $fullpath = $this->url.$path;

        // set headers
        $headers = [
            'Rest-Key: '.$this->publicKey,
            'Rest-Sign: '.$sign,
        ];

        // build curl call
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT,
            'Mozilla/4.0 (compatible; BL3P PHP client; '.PHP_OS.'; PHP/'.PHP_VERSION.')');
        curl_setopt($ch, CURLOPT_URL, $fullpath);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // execute curl request
        $res = curl_exec($ch);

        // throw exception with additional information when curl request returns false
        if (false === $res) {
            throw new \Exception('API request failed: Could not get reply from API: '.curl_error($ch));
        }

        // close curl connection
        curl_close($ch);

        // convert json into an array
        $result = json_decode($res, true);

        // check json convert result and throw an exception if invalid
        if (!$result) {
            throw new \Exception('API request failed: Invalid JSON-data received: '.substr($res, 0, 100));
        }

        if (!array_key_exists('result', $result)) {
            //note that data now is the first element in the array.
            $result['data'] = $result;
            $result['result'] = 'success';

            //remove all the keys in $result except 'result'  and 'data'
            return array_intersect_key($result, array_flip(['result', 'data']));
        }

        //check returned result of call, if not success then throw an exception with additional information
        if ('success' !== $result['result']) {
            if (!isset($result['data']['code']) || !isset($result['data']['message'])) {
                throw new \Exception(sprintf('Received unsuccessful state, and additionally a malformed response: %s', var_export($result['data'], true)));
            }

            throw new \Exception(sprintf('API request unsuccessful: [%s] %s', $result['data']['code'], $result['data']['message']));
        }

        return $result;
    }
}