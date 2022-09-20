<?php

/*
 * Copyright (C) 2021 Olli-Pekka Wallin
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

use OPNsense\Goddy\GdDomains;
use OPNsense\Goddy\GdUtils;
use OPNsense\Goddy\Service\GdRecord;

require_once("interfaces.inc");
require_once("util.inc");

$use_test_record = 1;

function goddy_configure(): array
{
    return array(
        'bootup' => array('goddy_configure_do'),
        'local' => array('goddy_configure_do'),
        'newwanip' => array('goddy_configure_do'),
    );
}

function isGoddyEnabled(): bool
{
    global $config;
    return isset($config['OPNsense']['Goddy']['settings']['enabled']) &&
        $config['OPNsense']['Goddy']['settings']['enabled'] == 1;
}

function getDefaultInterfaceName()
{
    global $config;
    return isset($config['OPNsense']['Goddy']['settings']['interface']);

}

function goddy_services()
{
    $services = array();
    if (isGoddyEnabled()) {
        $services[] = array(
            'description' => gettext('GoDaddy specific Dynamic DNS updater'),
            'configd' => array(
                'restart' => array('goddy reload'),
                'start' => array('goddy start')
            ),
            'nocheck' => true,
            'name' => 'goddy',
        );
    }
    return $services;
}

function goddy_cron()
{
    $jobs = array();
    if (dyndns_enabled()) {
        $jobs[]['autocron'] = array('/usr/local/etc/rc.goddy', '11', '1');
    }
    return $jobs;
}

function goddy_syslog()
{
    $logfacilities = array();
    $logfacilities['goddy'] = array(
        'facility' => ['goddy']
    );
    return $logfacilities;
}

function goddy_list()
{
    return array(
        'godaddy' => 'GoDaddy',
        'godaddy-v6' => 'GoDaddy (v6)',
    );
}

function goddy_configure_do()
{
    global $config;

    if (isGoddyEnabled()) {

        $opnsense = $config['OPNsense'];
        $goddy = $opnsense['Goddy'];

        if (empty($goddy)) {
            syslog(LOG_ERR, "Goddy: Internal model structure can't be found, quit now.");
            return -1;
        }

        $api_keys = ['api.key' => $goddy['settings']['api_key'],
            'api.secret' => $goddy['settings']['api_secret']
        ];

        $gd_domains = new GdDomains();
        $domains = $gd_domains->getAllDomains();

        foreach ($domains as $uuid => $domain) {
            if (!$domain['enabled']) {
                continue;   // since domain is not enabled skip to next.
            }
            $ip_type = '';
            switch ($goddy['settings']['check_method']) {
                case 'ip4onlyme':
                    $raw_str = file_get_contents('https://ip4only.me/api/');
                    $ip = preg_split("/\,/", $raw_str)[1];
                    $ip_type = 'ipv4';
                    break;
                case 'ip6onlyme':
                    $raw_str = file_get_contents('https://ip6.me/api/');
                    $content = preg_split("/\,/", $raw_str);
                    $ip_type = strtolower($content[0]);
                    $ip = $content[1];
                    break;
                case 'ipfy4' :
                    //  https://www.ipify.org/
                    $ip = file_get_contents('https://api.ipify.org');
                    $ip_type = 'ipv4';
                    break;
                case 'ipfy6':
                    $ip = file_get_contents('https://api64.ipify.org');
                    $ip_type = 'ipv6';
                    break;
                default:
                case 'if':
                    $ip = getCurrentInterfaceIPAddress(getDomainInterface($domain['interface']));
                    break;
            }
            syslog(LOG_NOTICE, "ip address: " . $ip);
            if ($ip != null) {
                if (strcmp($domain['ip4_address'], $ip) == 0) {
                    syslog(LOG_NOTICE, "ip matches with current one");
                } else {
                    // ipv4 address need to update.
                    if (is_private_ip($ip)) {
                        syslog(LOG_ERR, $domain['domain'] . "goddy: : Unable to update DNS record because interface is connected to private ip range.");
                    } else {
                        $updated = updateA1Record($api_keys, $domain['domain'], $ip, $ip_type);
                        if ($updated) {
                            $gd_domains->updateDomainIPv4Address($uuid, $ip);
                            syslog(LOG_NOTICE, "interface ip address " . $ip);
                            syslog(LOG_NOTICE, "domain ip address " . $domain['ip4_address']);
                        }
                    }
                }
            }
        }

        sleep(1);
    } else {
        syslog(LOG_NOTICE, "Goddy: plugin is disabled");
    }
}

function getCurrentInterfaceIPAddress($interface): string
{
    $interface_real = get_real_interface(strtolower($interface));
    return get_interface_ip($interface_real);
}

function getDomainInterface($interfaces): string|null
{
    $the_interface = null;
    foreach ($interfaces as $code => $interface) {
        if ($interface['selected'] === 1) {
            $the_interface = $interface['value'];
        }
    }
    return $the_interface;
}

function updateA1Record($keys, $domain, $if_ip_addr, $ip_type, $name = '@'): bool
{
    $updated = false;
    if ($ip_type == 'ipv4') {
        $record = 'A';
    } else {
        $record = 'AAAA';
    }

    $data = [
        'domain' => $domain,
        'name' => $name,
        'record' => $record,
        'payload' => $if_ip_addr
    ];

    $record = new GdRecord($keys, $data, 'put');
    $results = $record->doRequest();

    if ($results['status'] == 'ok') {
        $updated = true;
    }

    return $updated;
}
