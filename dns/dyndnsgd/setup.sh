#!/bin/sh

TARGET_BASE_PATH=/usr/local/opnsense/mvc/app
SOURCE_BASE_PATH=src/opnsense/mvc/app

rm -r $TARGET_BASE_PATH/models/OPNsense/DynDNSGD
rm -r $TARGET_BASE_PATH/views/OPNsense/DynDNSGD
rm -r $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD

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
cp helpers/dyndnsgd.log /var/log
cp -v $SOURCE_BASE_PATH/controllers/OPNsense/DynDNSGD/Api/* $TARGET_BASE_PATH/controllers/OPNsense/DynDNSGD/Api
