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

namespace OPNsense\DynDnsGD\Api;

use OPNsense\Base\ApiMutableModelControllerBase;


class DomainsController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'dyndnsgd';
    protected static $internalModelClass = '\OPNsense\DynDNSGD\Domains';

    public function getAction($uuid = null)
    {
        $this->sessionClose();
        return $this->getBase('domain', 'domains.domain', $uuid);
    }

    public function addAction()
    {
        return $this->addBase('domain', 'domains.domain');
    }

    public function updateAction($uuid)
    {
        return $this->setBase('domain', 'domains.domain', $uuid);
    }

    public function delAction($uuid)
    {
        return $this->delBase('domains.domain', $uuid);
    }

    public function toggleAction($uuid, $enabled = null)
    {
        return $this->toggleBase('domains.domain', $uuid);
    }

    public function searchAction()
    {
        return $this->searchBase('domains.domain', array('enabled', 'domain', 'account', 'interface', 'description'), 'domain');
    }
}
