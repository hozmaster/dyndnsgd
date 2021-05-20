<?php
/*
 * Copyright (c) 2021.  Olli-Pekka Wallin All rights reserved
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
