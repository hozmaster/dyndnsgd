<?php
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

namespace OPNsense\Goddy;

use OPNsense\Base\BaseModel;
use OPNsense\Core\Config;

class GdDomains
{
    protected const DOMAIN_CONFIG_PATH = 'domains.domain';
    private string $uuid;

    public function getDomain(string $uuid)
    {
        // Get config object
        $model = new Domains();
        $this->uuid = $uuid;
        return $model->getNodeByReference(self::DOMAIN_CONFIG_PATH . ".${uuid}");
    }

    public function updateDomainIPv4Address(string $uuid, string $ip4Address)
    {
        // Get config object
        $model = new Domains();
        $this->uuid = $uuid;
        $obj = $model->getNodeByReference(self::DOMAIN_CONFIG_PATH . ".${uuid}");
        if ($obj == null) {
            GdUtils::log_error("Domain not found: ${uuid}");
            return false;
        }

        $obj->ipv4_address = $ip4Address;
        // Serialize to config and save
        $model->serializeToConfig();
        Config::getInstance()->save();
    }

    public function getAllDomains()
    {
        $model = new Domains();
        $obj = $model->getNodes();
        return $obj['domains']['domain'];
    }

    public function saveNewRecord($content)
    {
        $model = new Domains();
        $node = $model->domains->domain->add();

        $node->enabled = 0;
        $node->domain = $content['domain'];
        $node->domain_id = $content['domainId'];

        $validationMessages = $model->performValidation();
        foreach ($validationMessages as $message) {
            GdUtils::log("validation failure on field " . $message->getField() . "  returning message : " . $message->getMessage());
        }

        // Save a new record to the system provided config path
        if (!$validationMessages->count()) {
            $model->serializeToConfig();
            $cnf = Config::getInstance();
            $cnf->save();
        }
    }

}
