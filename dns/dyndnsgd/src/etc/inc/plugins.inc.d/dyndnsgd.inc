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

require_once("interfaces.inc");
require_once('plugins.inc.d/dyndnsgd/gdDnsUpdater.inc');


function dyndns_gd_configure()
{
    return array(
        'bootup' => array('dyndns_gd_configure_do'),
        'local' => array('dyndns_gd_configure_do'),
        'newwanip' => array('dyndns_gd_configure_do:2'),
    );
}

function dyndns_gd_enabled(): bool
{
    global $config;
    return isset($config['OPNsense']['DynDNSGD']['settings']['enabled']) &&
        $config['OPNsense']['DynDNSGD']['settings']['enabled'] == 1;
}

function dyndns_gd_services()
{
    // TODO : remove me
    syslog(LOG_NOTICE, "DynDNSGD: dyndns_gd_services()");
    global $config;

    $services = array();

    if (dyndns_gd_enabled()) {
        $services[] = array(
            'description' => gettext('Dynamic DNS'),
            //            'configd' => array(
            //                'restart' => array('dyndns_gd reload'),
            //            ),
            'nocheck' => true,
            'name' => 'dyndns_gd',
        );
    }
    return $services;
}
function dyndns_gd_list()
{
    // TODO : remove me
    syslog(LOG_NOTICE, "DynDNSGD: dyndns_gd_list()");
    return array(
        'godaddy' => 'GoDaddy',
        'godaddy-v6' => 'GoDaddy (v6)',
    );
}

//
//function dyndns_gd_cron()
//{
//    // TODO : remove me
//    syslog(LOG_NOTICE, "DynDNSGD: dyndns_gd_cron()");
//    $jobs = array();
//    if (dyndns_enabled()) {
//        $jobs[]['autocron'] = array('/usr/local/etc/rc.dyndns_gd', '11', '1');
//    }
//
//    return $jobs;
//}

function dyndns_gd_configure_do($verbose = false, $int = '')
{
    global $config;
    if (dyndns_gd_enabled()) {

        $dyndns_gd = $config['OPNsense']['DynDNSGD'];
        $updater = new gdDnsUpdater();

        if (!count($dyndns_gd)) {
            // TODO : No data for dyndnsgd. Exit with error code in the log
            return;
        }

        if (!$updater->addAccounts($dyndns_gd['Accounts'])) {
            // TODO : No data for dyndnsgd. Exit with error code in the log
            return;
        }

        if (!$updater->addDomains($dyndns_gd['domains'])) {
            // TODO : No data for dyndnsgd. Exit with error code in the log
            echo 'Failed' . PHP_EOL;
            return;
        }

        // get r if addr of used gateway address.
        $r_if = get_real_interface('wan');
        $updater->_if = $r_if;
        $updater->_if_addr4 = find_interface_ip($r_if);

        $updater->checkAndDoDNSUpdate();
        sleep(1);

    }

}
