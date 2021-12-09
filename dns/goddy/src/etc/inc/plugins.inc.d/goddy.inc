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
require_once('plugins.inc.d/goddy/gdDnsUpdater.inc');

function goddy_configure(): array
{
    return array(
        'bootup' => array('goddy_configure_do'),
        'local' => array('goddy_configure_do'),
        'newwanip' => array('goddy_configure_do:2'),
    );
}

function goddy_enabled(): bool
{
    global $config;
    return isset($config['OPNsense']['Goddy']['settings']['enabled']) &&
        $config['OPNsense']['Goddy']['settings']['enabled'] == 1;
}

function goddy_services()
{
    $services = array();
    if (goddy_enabled()) {
        $services[] = array(
            'description' => gettext('GoDaddy specific Dynamic DNS updater'),
            'configd' => array(
                'restart' => array('goddy reload'),
                'start' => array('goddy start')
            ),
            'nocheck' => true,
            'name' => 'goddy',
            'pidfile' => '/var/run/goddy.pid'
        );
    }
    return $services;
}

function goddy_list()
{
    return array(
        'godaddy' => 'GoDaddy',
        'godaddy-v6' => 'GoDaddy (v6)',
    );
}

function goddy_configure_do($verbose = true, $param = '')
{
    global $config;

    if (goddy_enabled()) {
        $opnsense = $config['OPNsense'];
        $goddy = $opnsense['Goddy'];

        if (empty($goddy)) {
            syslog(LOG_ERR, "Goddy: Internal model structure can't be found, Quit now.");
            return -1;
        }

        ob_start();
        var_dump($goddy);
        $dump = ob_get_contents();
        file_put_contents("/tmp/goddy_full_models_dump", $dump);
        ob_end_clean();

        $verbose = $goddy['settings']['log_level'] == 'debug';
        $wan_if = $goddy['settings']['interface'];
        if (empty($wan_if)) {
            $wan_if = "wan";
        }

        $accounts = array_pop($goddy['accounts']);
        $domains = array_pop($goddy['domains']);
        $gw_groups = return_gateway_groups_array();
        if ((!empty($param)) || ($param == $wan_if) || (is_array($gw_groups[$wan_if]))) {
            $updater = new gdDnsUpdater(
                $wan_if,
                $verbose,
                $goddy,
                $accounts,
                $domains
            );

            // do actual verification and the update process if needed
            $updater->checkAndDoDNSUpdate();
            sleep(1);
        }

    } else {
        syslog(LOG_NOTICE, "Goddy: plugin is disabled");
    }
}
