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

define("FOO", "something");


class gdDnsUpdater
{
    public $_if;
    private string $_if_addr4;
    private array $_accounts = [];
    private array $_domains = [];
    private array $dnsServiceList = [];
    private string $dnsService = 'gd';
    private $models;
    private $handler;
    private $verboseLogging = false;

    const LOG_PREFIX = "DynDNSGD";

    public function __construct(
        $if4_addr,
        $verboseLogging,
        $models
    )
    {
        $this->_if_addr4 = $if4_addr;
        $this->verboseLogging = $verboseLogging;
        $this->models = $models;
        $this->dnsServiceList = dyndnsgd_list();
    }

    public function checkAndDoDNSUpdate()
    {
        $updater_lck = lock("dyndnsgd_" . LOCK_EX);
        if ($this->verboseLogging) {
            $this->log_message(" updater lock set ");
            $this->log_message(" wan address : " . $this->_if_addr4);
        }

        include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GoDaddy.inc');
        $this->handler = new GoDaddy();

        $this->getDataFromModels();

        if ($this->verboseLogging) {
            $this->log_message(" domain count  : " . count($this->_domains));
            $this->log_message(" account count : " . count($this->_accounts));
        }

        foreach ($this->_domains as $domain) {
            $keys = $this->getAccountKeysForDomain($domain['account_uuid']);
            if ($keys) {
                if ($this->verboseLogging) {
                    $this->log_message("Updating records for domain : " . $domain['domain']);
                }
                $result = $this->handler->updateA1Records($domain['domain'], $keys, $this->_if_addr4);
                if ($result['status'] == 'failed') {
                    $this->log_message(" Update operation failed for domain : " . $domain['domain']);
                } else {
                    $this->log_message(" Record updated :" . $domain['domain']);
                }
            } else {
                if ($this->verboseLogging) {
                    $this->log_message('No account/API keys cant found for domain ' . $domain['domain']);
                }
            }
            unlock($updater_lck);
            if ($this->verboseLogging) {
                $this->log_message(" updater lock released. ");
            }
        }
    }


    public function fetchDomains($defs_domains): bool
    {
        if (isset($defs_domains['domains'])) {
            foreach ($defs_domains['domains']['domain'] as $domain) {
                if ($domain['enabled']) {
                    $temp['domain'] = $domain['domain'];
                    $temp['account_uuid'] = $domain['account'];
                    $temp['interface'] = $domain['interface'];
                    $temp['at_record'] = $domain['at_record'];
                    array_push($this->_domains, $temp);
                }
            }
        } else {
            // TODO : No data for dyndnsgd. Exit with false and log error code.
            return false;
        }
        if (!count($this->_domains)) {
            return false;
        }

        return true;
    }

    public function fetchAccounts($defs_acc_s): bool
    {
        if (isset($defs_acc_s['accounts'])) {
            $accs = $defs_acc_s['accounts'];
            foreach ($accs as $acc) {
                if ($acc['enabled']) {
                    $temp['api.key'] = $acc['api_key'];
                    $temp['api.secret'] = $acc['api_secret'];
                    $temp['uuid'] = $acc['@attributes']['uuid'];
                    $temp['test'] = $acc['staging'];
                    array_push($this->_accounts, $temp);
                }
            }
        } else {
            // TODO : No data for dyndnsgd. Exit with false and log error code
            return false;
        }
        if (!count($this->_accounts)) {
            return false;
        }
        return true;
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

    private function log_message($msg)
    {
        log_error(self::LOG_PREFIX . ' ' . $msg);
    }

    private function getDataFromModels()
    {
        if (!count($this->models)) {
            // TODO : No data for dyndnsgd. Exit with error code in the log
            syslog(LOG_NOTICE, "DynDNSGD: Internal model problems");
            return;
        }

        if (!$this->fetchAccounts($this->models['accounts'])) {
            // TODO : No data for dyndnsgd. Exit with error code in the log
            syslog(LOG_NOTICE, "DynDNSGD: No valid accounts.");
            return;
        }

        if (!$this->fetchDomains($this->models['domains'])) {
            // TODO : No data for dyndnsgd. Exit with error code in the log
            syslog(LOG_NOTICE, "DynDNSGD: Domains are not yet fetched.");
            return;
        }
    }
}
