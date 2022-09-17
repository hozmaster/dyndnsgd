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
require_once('plugins.inc.d/goddy/gdDnsUpdater.inc');

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

        foreach ($domains as $key => $domain) {
            if (!$domain['enabled']) {
                syslog(LOG_NOTICE, "Domain is not enabled :  " . $domain['domains']);
                continue;   // since domain is not enabled skip to next.
            }
            $s_interface = getDomainInterface($domain['interface']);
            if ($s_interface != null) {
                $interface_real = get_real_interface(strtolower($s_interface));
                $ip = get_interface_ip($interface_real);
                if (strcmp($domain['ip4_address'], $ip) == 0) {
                    syslog(LOG_NOTICE, "ip matches with current one");
                } else {
                    // ipv4 address need to update.
                    if (is_private_ip($ip)) {
                        syslog(LOG_ERR, $domain['domain'] . ": Unable to update DNS record because interface is connected to private ip range.");
                    } else {
                        $updated = updateA1Record($api_keys, $domain['domain'], $ip);
                        if ($updated) {
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

function getCurrentInterfaceIPAddress ()
{
    //$ip = file_get_contents('https://api.ipify.org');
    //echo "My public IP address is: " . $ip;
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

function updateA1Record($keys, $domain, $if_ip_addr, $name = '@'): bool
{
    $updated = false;
    // TODO  Fow now. Change this to production or replace with test parameter
    $data = [
        'domain' => $domain,
        'name' => '_foorecttest',
        'record' => 'TXT',          // 'record' => 'A',
        'payload' => $if_ip_addr . '_' . time()
    ];

    $record = new GdRecord($keys, $data, 'put');
    $results = $record->doRequest();

    if ($results['status'] == 'ok') {
        $updated = true;
    }

    return $updated;
}
