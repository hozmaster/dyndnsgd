<?php

namespace OPNsense\Goddy;

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
use OPNsense\Goddy\Service\GdService;

class Worker
{
    public function __construct()
    {
    }

    public function fetchAllUserGDDomains()
    {
        $api_params = GdUtils::getApiKeyAndSecret();

        $myfile = fopen("/tmp/fetchallusergddomains.txt", "w") or die("Unable to open file!");
        $txt = "Key : " . $api_params['api_key'] . "\n";
        fwrite($myfile, $txt);
        $txt = "secret : " . $api_params['api_secret'] . "\n";
        fwrite($myfile, $txt);
        fclose($myfile);

    }

//    private function fetch_all_gd_domains($status = 'ACTIVE')
//    {
//        $path = self::ACCOUNT_CONFIG_PATH;
//        $loaded = $this->loadAccount($path, $this->uuid);
//        if (!$loaded) {
//            GdUtils::log(' Cant find account for given uuid ' . $this->uuid);
//            return false;
//        }
//
//        $base_url = $this->gd_service->get_base_url();
//        $url = "$base_url/v1/domains?statuses=$status";
//        $header = $this->gd_service->getHeader($this->getKey(),$this->getSecretKey() );
//
//        $response_code = $this->gd_service->doGetRequest($url, $header);
//        GdUtils::log("Fetching domains, response : " . $response_code);
//
//        if ($response_code == GdService::REQUEST_OK) {
//            $domains = $this->gd_service->get_data();
//            $gd_domains = new Domains();
//            $c_domains = $gd_domains->getAllDomains();
//            $save_count = 0;
//            foreach ($domains as $domain) {
//                $key = array_search($domain['domain'], array_column($c_domains, 'domain'));
//                if ($key === false) {
//                    $gd_domains->saveNewRecord($this->uuid, $domain);
//                    $save_count ++;
//                }
//            }
//            GdUtils::log("Count of added domains:" . $save_count);
//        } else {
//            GdUtils::log('Request failed with code ' . $response_code . ', ' .
//                $this->gd_service->parseResponseInfo($response_code));
//        }
//        return true;
//    }

}
