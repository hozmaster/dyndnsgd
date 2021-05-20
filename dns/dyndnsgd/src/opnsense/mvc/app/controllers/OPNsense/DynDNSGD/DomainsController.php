<?php

namespace OPNsense\DynDNSGD;

class DomainsController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->formDialogEditDomain = $this->getForm("dlgeditdomain");
        // pick the template to serve to our users.
        $this->view->pick('OPNsense/DynDNSGD/domains');
    }
}
