
## WHAT

GoDaddy specific Dynamic DNS plugin for OPNsense

This plugin allows you to update public ip address to dns service provider when it's changed by ISP. This plugin will be at first GoDaddy specific, it will be handle all settings what user can do their via web page. 

## WHY

Current functionality of dynamic dns in OPNSense platform is quite limited and will handle all most popular dns service providers.

## STATUS

Status : Tech preview.

Only frontend side is works currently.  User can create and edit and account and domains in their respective views. Data is saved to the internal database whet OPNSense platform offers.

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
