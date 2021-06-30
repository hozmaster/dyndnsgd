<?php

namespace OPNsense\DynDNSGD;

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

use OPNsense\Core\Config;

class Worker extends Common
{
    public function __construct($uuid)
    {
        $this->uuid = $uuid;

        //
        //        $dms = $this->curl_get_domains_request($api_key, $api_secret);
        //        // check if error code
        //        if (isset($domains['code'])) {
        //            $msg = explode(":", $domains['message']);
        //            echo 'error code ' . $msg . PHP_EOL;
        //        } else {
        //            foreach ($dms as $dm) {
        //                echo 'Domain : ' . $dm['domain'] . ', expires ' . $dm['expires'] . PHP_EOL;
        //            }
        //        }
    }

    public function some_empty_method()
    {
        $path = self::ACCOUNT_MODEL_PATH;
        $loaded = $this->loadAccount($path, $this->uuid);
        if ($loaded) {
            return $this->getName();
        } else {
            return $this->uuid;
        }
    }

    public function curl_get_domains_request($status = 'ACTIVE')
    {
        $url = "https://api.godaddy.com/v1/domains?statuses=$status";
        // set your key and secret
        $header = array(
            "Authorization: sso-key $this->api_key:$this->api_secret"
        );
        return $this->do_godaddy_get_request($url, $header);
    }

}
