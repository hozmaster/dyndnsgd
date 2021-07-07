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
        $this->gd_service = new GDService();
    }

    public function fetch_all_domains()
    {
        $status = 'ACTIVE';
        $this->fetch_all_gd_domains($status);
    }

    private function fetch_all_gd_domains($status = 'ACTIVE')
    {
        $path = self::ACCOUNT_CONFIG_PATH;
        $loaded = $this->loadAccount($path, $this->uuid);
        if (!$loaded) {
            GdUtils::log(' Cant find account for given uuid ' . $this->uuid);
            return false;
        }

        $base_url = $this->gd_service->get_base_url();
        $url = "$base_url/v1/domains?statuses=$status";
        // set your key and secret
        $api_key = $this->getKey();
        $api_secret = $this->getSecretKey();
        $header = array(
            "Authorization: sso-key $api_key:$api_secret"
        );

        $response_code = $this->gd_service->do_godaddy_get_request($url, $header);

        if ($response_code == GDService::REQUEST_OK) {
            $domains = $this->gd_service->get_data();
            $gd_domains = new GDDomains();
            foreach ($domains as $domain) {
                // Verify at first existing record
                $gd_domains->saveNewRecord($this->uuid, $domain);
            }

        } else {
            GdUtils::log('Request failed with code ' . $response_code . ', ' .
                $this->gd_service->gd_parse_response_info($response_code));

        }
        return true;

    }

}
