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

use OPNsense\Goddy\GDDatabase;
use OPNsense\Goddy\GdUtils;
use OPNsense\Goddy\Service\GdRecord;

class gdDnsUpdater
{
    private $_if;       // index of wan address
    private string $_if_addr4;
    private bool $_useIPv6;
    private array $_accounts = [];
    private array $_domains = [];
    private bool $verboseLogging = false;
    private array $cachedRecords = [];
    private $gdDb = null;

    public function __construct(
        $if,
        $verboseLogging,
        $accounts,
        $domains
    )
    {
        $this->verboseLogging = $verboseLogging;

        $this->_useIPv6 = false;
        $this->_if = $if;
        $this->_if_addr4 = $this->checkIPAddress();
        $this->_accounts = $accounts;
        $this->_domains = $domains;

        $this->gdDb = new GDDatabase();

    }

    public function checkAndDoDNSUpdate()
    {
        $updater_lck = lock("goddy_" . LOCK_EX);

        $this->log_message(" updater lock set ");
        $this->log_message(" wan address : " . $this->_if_addr4);

        // Ensure that we were able to look up the IP
        if (!is_ipaddr($this->_if_addr4)) {
            $this->log_message("There was an error trying to determine the public IP for interface - ({$this->_if}). Probably interface is not a WAN interface.", true);
            unlock($updater_lck);
            return;
        }

        $this->log_message(" Count of domains  : " . count($this->_domains));

        $this->cachedRecords = $this->gdDb->getCachedDomainRecords();

        foreach ($this->_domains as $domain) {
            if ($domain['enabled']) {
                // key property uuid of used account
                $keys = $this->getAccountKeysForDomain($domain['account']);
                $domainUuid = $domain['@attributes']['uuid'];
                if ($keys) {
                    $cachedRecord = $this->getCurrentIPAddress($domainUuid);
                    if (!$cachedRecord || $cachedRecord['ipv4_address'] != $this->_if_addr4) {
                        $name = "@";
                        $type = 'A';
                        $this->log_message("Updating records for domain : " . $domain['domain']);
                        $result = $this->updateA1Records($domain['domain'], $keys, $name, $this->_if_addr4);
                        if ($result['status'] != 'failed') {
                            $db_result = $this->gdDb->setCachedIpForDomain($domainUuid, $type, $name, $this->_if_addr4, "");
                            $this->log_message("[{$domain['domain']}]: '@'-Record updated, db: {$db_result} ", true);
                        } else {
                            $this->log_message("[{$domain['domain']}]: Unable to update record : " . $result['reason']);
                        }

                    } else {
                        $this->log_message("[{$domain['domain']}]: No need to update a ip address to service");
                    }
                } else {
                    $this->log_message("No keys can't be from used account. Skip domain.");
                }
            }
        }

        unlock($updater_lck);
        $this->log_message(" updater lock released. ");
    }

    private function getCurrentIPAddress($d_uuid): array
    {
        $data = [];
        $result = array_search($d_uuid, array_column($this->cachedRecords, 'uuid'), true);
        if ($result !== false) {
            $data = [
                'ipv4_address' => $this->cachedRecords[$result]['ipv4_address'],
                'ipv6_address' => $this->cachedRecords[$result]['ipv6_address'],
            ];
        }
        return $data;
    }

    private function getAccountKeysForDomain($uuid): ?array
    {
        $keys = null;
        foreach ($this->_accounts as $account) {
            $is_match = $account['@attributes']['uuid'] == $uuid;
            if ($is_match) {
                $keys = [
                    'api.key' => $account['api_key'],
                    'api.secret' => $account['api_secret']
                ];
                break;
            }
        }
        return $keys;
    }

    private function checkIPAddress()
    {
        $ip_address = get_dyndns_ip($this->_if, $this->_useIPv6 ? 6 : 4);
        if (!is_ipaddr($ip_address)) {
            $this->log_message("IP address could not be extracted", true);
            $ip_address = 0;
        } else {
            $this->log_message("{$ip_address} extracted");
            $this->_dnsIP = $ip_address;
        }
        return $ip_address;
    }

    private function log_message($msg, $log_always = false)
    {
        $do_log = false;
        if ($log_always || $this->verboseLogging) {
            $do_log = true;
        }
        if ($do_log) {
            GdUtils::log_error($msg);
        }
    }

    public function updateA1Records($domain, $keys, $name, $if_ip_addr): array
    {
        $data = [
            'domain' => $domain,
            'name' => $name,
            'type' => 'A',
            'payload' => $if_ip_addr,
        ];

        $record = new GdRecord($keys, $data, 'put');
        $record->doRequest();
        return $record->getResponse();

    }

}
