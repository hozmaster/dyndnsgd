## WHAT

GoDaddy specific Dynamic DNS plugin for OPNsense

This plugin allows you to update public ip address to dns service provider when it's changed by ISP. This plugin will be at first GoDaddy specific, it will be handle all settings what user can do their via web page. 

## WHY

Current functionality of dynamic dns in OPNSense platform is quite limited and will handle all most popular dns service providers.

## STATUS

Status : In progress.

Views and dialogs are more and less ready. Still some opens issues still exist but this should work pretty reasonably. WAN ip address will updated to the service. Records will updated database and it can be fetched from it. There is some limitations still left but overall situtation is quite good. Not production ready yet but it can used to testing purposes and for study in VM.

## GOALS

This plugin should be able to :

Account : 
* Support to add, edit, delete accounts (v.0.9.0). Enable, disable account.
* Support to fetch all owned domains from service (v.0.9.0).
* Check and update status of domains repeatedly owned by account
* Log activity

Domains :
* Ability to fetch subdomains from service.
* Ability maintain records for domain.

GoDaddy :

* Fetch all aquired domains from the GoDaddy service
* Support to add, edit, delete subdomains for all kinds of service type what GoDaddy supports (v1.0).
                                                                                                     

## Instructions

Only Admin user is normally use this component since nature of this plugin. So when user is mentioned in here it's 
user which have Admin level privileges. 

### After installation.

Verify that this plugin is visible at Service-manu. It should menu item the 'DynDNSGD'. If not plugin is not installed correctly.

Open and verify opened sub menu contains next sub items: 'Settings', 'Accounts', 'Domains' and 'Log Files'.

* Some key point from component: user can't enter any of domain for accounts. They are fetched from Account-view. 
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

### Accounts-view

In this view, user is able to add GoDaddy accounts details for plugin usage. Until proper account credentials,
plugin can't work properly. Normally, this view contains only one or two records. 

Admin user can create production or test account as GoDaddy supports both types. However, only production keys are 
supported currently.

On right side of this view, there is '+'-icon where user can create new record for account. When user press it,
an 'Add Account'-dialog is opened on screen.

#### Add Account dialog

The Add Account dialog contains next components : 

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
