
## WHAT

GoDaddy specific Dynamic DNS plugin for OPNsense

This plugin allows you to update public ip address to dns service provider when it's changed by ISP. This plugin will be at first GoDaddy specific, it will be handle all settings what user can do their via web page. 

## WHY

Current functionality of dynamic dns in OPNSense platform is quite limited and will handle all most popular dbns service providers.

## STATUS

Status : Tech preview.

Only frontend side is work currenyly.  User can create and edit and account and domains in their respective views. Data is savved to the internal database whet OPNSense platform offers.

## GOALS

This plugin should be able to :

* Support to add, edit, delete accounts (v.0.9.0).
* Support to add,edit, delete and disable/enable domains (v.0.9.0).
* Suport add, edit, delete subdomains for all kinds of service type what GoDaddy supports (v1.0).
* Log activity
