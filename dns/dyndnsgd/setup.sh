#!/bin/sh

# This file is for helping development work.

TARGET_BASE_PATH=/usr/local/opnsense/mvc/app
SOURCE_BASE_PATH=src/opnsense/mvc/app

rm -r $TARGET_BASE_PATH/models/OPNsense/DynDNSGD
rm -r $TARGET_BASE_PATH/views/OPNsense/DynDNSGD
rm -r $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD
rm -r $TARGET_BASE_PATH/library/OPNsense/DynDNSGD
rm -r /usr/local/opnsense/scripts/OPNsense/DynDNSGD

#
if [ ! -d $TARGET_BASE_PATH/models/OPNsense/DynDNSGD ]; then
  mkdir $TARGET_BASE_PATH/models/OPNsense/DynDNSGD
  mkdir $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/ACL
  mkdir $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/Menu
fi

# views
if [ ! -d $TARGET_BASE_PATH/views/OPNsense/DynDNSGD ]; then
  mkdir $TARGET_BASE_PATH/views/OPNsense/DynDNSGD
fi

# controller
if [ ! -d $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD ]; then
  mkdir $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD
  mkdir $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/forms
  mkdir $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/Api
fi

# scripts
if [ ! -d /usr/local/opnsense/scripts/OPNsense/DynDNSGD ]; then
  mkdir /usr/local/opnsense/scripts/OPNsense/DynDNSGD
fi

# library
if [ ! -d $TARGET_BASE_PATH/library/OPNsense/DynDNSGD ]; then
  mkdir $TARGET_BASE_PATH/library/OPNsense/DynDNSGD
fi

cp -v $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/ACL/* $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/ACL
cp -v $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/Menu/* $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/Menu

## model
cp -v $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/*.xml $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/
cp -v $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/*.php $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/
#
## views
cp -v $SOURCE_BASE_PATH/views/OPNsense/DynDNSGD/*.volt $TARGET_BASE_PATH/views/OPNsense/DynDNSGD/
#
## controllers
cp -v $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/*.php $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/
cp -v $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/forms/* $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/forms
cp -v $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/Api/* $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/Api

## library
cp -v $SOURCE_BASE_PATH/library/OPNsense/DynDNSGD/*.php $TARGET_BASE_PATH/library/OPNsense/DynDNSGD/

## script
cp -v src/opnsense/scripts/* /usr/local/opnsense/scripts/OPNsense/DynDNSGD
chmod a+x /usr/local/opnsense/scripts/OPNsense/DynDNSGD/*.php

## service
cp -vf src/opnsense/service/conf/actions.d/actions_dyndnsgd.conf /usr/local/opnsense/service/conf/actions.d/actions_dyndnsgd.conf

## legacy plugins support
cp -vf usr/local/etc/rc.dyndnsgd /usr/local/etc/rc.dyndnsgd
chmod a+x /usr/local/etc/rc.dyndnsgd
cp -vf src/etc/inc/plugins.inc.d/dyndnsgd.inc /usr/local/etc/inc/plugins.inc.d/dyndnsgd.inc
