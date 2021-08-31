#!/usr/local/bin/php
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

@include_once('config.inc');
@include_once('certs.inc');
@include_once('util.inc');

use OPNsense\DynDNSGD\Worker;


const EXAMPLES = <<<TXT
- Fetch all domains of account
  arbitrator.php --domains fetch_domains --uuid 00000000-0000-0000-0000-000000000000
TXT;

// Supported account actions and their help text
const ACTIONS = [
    'verify' => [
        'description' => 'Verify status of the account.',
    ],
    'fetch_domains' => [
        'description' => 'Fetch all domains for account',
    ]
];

function help()
{
}

function validateMode($mode)
{
    $return = false;
    foreach (ACTIONS as $name => $options) {
        if ($mode === $name) {
            $return = true;
            break;
        }
    }
    return $return;
}

function main()
{
    // Parse command line arguments
    $options = getopt('h', ['account:', 'help', 'mode:', 'uuid:']);
    if (empty($options) || isset($options['h']) || isset($options['help']) ||
        (isset($options['mode']) and !validateMode($options['mode']))) {
        log_error("Invalid or not valid amount of arguments passed.");
        help();
    }
    if ($options['domains'] === 'fetch_domains') {
        log_notice("fetch all domains for account");
    }
    if ($options['mode'] === 'verify') {
        $worker = new Worker($options['uuid']);
        $worker->fetch_all_domains();
    } else {
        help();
    }
}

function log_error($msg)
{
    syslog(LOG_ERR, "DynDNSGD: " . $msg);
}

function log_notice($msg)
{
    syslog(LOG_NOTICE, "DynDNSGD: " . $msg);
}

// Run!
main();
