# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://git.d3data.de/D3Private/linkmobility4oxid/compare/1.1.1.0...rel_1.x)

## [1.1.1.0](https://git.d3data.de/D3Private/linkmobility4oxid/compare/1.1.0.1...1.1.1.0) - 2023-01-01
### Added
- make installable in OXID 6.5.1 (CE 6.13)
- add linkmobility to remark types in installation triggers
- regenerate database views in installation process

### Changed
- adjust requirements

### Fixed
- fix error if configuration is missing

## [1.1.0.1](https://git.d3data.de/D3Private/linkmobility4oxid/compare/1.1.0.0...1.1.0.1) - 2022-09-29
### Changed
- adjust readme

## [1.1.0.0](https://git.d3data.de/D3Private/linkmobility4oxid/compare/1.0.0.0...1.1.0.0) - 2022-07-28
### Added
- phpstan code checks

### Changed
- improved changelog

### Fixed
- type in IntelliSenseHelper class name

### Removed
- PHP 7.0 support

## [1.0.0.0](https://git.d3data.de/D3Private/linkmobility4oxid/releases/tag/1.0.0.0) - 2022-07-13
### Added
- initial implementation
    Send message on:
  - Order completion, sending order confirmation message.
  - Sended now message
  - Storno message

  - Sending individual message from the admin area of the shop to contact data from the customer account
  - Sending individual message from the admin area of the shop to contact data from existing order