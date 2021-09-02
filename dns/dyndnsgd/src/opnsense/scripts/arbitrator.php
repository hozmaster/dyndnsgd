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

include_once('config.inc');
include_once('certs.inc');
include_once('util.inc');

use OPNsense\DynDNSGD\Worker;

// Summary that will be displayed in usage information.
const ABOUT = <<<TXT

This script acts as a bridge between the OPNsense WebGUI/API and the
GoDaddy account.

TXT;

const EXAMPLES = <<<TXT
- Fetch all users domains from GoDaddy service.
  arbitrator.php --mode fetch --uuid 00000000-0000-0000-0000-000000000000
- Validate key and secret key are valid against GoDaddy account
  arbitrator.php --mode fetch --uuid 00000000-0000-0000-0000-000000000000  
TXT;

// Supported account actions and their help text
const MODES = [
    'validate' => [
        'description' => 'Verify key and secret key are valid.',
    ],
    'fetch' => [
        'description' => 'Fetch all domains for account',
    ]
];

// Supported command line options and their usage information.
const STATIC_OPTIONS = <<<TXT
-h, --help          Print commandline help
--mode              Specify the mode of operation
--uuid              The id of the account in the Account-model
--verbose           Turn verbose mode on. Note: key and secret key are not logged when setting is on.
TXT;

function arb_help()
{
    echo ABOUT . PHP_EOL
        . "Usage: " . basename($GLOBALS["argv"][0]) . " --mode MODE [options]" . PHP_EOL
        . PHP_EOL . STATIC_OPTIONS . PHP_EOL;

    echo PHP_EOL . 'Available modes:' . PHP_EOL;
    foreach (MODES as $name => $options) {
        echo "\"$name\" - {$options["description"]}" . PHP_EOL;
    }

    echo PHP_EOL . "Examples:" . PHP_EOL
        . str_replace('/\r\n|\n|\r/g', PHP_EOL, EXAMPLES)
        . PHP_EOL . PHP_EOL;
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
    $options = getopt('h::', ['account:', 'help', 'mode:', 'uuid:', 'verbose::']);
    if (empty($options) || isset($options['h']) || isset($options['help']) ||
        (isset($options['mode']) and !validateMode($options['mode']))) {
        arb_help();
    } elseif (($options['mode'] === 'validate') && (isset($options['uuid']))) {
        log_notice("mode: validate, nothing yet");
    } elseif (($options['mode'] === 'fetch') && (isset($options['uuid']))) {
        $worker = new Worker($options['uuid']);
        if (isset($options['verbose'])) {
            log_notice("mode: fetch, toggle mode on");
            $worker->toggleVerbose();
        }
        $worker->fetch_all_domains();
    } else {
        arb_help();
    }
}

function log_notice($msg)
{
    syslog(LOG_NOTICE, "DynDNSGD: " . $msg);
}

// Run!
main();
