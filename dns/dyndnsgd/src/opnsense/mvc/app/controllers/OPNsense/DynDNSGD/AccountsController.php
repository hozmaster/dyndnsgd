<?php

namespace OPNsense\DynDNSGD;

class AccountsController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->formDialogEditServiceAccount = $this->getForm("dlgeditserviceaccount");
        // pick the template to serve to our users.
        $this->view->pick('OPNsense/DynDNSGD/accounts');
    }
}
