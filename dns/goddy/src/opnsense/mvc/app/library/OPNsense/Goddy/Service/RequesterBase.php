<?php

namespace OPNsense\Goddy\Service;

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

class RequesterBase
{
    protected string $_UserAgent = 'User-Agent: goddy/1.0';
    protected string $productionApiUrl;
    protected string $stagingApiUrl;
    protected $ch;
    protected bool $ipv6Supported;

    public function updateARecords($dnsHost, $recordType, $dnsDomain)
    {
    }

    protected function initCurl()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->_UserAgent);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($this->ch, CURLOPT_INTERFACE, $this->_dnsRequestIfIP);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);

    }

    public function supportedRecords(): array
    {
        return ['A', 'AAAA', 'MX', 'TXT', 'CNAME'];
    }

    public function parseResponseInfo($code): string
    {
        switch ($code) {
            case 200:
                $status = 'Ok';
                break;
            case 400:
                $status = 'Request was malformed. Please contact application developer.';
                break;
            case 401:
                $status = 'Authentication credentials are invalid';
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
                $status = "Internal server error at GoDaddy";
                break;
            default:
                $status = "Undefined error occur.";
        }
        return $status;
    }

}
