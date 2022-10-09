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

class Worker extends GdService
{
    public function fetchAllUserGDDomains($api_key, $api_secret): array
    {
        if (!strlen($api_key) || !strlen($api_secret)) {
            $response = array("results" => "failed",
                "message" => "Missing or invalid api_key and/or api_secret"
            );

        } else {
            $response = array("results" => "success",
                "message" => $this->fetchAllGDDomains($api_key, $api_secret));

        }
        return $response;
    }

    // Try to found dns record and update domain ip address.
    public function updateIPv4AddressByDNSLookup($uuid): array
    {
        $message = "Domain record can't be found.";
        $domains = new GdDomains();
        $domainObj = $domains->getDomain($uuid);
        if ($domainObj) {
            $dns = dns_get_record($domainObj->domain, DNS_A);
            if (empty($dns)) {
                // Don't care about if it's empty, just clear existing one and let
                // update routines to fix the issue.
                $domains->updateDomainIPv4Address($uuid, "");
                $message = "DNS record can't be found. Local ip address cleared ";
            } else {
                $domains->updateDomainIPv4Address($uuid, $dns[0]['ip']);
                $message = "Ip address updated with resolved ip address.";
            }
        }
        return array("message" => $message);
    }

    private function fetchAllGDDomains($api_key, $api_secret): string
    {
        $domainCount = 0;
        $base_url = $this->get_base_url();
        $url = "$base_url/v1/domains?statuses=ACTIVE";
        $header = $this->getHeader($api_key, $api_secret);
        $response_code = $this->doGetRequest($url, $header);
        if ($response_code == GdService::REQUEST_OK) {
            $domains = $this->get_data();
            $gd_domains = new GdDomains();
            $c_domains = $gd_domains->getAllDomains();
            $saveCount = 0;
            foreach ($domains as $domain) {
                $key = in_array($domain['domain'], array_column($c_domains, 'domain'));
                if ($key === false) {
                    $gd_domains->saveNewRecord($domain);
                    $saveCount++;
                }
                $domainCount++;
            }

            if ($saveCount) {
                $message = "Request passed. Amount of domains added: " . $saveCount;
            } else {
                $message = "Request passed. No new domains found. ";
            }
        } else {
            $message = $this->parseResponseInfo($response_code);
        }
        return $message;
    }

}
