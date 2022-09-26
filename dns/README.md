# GODDY

[![License](https://img.shields.io/badge/License-BSD_2--Clause-orange.svg)](https://opensource.org/licenses/BSD-2-Clause)

## WHAT

GoDaddy specific Dynamic DNS plugin for OPNsense

This plugin allows system to update public ip address of the '@'-service records to the GoDaddy service. 

## WHY

Currently, for most internet users only dynamic dns addresses are available for consumers. If consumers run web services there is need to 
somehow update current dynamic ip address to DNS service providers. However, in most use cases, you need an @ record to be updated so that web and email traffic can reach the target system.  

## STATUS

Status : In progress. Suitable for testing in the demo environment.

Views and dialogs and models are ready. there is still some opens issues left but this plugin should work pretty reasonably. WAN ip address will update to the service. Records will update database, and it can be fetched from it. There is some limitations still left but overall situation is quite good. Not production ready yet, but it can be used to testing purposes and for study in VM.

Currently only IPv4 addresses are supported.

## GOALS

Requirements:

* Should be able to save required settings to system config storage.
* Should be able to check current ip address of the '@'-record and update if it's needed.
* Can search for domains owned by the user from the GoDaddy service
                                                                                                    
## Instructions

### Requirements
OPNSense 22.7 or later since part of code requires PHP 8 or later.

Only Admin user is normally use this component since nature of this plugin. So when user is mentioned in here it's 
user which have Admin level privileges. 

### After installation.

Verify that this plugin is visible at Service-manu. It should have a menu item called **'Dynamic DNS (goddy)**.

Open and verify opened sub menu contains next sub items: 'Settings', 'Domains' and 'Log'. Enter GoDaddy API credentials and other settings 
and press the Fetch-button. All user owned domains should be now listed in the Domains-view. 

### WAN IP change checking 

To track periodically, create a new cron job via System - Settings - Cron. In the view, pres a '+'-button to crete a new job. In the dialog to create a job which run in every 5 minutes enter next parameters: 

> Minutes : */5\
> Hours: *\
> Day of month: *\
> Months: *\
> Days of weeks: *\
> Commands: Dynamic DNS update (goddy)\

Parameters text field can be left empty.
Description is needed so write good one  
