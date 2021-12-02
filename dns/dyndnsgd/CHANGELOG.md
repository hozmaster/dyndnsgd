# Changelog

All notable changes to this project will be documented in this file.

## 0.5.1 [Unreleased] - 202x-xx-xx

Development version. Do not install to production system. 

Key features:
- Log support improvements
- Model changes (change all occurrences of 'DynDNSGD' lower case)
- Move all curl actions to the library
- Some UI changes ?

### Added
TBD

### Changed
TBD

### Fixed
TBD

## 0.5.0 Released - 2021-11-25

Key features 
- Dynamic DNS IP update detection, cached ip's to database 

### KNOWN ISSUES
- Interface detection may not work correctly (interface detection fails)
- Only '@'-record is support at a moment
- Only Ipv4 addresses is supported currently.

### Added
- Initial SQLite3 support (database, tables, columns etc)
- Setup.sh creates now /var/dyndnsgd folder structure
- Added shell script to create a sqlite3 database for dyndnsgd-plugin
- Basic database operation now work (insert and search)
- Saved record for a domain will be checked before try to update ip address of record to the service.
- Show cached ipv4 address in the Domains view.

### Changed
- Domains model patched to 0.3.1.
- Removed the ip4 address-row from the Domain details dialog. 

### Fixed
- After plugin has been installed, installer will create required folders and files to system


## 0.3.8 - 2021-09-27

* Domain fetching from GoDaddy service with valid keys should work 
* DNS records update should work (hard coded TXT record testing purpose only)
* Plugin is now listed in the Dashboard as a service.

### KNOWN ISSUES

Beta product. All db changes are now done. 
Not yet save a WAN address to the GoDaddy service.   

### Added

Settings view

- Add @-records by default ?-option

Account view

- Fetch domains GoDaddy service.

Domains view

- Added ability to remove domains from the view

Overall

- Ability to define Cron job so record updating can done by this plugin.
- Settings model: Add new column for 'Add @-records by default'-option
- Account model : Replace two column names with better one. Increase model version.
- Domains model : Increase model version.
- Domains model : Added IP4(6) address columns to the Domains-model
- Fix problems and bugs in various places.

### Changed

Account view

- Verify button renamed to Fetch Domains-button

### Fixed

- [Can now actually fetch owned domains from GoDaddy service.]

## 0.3.0 [Unreleased] - yyyy-mm-dd

### KNOWN ISSUES

### Added

- Coding updating GoDaddy DNS records started.
- the Settings view.
- Log view show actual content of log data
- Service support (kind of)

### Changed

the BSD-2 License added to php files, previous licences removed from use. Added Library also to the setup.sh-files

### Fixed

None

## 0.2.7 [released] - 2021-05-29

### KNOWN ISSUES

- Backend is not yet supported.
- Logging is not yet supported.

### Added

N/A

### Changed

Remove Edit-buttons from both views (Account, Domains)

Account dialog :

- Command buttons for Edit and Delete actions added for account row
- Delete button from bottom of grid was removed.
- Tooltip was added to the Add-button.
- Added data formatter to the Staging-column.

Domain dialog :

- Command buttons for Edit and Delete actions added for account row
- Delete button from bottom of grid was removed.
- Tooltip was added to the Add-button.

### Fixed

## 0.2.6   2021-05-19

### Added:

- The Log file-menu item.
- The Log file-view.

### Changed

Account view:

- Users can now add and delete accounts in the view.

Domains view :

- Users can now add, create, delete and domains to database.
- Grid view improved. 
