# Changelog

All notable changes to this project will be documented in this file.

## 0.3.x-dev [Unreleased] - yyyy-mm-dd

### KNOWN ISSUES

### Added

Settings view
- Add @-records by default ?-option

Account view
- Fetch domains GoDaddy service.

Overall

- Ability to define Cron job so record updating can done by this plugin.
- Settings model: Add new column for 'Add @-records by default'-option
- Domains: Added  IP4(6) address columns to the Domains-model

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
