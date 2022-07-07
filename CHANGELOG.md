# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 0.3.1

### Fixed

- Added support for mock classes with destructor

## 0.3.0

### Fixed

- Make services non-lazy to avoid "A method by name setProxyInitializer already exists in this class."

## 0.2.0

### Added

- Support for services created with factories
- Support for Symfony 6

### Changed

- All proxied services are lazy

## 0.1.3

### Changed

- The real object is updated whenever a proxy is initialized.

## 0.1.2

### Changed

- All proxied services are made public

## 0.1.1

### Added

- Trait `RestoreServiceContainer`
- Support for `ServiceMock::swap()`

## 0.1.0

First version
