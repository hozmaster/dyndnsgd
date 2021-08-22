#!/bin/sh

#
# Copyright (c) 2021, Olli-Pekka Wallin
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this
#    list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice,
#    this list of conditions and the following disclaimer in the documentation
#    and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
# FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
# DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
# SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
# CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
# OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#

# This file is for helping development work.

TARGET_BASE_PATH=/usr/local/opnsense/mvc/app
SOURCE_BASE_PATH=dyndnsgd/src/opnsense/mvc/app

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

cp -vf $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/ACL/* $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/ACL
cp -vf $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/Menu/* $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/Menu

## model
cp -vf $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/*.xml $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/
cp -vf $SOURCE_BASE_PATH/models/OPNsense/DynDNSGD/*.php $TARGET_BASE_PATH/models/OPNsense/DynDNSGD/
#
## views
cp -vf $SOURCE_BASE_PATH/views/OPNsense/DynDNSGD/*.volt $TARGET_BASE_PATH/views/OPNsense/DynDNSGD/
#
## controllers
cp -vf $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/*.php $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/
cp -vf $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/forms/* $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/forms
cp -vf $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/Api/* $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/Api

## library
cp -vf $SOURCE_BASE_PATH/library/OPNsense/DynDNSGD/*.php $TARGET_BASE_PATH/library/OPNsense/DynDNSGD/

## script
cp -vf dyndnsgd/src/opnsense/scripts/* /usr/local/opnsense/scripts/OPNsense/DynDNSGD
chmod a+x /usr/local/opnsense/scripts/OPNsense/DynDNSGD/*.php

## service
cp -vf dyndnsgd/src/opnsense/service/conf/actions.d/actions_dyndnsgd.conf /usr/local/opnsense/service/conf/actions.d/actions_dyndnsgd.conf

## legacy plugins support
cp -vf src/etc/rc.dyndnsgd /usr/local/etc/rc.dyndnsgd
## chmod a+x /usr/local/etc/rc.dyndnsgd

cp -vf src/etc/inc/plugins.inc.d/dyndnsgd.inc /usr/local/etc/inc/plugins.inc.d/dyndnsgd.inc
cp -vf src/etc/inc/plugins.inc.d/dyndnsgd/GoDaddy.inc /usr/local/etc/inc/plugins.inc.d/dyndnsgd/GoDaddy.inc
cp -vf src/etc/inc/plugins.inc.d/dyndnsgd/RequesterBase.inc /usr/local/etc/inc/plugins.inc.d/dyndnsgd/RequesterBase.inc

if [ ! -d /usr/local/etc/inc/plugins.inc.d/dyndnsgd ]; then
  mkdir /usr/local/etc/inc/plugins.inc.d/dyndnsgd
fi
cp -vf dyndnsgd/src/etc/inc/plugins.inc.d/dyndnsgd/gdDnsUpdater.inc /usr/local/etc/inc/plugins.inc.d/dyndnsgd/gdDnsUpdater.inc
