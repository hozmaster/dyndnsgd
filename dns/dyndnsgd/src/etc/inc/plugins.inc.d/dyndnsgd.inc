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


function dyndnsgd_configure()
{
    return array(
        'bootup' => array('dyndns_gd_configure_do'),
        'local' => array('dyndns_gd_configure_do'),
        'newwanip' => array('dyndns_gd_configure_do:2'),
    );
}

function dyndnsgd_enabled(): bool
{
    global $config;
    return isset($config['OPNsense']['DynDNSGD']['settings']['enabled']) &&
        $config['OPNsense']['DynDNSGD']['settings']['enabled'] == 1;
}

function dyndnsgd_services()
{
    $services = array();
    if (dyndnsgd_enabled()) {
        $services[] = array(
            'description' => gettext('GoDaddy specific Dynamic DNS updater'),
            'configd' => array(
                'restart' => array('dyndns_gd reload'),
            ),
            'nocheck' => true,
            'name' => 'dyndnsgd',
            'pidfile' => '/var/run/dyndnsgd.pid'
        );
    }
    return $services;
}

function dyndnsgd_list()
{
    return array(
        'godaddy' => 'GoDaddy',
        'godaddy-v6' => 'GoDaddy (v6)',
    );
}

function dyndnsgd_configure_do($verbose = false, $int = '')
{
    global $config;
    if (dyndnsgd_enabled()) {
        $models = $config['OPNsense']['DynDNSGD'];
        $verbose = $models['settings']['log_level'] == 'debug';

        // get r if addr of used gateway address.
        $r_ifddr4 = get_real_interface('wan');

        $updater = new gdDnsUpdater(
            $r_ifddr4,
            $verbose,
            $models
        );

        // do actual verification and the update process if needed
        $updater->checkAndDoDNSUpdate();
        sleep(1);

    } else {
        syslog(LOG_NOTICE, "DynDNSGD: plugin is disabled");
    }

}
