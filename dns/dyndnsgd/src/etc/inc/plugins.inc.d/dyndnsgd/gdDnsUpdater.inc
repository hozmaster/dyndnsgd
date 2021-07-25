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

class gdDnsUpdater
{
    public $_if;
    public $_if_addr4;
    private array $_accounts = [];
    private array $_domains = [];
    private array $dnsServiceList = [];
    private string $dnsService = 'gd';

    public function __construct()
    {
        $this->dnsServiceList = dyndns_gd_list();
    }

    public function checkAndDoDNSUpdate()
    {
        $updater_lck = lock("dyndns_gd_" . LOCK_EX);
        switch ($this->dnsService){
            case 'gd':
                include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GDService.inc');
                $obj = new GDService();
            break;
        }

        foreach ($this->_domains as $domain) {

        }

        var_dump($obj->supportedRecords());
        unlock($updater_lck);
    }

    public function addDomains($defs_domains): bool
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

    public function addAccounts($defs_acc_s): bool
    {
        if (isset($defs_acc_s['accounts'])) {
            $accs = $defs_acc_s['accounts'];
            foreach ($accs as $acc) {
                if ($acc['enabled']) {
                    $temp['key'] = $acc['key'][0];
                    $temp['secret'] = $acc['secret_key'];
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

}
