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

use OPNsense\goddy\GDDatabase;

class gdDnsUpdater
{
    private $_if;
    private string $_if_addr4;
    private bool $_useIPv6;
    private array $_accounts = [];
    private array $cached_records = [];
    private array $_domains = [];
    private string $dnsService = 'gd';
    private $models;
    private $handler;
    private $verboseLogging = false;
    private $gdDb = null;

    const LOG_PREFIX = "Goddy";

    public function __construct(
        $if,
        $verboseLogging,
        $models
    )
    {
        $this->_useIPv6 = false;
        $this->_if = $if;
        $this->_if_addr4 = $this->checkIPAddress();
        $this->verboseLogging = $verboseLogging;
        $this->models = $models;
        $this->gdDb = new GDDatabase();
        $this->cached_records = $this->gdDb->getCachedDomainRecords();
    }

    public function checkAndDoDNSUpdate()
    {
        $updater_lck = lock("goddy_" . LOCK_EX);
        if ($this->verboseLogging) {
            $this->log_message(" updater lock set ");
            $this->log_message(" wan address : " . $this->_if_addr4);
        }

        // Ensure that we were able to lookup the IP
        if (!is_ipaddr($this->_if_addr4)) {
            $this->log_message("There was an error trying to determine the public IP for interface - ({$this->_if}). Probably interface is not a WAN interface.", true);
            unlock($updater_lck);
            return;
        }

        include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GoDaddy.inc');
        $this->handler = new GoDaddy();

        $this->getDataFromModels();

        $this->log_message(" domain count  : " . count($this->_domains));
        $this->log_message(" account count : " . count($this->_accounts));

        foreach ($this->_domains as $domain) {
            $keys = $this->getAccountKeysForDomain($domain['account_uuid']);
            $cached_record= $this->getCurrentIPAddress($domain['uuid']);
            if ($keys && $cached_record['ipv4_address'] != $this->_if_addr4) {
                $name = "@";
                $this->log_message("Updating records for domain : " . $domain['domain']);
                $result = $this->handler->updateA1Records($domain['domain'], $keys, $name, $this->_if_addr4);
                if ($result['status'] != 'failed') {
                    $type = 'A';
                    $this->gdDb->setCachedIpForDomain($domain['uuid'], $type, $name, $this->_if_addr4, "");
                    $this->log_message("[{$domain['domain']}]: '@'-Record updated ", true);
                } else {
                    $this->log_message("[{$domain['domain']}]: Unable to update record : " . $result['reason'], true);
                }
            } else {
                $this->log_message("[{$domain['domain']}]: No need to update a ip address to service");
            }
            unlock($updater_lck);
            $this->log_message(" updater lock released. ");
        }
    }

    private function getDomainData($defs_domains): bool
    {
        if (isset($defs_domains['domains'])) {
            foreach ($defs_domains['domains']['domain'] as $domain) {
                if ($domain['enabled']) {
                    $temp['domain'] = $domain['domain'];
                    $temp['uuid'] = $domain['@attributes']['uuid'];
                    $temp['account_uuid'] = $domain['account'];
                    $temp['interface'] = $domain['interface'];
                    $temp['at_record'] = $domain['at_record'];
                    array_push($this->_domains, $temp);
                }
            }
        } else {
            // TODO : No data for goddy. Exit with false and log error code.
            return false;
        }
        if (!count($this->_domains)) {
            return false;
        }

        return true;
    }

    private function fetchAccounts($defs_acc_s): bool
    {
        if (!isset($defs_acc_s['accounts']['account'])) {
            return false;
        }
        $accs = $defs_acc_s['accounts']['account'];
        foreach ($accs as $acc) {
            if ($acc['enabled']) {
                $temp['api.key'] = $acc['api_key'];
                $temp['api.secret'] = $acc['api_secret'];
                $temp['uuid'] = $acc['@attributes']['uuid'];
                $temp['test'] = $acc['staging'];
                array_push($this->_accounts, $temp);
            }
        }
        if (!count($this->_accounts)) {
            return false;
        }
        return true;
    }

    private function getCurrentIPAddress($d_uuid): array
    {
        $data = [];
        $result = array_search($d_uuid, array_column($this->cached_records, 'uuid'), true);
        if ($result !== false) {
            $data = [
                'ipv4_address' => $this->cached_records[$result]['ipv4_address'],
                'ipv6_address' => $this->cached_records[$result]['ipv6_address'],
            ];
        }
        return $data;
    }

    private function getAccountKeysForDomain($uuid): ?array
    {
        $result = array_search($uuid, array_column($this->_accounts, 'uuid'), true);
        if ($result !== false) {
            $keys = [
                'api.key' => $this->_accounts[$result]['api.key'],
                'api.secret' => $this->_accounts[$result]['api.secret']
            ];
        } else {
            $keys = null;
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
            log_error(self::LOG_PREFIX . ' ' . $msg);
        }
    }

    private function getDataFromModels()
    {
        if (!count($this->models)) {
            // TODO : No data for goddy. Exit with error code in the log
            syslog(LOG_NOTICE, "Goddy: Internal model problems");
            return;
        }

        $accounts = $this->models['accounts'];
        if (!$this->fetchAccounts($accounts)) {
            // TODO : No data for goddy. Exit with error code in the log
            syslog(LOG_NOTICE, "Goddy: No valid accounts.");
            return;
        }

        if (!$this->getDomainData($this->models['domains'])) {
            // TODO : No data for goddy. Exit with error code in the log
            syslog(LOG_NOTICE, "Goddy: Suitable domains not found.");
            return;
        }
    }
}