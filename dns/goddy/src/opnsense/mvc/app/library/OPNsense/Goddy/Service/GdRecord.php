<?php

/*
 * Copyright (c) 2021, Olli-Pekka Wallin
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\Goddy\Service;

use OPNsense\Goddy\GdUtils;

/**
 *
 */
class GdRecord
{
    //Requested token
    const theApiUrl = 'https://api.godaddy.com';
    const defaultTtl = 600;
    const requestTimeout = 60;
    //Type of request
    private string $requestType;
    //curl example
    private $curl;
    public $status;
    private array $data;
    private array $headers = array();
    private array $keys;

    /**
     * @param array $keys
     * @param array $data
     * @param string $requestType
     */
    public function __construct(array $keys = [], array $data = [], string $requestType = 'put')
    {
        $this->keys = $keys;
        $this->requestType = strtolower($requestType);

        $this->data = $data;

        try {
            if (!$this->curl = curl_init()) {
                throw new exception ('curl initialization error: ');
            };
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, self::requestTimeout);
    }

    /**
     * Set record information to be ready update record information to the service.
     * @return void
     */
    public function put()
    {
        // Path info mode
        $ttl = self::defaultTtl;
        if (!empty($this->data['ttl'])) {
            $ttl = $this->data['ttl'];
        }

        $domain = $this->data['domain'];
        $type = $this->data['record'];
        $name = $this->data['name'];

        $url = self::theApiUrl . "/v1/domains/$domain/records/$type/$name";

        $payload = (object)[
            'data' => $this->data['payload'],
            'ttl' => $ttl,
        ];

        $final_data[] = $payload;

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($final_data));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
    }

    /**
     * [delete delete resource]
     * Delete delete resource
     * @return void
     */
    public function delete(): void
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    public function supportedRecords(): array
    {
        return ['A', 'AAAA', 'MX', 'TXT', 'CNAME'];
    }

    /**
     *[dorequest execute send request]
     * @return [type] [description]
     */
    public function doRequest(): array
    {
        //Send header message
        $this->setHeader();
        //Send request by
        switch ($this->requestType) {
            case 'post':
                $this->post();
                break;
            case 'put':
                $this->put();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
                break;
        }
        //Execute curl request
        curl_exec($this->curl);
        //Get curl execution status information
        return $this->getResponse();
    }

    /**
     *Set the header information
     */
    private function setHeader()
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers());
    }

    /**
     *Get status information in curl
     */
    public function getInfo()
    {
        return curl_getinfo($this->curl);
    }

    /**
     *Close curl connection
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }

    private function headers(): array
    {
        $api_key = $this->keys['api.key'];
        $api_secret = $this->keys['api.secret'];
        return array(
            "Authorization: sso-key $api_key:$api_secret",
            "Content-Type: application/json",
            "Accept: application/json"
        );
    }

    public function getResponse(): array
    {
        $result = [];
        $info = curl_getinfo($this->curl);
        switch ($info['http_code']) {
            case 200:
            case 201:
                $result['status'] = 'ok';
                if ($this->requestType == 'put') {
                    $result['reason'] = 'Record updated.';
                } else {
                    $result['reason'] = 'Operation done successful.';
                }
                break;
            case 400:
            case 401:
            case 403:
            case 404:
            case 422;
            case 429:
                {
                    $result['status'] = 'failed';
                    $result['reason'] = $info['message'];
                }
                break;
            case 500:
            case 504:
                {
                    $result['status'] = 'failed';
                    $result['reason'] = 'Unable to contact to the service provider';
                }
                break;
            default:
                {
                    $result['status'] = 'failed';
                    $result['reason'] = 'Undefined error ' . $info['http_code'] . ' error occur';
                }
                break;

        }
        return $result;
    }

}
