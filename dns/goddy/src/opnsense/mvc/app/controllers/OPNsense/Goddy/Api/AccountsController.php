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
use OPNsense\Goddy\Accounts;
use OPNsense\Goddy\GdUtils;

class AccountsController extends ApiMutableModelControllerBase
{
    protected static $internalModelClass = '\OPNsense\Goddy\Accounts';
    protected static $internalModelName = 'accounts';

    public function getAction($uuid = null)
    {
        $this->sessionClose();
        return $this->getBase('account', 'accounts.account', $uuid);
    }

    public function addAction()
    {
        return $this->addBase('account', 'accounts.account');
    }

    public function updateAction($uuid)
    {
        return $this->setBase('account', 'accounts.account', $uuid);
    }

    public function delAction($uuid)
    {
        return $this->delBase('accounts.account', $uuid);
    }

    public function toggleAction($uuid, $enabled = null)
    {
        return $this->toggleBase('accounts.account', $uuid);
    }

    public function searchAction()
    {
        return $this->searchBase('accounts.account', array('enabled', 'service_provider', 'name', 'description', 'staging'), 'name');
    }

    public function fetchAction($uuid): array
    {
        if ($uuid != null) {
            $mdlAccount = new Accounts();
            $node = $mdlAccount->getNodeByReference('accounts.account.' . $uuid);
            if ($node != null) {
                $backend = new Backend();
                $response = $this->parseResponse($backend->configdRun("goddy fetch-domains ${uuid}"));
                return array("response" => $response);
            }
        }
        return array("response" => "status: account not found from device.");
    }

    private function parseResponse($response): array
    {
        GdUtils::log('Domain fetching results: ' . $response);
        $target = explode(' ', trim($response));
        if ($target[0] == 'Error') {
            $target[1] = str_replace(array('(', ')'), '', $target[1]);
        }
        return $target;
    }
}
