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

class GdService extends RequesterBase
{
    const REQUEST_OK = 200;

    private $response_code;
    private $data;
    private string $production_url = "api.godaddy.com";
    private string $staging_url = 'api.ote-godaddy.com';
    private string $ttl = '500';

    public function parse_error_response($rsp)
    {
        if (isset($rsp['code'])) {
            $this->error_code = $rsp['code'];
            $this->message = $rsp['message'];
        }
    }

    public function get_base_url($is_test_url = false): string
    {
        !$is_test_url ? $url = "https://$this->production_url" : $url = "https://$this->staging_url";
        return $url;
    }

    public function getHeader($api_key, $api_secret): array
    {
        return array(
            "Authorization: sso-key $api_key:$api_secret",
            "Content-Type: application/json",
            "Accept: application/json"
        );
    }

    public function get_data()
    {
        return $this->data;
    }

    public function get_response_code()
    {
        return $this->response_code;
    }

    /*
     * Update IPv4 address of the domain to service
     *
     * @param keys          Api credentials in the array
     * @param domain        Name of the domain
     * @param recordName    Name of the record ( @ )
     * @param ipv4Addr      Ipv4 address
     * @param record type   Record type ('A' or 'AA')
     *
     * @param \Exception $e  Another parameter description.
     *
     *  @return array
    */
    public function doUpdateRecord($keys, $domain, $recordName, $ipv4Addr, $recordType = 'A'): array
    {
        $this->initCurl();
        $payload = array();

        // for now just IPv4, it might be changed later on.
        $record_object = (object)[
            'data' => $ipv4Addr,
            'ttl' => $this->ttl,
        ];

        $payload[] = $record_object;

        $url = $this->getBaseUrl() . "/v1/domains/$domain/records/$recordType/$recordName";
        $headers = $this->getHeader($keys['api.key'], $keys['api.secret']);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($this->ch);
        $info = curl_getinfo($this->ch);
        $response = [];
        $code = $info['http_code'];
        if ($code != 200 && $code != 201) {
            $data = json_decode($result, true);
            $response['status'] = 'failed';
            $response['code'] = $code;
            $response['reason'] = $data['message'];
        } else {
            $response['status'] = 'ok';
            $response['code'] = $code;
            $response['reason'] = "";
        }

        curl_close($this->ch);
        return $response;
    }

    public function doGetRequest($url, $header)
    {
        //open connection
        $ch = curl_init();
        $timeout = 60;
        //set the url and other options for curl
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // Values: GET, POST, PUT, DELETE, PATCH, UPDATE
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //execute call and return response data.
        $result = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        $this->response_code = $responseInfo["http_code"];
        //close curl connection
        curl_close($ch);
        // decode and return the json response
        $this->data = json_decode($result, true);
        return $this->response_code;
    }

}
