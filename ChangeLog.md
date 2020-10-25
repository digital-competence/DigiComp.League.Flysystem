# CHANGELOG
All notable changes to this project will be documented in this file.

Since 6.0 the format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 2.1.0 2020-10-25

### Changed
- using filesystem adapter arguments in the root of configuration is now deprecated. Move them to an own key `adapterArguments`

### Added
- named Filesystems in own Filesystems.yaml
- `CommandController` which allows to list all available Plugins `flysystem:listplugins`.
