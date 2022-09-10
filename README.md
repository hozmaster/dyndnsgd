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

Settins:
* Ability to save required settings to system config storage.

Domains :
* Ability to check current ip address of the '@'-record and update if it's needed.

GoDaddy :

* Fetch all acquired domains from the GoDaddy service
                                                                                                    

## Instructions

Only Admin user is normally use this component since nature of this plugin. So when user is mentioned in here it's 
user which have Admin level privileges. 

### After installation.

Verify that this plugin is visible at Service-manu. It should have a menu item called **'Dynamic DNS (goddy)**.

Open and verify opened sub menu contains next sub items: 'Settings', 'Domains' and 'Log'.

### Settings-view

The Settings view contains next controls : 'Enable', 'Interface' and 'Log Level'

#### Enabled
From 'Enable'-component user can disable of enable activity of the plugin. Default value is 'Enabled'.

#### Interface
This control contains all interfaces which system contains. Default selection is 'WAN' but admin can change this 
correct interface which connected ISP service.

#### Log level
This component controls how the logging verbose level during operations. Default level is 'normal'. 
If this is selected, only fatal errors are logged. Use debug when there is problems during activity.  

##### Name

User have to give name record for making selection easier.

##### Description

User can to give Description for this record. 

##### Service provider
                      
Only 'GoDaddy' selection is available currently.

##### Key

Enter the GoDaddy API key for this records. 

##### Secret Key

Enter the GoDaddy API secret key for used the GoDaddy account 

##### Staging

If selected, this record is test account. Leave unchecked for now. 

##### Buttons

This dialog has two buttons, 'Cancel' and 'Save'. If user press a 'Cancel'-buttons, changes for this dialog 
are discarded. 

If user press a 'Save'-button, a new record are added to system.

#### Small buttons after record

Each record in the grid contains three buttons, 'Edit', 'Delete' and 'Fetch'-domains buttons. 

Before user edit any domain in the Domains, user have to fetch all domains from service. This can be done using 'Fetch'-domain 
fetch all domains from the GoDaddy account has.
