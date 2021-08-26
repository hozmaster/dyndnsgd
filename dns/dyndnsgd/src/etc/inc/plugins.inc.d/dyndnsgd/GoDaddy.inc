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

require_once("RequesterBase.inc");

class GoDaddy extends RequesterBase
{

    private $ttl = 600;

    public function __construct()
    {
        $this->basApiUrl = 'https://api.godaddy.com/v1/domains/';
        $this->stagingApiUrl = 'https://api.ote-godaddy.com';
    }

    public function supportedRecords(): array
    {
        return ['A', 'AAAA', 'MX', 'TXT', 'CNAME'];
    }

    public function updateA1Records($domain, $keys, $if_ip_addr)
    {
        $this->initCurl();

        $payload = array();

        // for now, will be changed later on.
        $record_type = 'TXT';
        $record_name = 'dyndnsgd';

        $content = date("Y-m-d H:i:s", strtotime("now"));
        $record_object = (object)[
            'data' => $content,
            'ttl' => $this->ttl,
        ];

        array_push($payload, $record_object);

        $url = $this->getBaseUrl() . "/v1/domains/$domain/records/$record_type/$record_name";

        $headers = $this->getHeader($keys['api.key'], $keys['api.secret']);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($this->ch);
        $responseInfo = curl_getinfo($this->ch);
        if ($responseInfo["http_code"] != 200 && $responseInfo["http_code"] != 201) {
            $result = '{ "status" : "failed", "code" : ' . $responseInfo["http_code"] . ' }';
        }

        if (empty($result)) {
            $result = '{ "status" : "ok, "code" : 200 "}';
        }
        curl_close($this->ch);
        return json_decode($result, true);

    }

    private function getBaseUrl(): string
    {
        // forgot staging for while.
        return 'https://api.godaddy.com';
    }

    private function getHeader($api_key, $api_secret): array
    {
        return array(
            "Authorization: sso-key $api_key:$api_secret",
            "Content-Type: application/json",
            "Accept: application/json"
        );
    }

}
