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

# This script is for help development work.

TARGET_BASE_PATH=/usr/local/opnsense/mvc/app
SOURCE_BASE_PATH=goddy/src/opnsense/mvc/app

rm -r $TARGET_BASE_PATH/models/OPNsense/Goddy
rm -r $TARGET_BASE_PATH/views/OPNsense/Goddy
rm -r $TARGET_BASE_PATH/controllers/OPNsense/Goddy
rm -r $TARGET_BASE_PATH/library/OPNsense/Goddy
rm -r /usr/local/opnsense/scripts/OPNsense/Goddy

#
if [ ! -d $TARGET_BASE_PATH/models/OPNsense/Goddy ]; then
  mkdir $TARGET_BASE_PATH/models/OPNsense/Goddy
  mkdir $TARGET_BASE_PATH/models/OPNsense/Goddy/ACL
  mkdir $TARGET_BASE_PATH/models/OPNsense/Goddy/Menu
fi

# views
if [ ! -d $TARGET_BASE_PATH/views/OPNsense/Goddy ]; then
  mkdir $TARGET_BASE_PATH/views/OPNsense/Goddy
fi

# controller
if [ ! -d $TARGET_BASE_PATH/controllers/OPNsense/Goddy ]; then
  mkdir $TARGET_BASE_PATH/controllers/OPNsense/Goddy
  mkdir $TARGET_BASE_PATH/controllers/OPNsense/Goddy/forms
  mkdir $TARGET_BASE_PATH/controllers/OPNsense/Goddy/Api
fi

# scripts
if [ ! -d /usr/local/opnsense/scripts/OPNsense/Goddy ]; then
  mkdir /usr/local/opnsense/scripts/OPNsense/Goddy
fi

# library
if [ ! -d $TARGET_BASE_PATH/library/OPNsense/Goddy ]; then
  mkdir $TARGET_BASE_PATH/library/OPNsense/Goddy
fi

# template
if [ ! -d /usr/local/opnsense/service/templates/OPNsense/Goddy ]; then
  mkdir /usr/local/opnsense/service/templates/OPNsense/Goddy
fi

cp -vf $SOURCE_BASE_PATH/models/OPNsense/Goddy/ACL/* $TARGET_BASE_PATH/models/OPNsense/Goddy/ACL
cp -vf $SOURCE_BASE_PATH/models/OPNsense/Goddy/Menu/* $TARGET_BASE_PATH/models/OPNsense/Goddy/Menu

## model
cp -vf $SOURCE_BASE_PATH/models/OPNsense/Goddy/*.xml $TARGET_BASE_PATH/models/OPNsense/Goddy/
cp -vf $SOURCE_BASE_PATH/models/OPNsense/Goddy/*.php $TARGET_BASE_PATH/models/OPNsense/Goddy/
#
## views
cp -vf $SOURCE_BASE_PATH/views/OPNsense/Goddy/*.volt $TARGET_BASE_PATH/views/OPNsense/Goddy/
#
## controllers
cp -vf $SOURCE_BASE_PATH/controllers/OPNsense/Goddy/*.php $TARGET_BASE_PATH/controllers/OPNsense/Goddy/
cp -vf $SOURCE_BASE_PATH/controllers/OPNsense/Goddy/forms/* $TARGET_BASE_PATH/controllers/OPNsense/Goddy/forms
cp -vf $SOURCE_BASE_PATH/controllers/OPNsense/Goddy/Api/* $TARGET_BASE_PATH/controllers/OPNsense/Goddy/Api

## library
cp -vf $SOURCE_BASE_PATH/library/OPNsense/Goddy/*.php $TARGET_BASE_PATH/library/OPNsense/Goddy/

## script
cp -vf goddy/src/opnsense/scripts/OPNsense/Goddy/* /usr/local/opnsense/scripts/OPNsense/Goddy
chmod a+x /usr/local/opnsense/scripts/OPNsense/Goddy/*.php

## service
cp -vf goddy/src/opnsense/service/conf/actions.d/actions_goddy.conf /usr/local/opnsense/service/conf/actions.d/actions_goddy.conf

## templates
cp -vf  goddy/src/opnsense/service/templates/OPNsense/Goddy/* /usr/local/opnsense/service/templates/OPNsense/Goddy

## legacy plugins support
cp -vf goddy/src/etc/rc.goddy /usr/local/etc/rc.goddy

cp -vf goddy/src/etc/inc/plugins.inc.d/goddy.inc /usr/local/etc/inc/plugins.inc.d/goddy.inc
cp -vf goddy/src/etc/inc/plugins.inc.d/goddy/GoDaddy.inc /usr/local/etc/inc/plugins.inc.d/goddy/GoDaddy.inc
cp -vf goddy/src/etc/inc/plugins.inc.d/goddy/RequesterBase.inc /usr/local/etc/inc/plugins.inc.d/goddy/RequesterBase.inc

if [ ! -d /usr/local/etc/inc/plugins.inc.d/goddy ]; then
  mkdir /usr/local/etc/inc/plugins.inc.d/goddy
fi
cp -vf goddy/src/etc/inc/plugins.inc.d/goddy/gdDnsUpdater.inc /usr/local/etc/inc/plugins.inc.d/goddy/gdDnsUpdater.inc
