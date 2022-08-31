# Task List : 
- [ ] Views:
    - [x] The settings-view
        - [x] Add the Save-button and complete functionality
        - [x] Parameters:
            - [x] Enabled
            - [x] Key
            - [x] Secret key
            - [x] Check IP method
                - [x] Interface
            - [x] Interface
                - [x] List existing interfaces. User should be select used interface.
            - [x] Interval
                - [x] User should be able to give interval how often check will be made. Default 300 (5 minutes).
        - [x] User should be able to save settings
        - [ ] Should be able to show fetch results on the screen in the alert message box at certain time.
    - [ ] Add Fetch button and complete functionality
        - [ ] User should be able to connect to backend side
        - [ ] The Application should be able to perform to fetch domains from GoDaddy-service using given parameters
        - [ ] Return results of the action to the frontend
    - [ ] The Domains-view:
        - [ ] List all fetched domains
          -  [ ] Save current '@' record content to database (if possible)
        - [ ] User should be able to enable/disable domains from UI. If disabled, domain is not used to WAN IP checks
- [ ] Backend
  - [ ] backend should be able to receive the fetch request from UI side.
    - [ ] It should be able to connect to the Service with given key and secret.
    - [ ] Fetch all domains from the service.
    - [ ] Check is there new domains and update database if needed.
    - [ ] Backend should be able to react normal error cases (service is missing)
    - [ ] Backend should be able to give next results back to frontend.
      - [ ] No domains fetched
      - [ ] New domains fetched x amount
      - [ ] Error during processing request to the Service.
- [ ] Service 
  - [ ] Process the WAN IP check in given periodic
  - [ ] Periodic check is made.
  - [ ] Read settings from system's database structure
  - [ ] Read all domains and it's values from database to array
  - [ ] Remove disabled domains from array  
  - [ ] Processed all remaining domains in the array:
      - [ ] Verify existing ip from given interface
      - [ ] If update is required, update new ip value to the '@'-record to the GD service.
      - [ ] If ip address has changed, update it to also the database.

* All notable changes to this project will be documented in this file.

# Changelog

## 0.5.4 Released - 2021-12-27

### Added

Add animation when save changes from the Settings-view (short but still visible for users)

### Changed

Name of the plugin
Moved some functionality to the Library from /etc/rc.goddy side. (e.g. Record update)

### Fixed

Plugin should not crash when there is no accounts or domains listed in the models

## 0.5.3 [Released] - 2021-12-11

- PLug in renamed to Goddy
- Ip change and update functionality improved and simplified.
- Ui Models changed

### Added

TBD

### Changed

Name of the plugin

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
