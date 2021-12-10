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

class gdDnsUpdater
{
    private $_if;
    private string $_if_addr4;
    private bool $_useIPv6;
    private array $_accounts = [];
    private array $_domains = [];
    private $handler;
    private bool $verboseLogging = false;
    private array $cachedRecords = [];
    private $gdDb = null;

    const LOG_PREFIX = "Goddy";

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

        include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GoDaddy.inc');
        $this->handler = new GoDaddy();

        $this->log_message(" Count of domains  : " . count($this->_domains));
        $this->log_message(" Count of accounts : " . count($this->_accounts));

        $this->cachedRecords = $this->gdDb->getCachedDomainRecords();

        foreach ($this->_domains as $domain) {
            if ($domain['enabled']) {
                // key property uuid of used account
                $keys = $this->getAccountKeysForDomain($domain['account']);
                $cachedRecord = $this->getCurrentIPAddress($domain['uuid']);
                if (!$cachedRecord || $cachedRecord['ipv4_address'] != $this->_if_addr4) {
                    $name = "@";
                    $type = 'A';
                    $this->log_message("Updating records for domain : " . $domain['domain']);
                    $result = $this->handler->updateA1Records($domain['domain'], $keys, $name, $this->_if_addr4);
                    if ($result['status'] != 'failed') {
                        $this->gdDb->setCachedIpForDomain($domain['uuid'], $type, $name, $this->_if_addr4, "");
                        $this->log_message("[{$domain['domain']}]: '@'-Record updated ", true);
                    } else {
                        $this->log_message("[{$domain['domain']}]: Unable to update record : " . $result['reason']);
                    }

                }
                else {
                    $this->log_message("[{$domain['domain']}]: No need to update a ip address to service");
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

}
