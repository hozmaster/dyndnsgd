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

namespace OPNsense\DynDNSGD;


class GDService
{
    const REQUEST_OK = 200;

    private $response_code;
    private $data;
    private $production_url = "api.godaddy.com";
    private $staging_url = 'api.ote-godaddy.com';

    public function parse_error_response($rsp)
    {
        if (isset($rsp['code'])) {
            $this->error_code = $rsp['code'];
            $this->message = $rsp['message'];
        }
    }

    public function get_base_url($is_test_url = false)
    {
        $is_test_url == false ? $url = "https://$this->production_url" : $url = "https://$this->staging_url";
        return $url;
    }

    public function gd_parse_response_info($code)
    {
        switch ($code) {
            case 200:
                $status = 'Ok';
                break;
            case 400:
                $status = 'Request was malformed';
                break;
            case 401:
                $status = 'Authentication info not sent or invalid';
                break;
            case 403:
                $status = "Authenticated user is not allowed access";
                break;
            case 422:
                $status = "Limit must have a value no greater than 1000";
                break;
            case 429:
                $status = "Too many requests received within interval";
                break;
            case 500:
                $status = "Internal server error";
                break;
            default:
                $status = "Undefined error occur.";
        }
        return $status;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function get_response_code()
    {
        return $this->response_code;
    }

    public function do_godaddy_get_request($url, $header)
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // Values: GET, POST, PUT, DELETE, PATCH, UPDATE
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $variable);
        //curl_setopt($ch, CURLOPT_POST, true);
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
