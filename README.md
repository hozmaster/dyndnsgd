## WHAT

GoDaddy specific Dynamic DNS plugin for OPNsense

This plugin allows system to update public ip address of the '@'-service records to the goDaddy service. 

## WHY

For mots use cases it's just need to update '@'-record to allow web and mail traffic to reach target system.  

## STATUS

Status : In progress.

Views and dialogs are more and less ready. Still some opens issues still exist but this should work pretty reasonably. WAN ip address will updated to the service. Records will updated database and it can be fetched from it. There is some limitations still left but overall situtation is quite good. Not production ready yet but it can used to testing purposes and for study in VM.

## GOALS

This plugin should be able to :

Settings:
* Ability to save required settings to system config storage.

Domains :
* Ability to check current ip address of the '@'-record and update if it's needed.

GoDaddy :

* Fetch all acquired domains from the GoDaddy service
                                                                                                    
## Instructions

### Requirements
OPNSense 22.7 or later since part of code requires PHP 8 or later.

Only Admin user is normally use this component since nature of this plugin. So when user is mentioned in here it's 
user which have Admin level privileges. 

### After installation.

Verify that this plugin is visible at Service-manu. It should have a menu item called **'Dynamic DNS (goddy)**.

Open and verify opened sub menu contains next sub items: 'Settings', 'Domains' and 'Log'. Enter GoDaddy API credentials and other settings 
and press the Fetch-button. All user owned domains should be now listed in the Domains-view. 
