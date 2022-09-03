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

namespace OPNsense\Goddy\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Backend;
use OPNsense\Goddy\GdUtils;

class SettingsController extends ApiMutableModelControllerBase
{
    protected static $internalModelClass = '\OPNsense\Goddy\Settings';
    protected static $internalModelName = 'settings';

    public function fetchAction()
    {
        $response = "passed";
        // Start actual process, but we don't have method check actual response
        // from it. Maybe we need sockets ?
        $backend = new Backend();
        if ($this->request->isPost()) {
            $response = json_decode($backend->configdRun("goddy fetch-domains"), true);
        }
        return array("message" => $response['api_key']);
    }

    private function checkGoDaddyParameters(): bool
    {
        global $config;
        $pai_key = $config['OPNsense']['Goddy']['settings']['api_key'];
        $api_secret = $config['OPNsense']['Goddy']['settings']['api_secret'];
        if (strlen($pai_key) || strlen($api_secret)) {
            return true;
        } else {
            return false;
        }
    }

    private function parseResponse($response): array
    {
        GdUtils::log('Result of the fetching domains:' . $response);
        return array("message" => "bare action");
    }

}
