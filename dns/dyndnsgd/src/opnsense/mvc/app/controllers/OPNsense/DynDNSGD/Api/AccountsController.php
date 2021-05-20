<?php
/*
 * Copyright (c) 2021.  Olli-Pekka Wallin All rights reserved
 */

namespace OPNsense\DynDnsGD\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Backend;

class AccountsController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'dyndnsgd';
    protected static $internalModelClass = '\OPNsense\DynDNSGD\Accounts';

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

}
