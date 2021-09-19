<?php

namespace OPNsense\DynDNSGD;

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

use OPNsense\DynDNSGD\Accounts;

abstract class Common
{
    protected $uuid;                # config object uuid

    protected $mdl_account;
    protected $gd_service;
    private $config = null;

    protected const ACCOUNT_CONFIG_PATH = 'accounts.account';


    protected function getId()
    {
        return (string)$this->config->id;
    }

    protected function getName()
    {
        return (string)$this->config->name;
    }

    protected function getKey()
    {
        return (string)$this->config->api_key;
    }

    protected function getSecretKey()
    {
        return (string)$this->config->api_secret;
    }

    public function loadAccount(string $path, string $uuid)
    {
        // Get config object
        $model = new Accounts();
        $obj = $model->getNodeByReference("${path}.${uuid}");
        if ($obj == null) {
            GdUtils::log_error("config of type ${path} not found: ${uuid}");
            return false;
        }
        $this->mdl_account = $model;
        $this->config = $obj;
        return true;
    }

}
