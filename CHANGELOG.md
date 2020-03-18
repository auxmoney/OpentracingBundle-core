# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [0.5.0](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.4.3...v0.5.0) (2020-03-18)


### ⚠ BREAKING CHANGES

* add sampler configuration (#24)

### Features

* add sampler configuration ([#24](https://github.com/auxmoney/OpentracingBundle-core/issues/24)) ([b8a120b](https://github.com/auxmoney/OpentracingBundle-core/commit/b8a120be43d5e52a7d74083b5bd7092ed14be5ba))

### [0.4.3](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.4.2...v0.4.3) (2020-03-17)


### Bug Fixes

* Wrap calls to flush in try catch ([#26](https://github.com/auxmoney/OpentracingBundle-core/issues/26)) ([4ccfef0](https://github.com/auxmoney/OpentracingBundle-core/commit/4ccfef089a4b51dc65ef6f8e5ccddee6cd88bc72))

### [0.4.2](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.4.1...v0.4.2) (2020-03-13)


### Features

* add support for injecting into an array ([#23](https://github.com/auxmoney/OpentracingBundle-core/issues/23)) ([10417e6](https://github.com/auxmoney/OpentracingBundle-core/commit/10417e6bd12bf1cb4671d4562aa09787ee00350a))

### [0.4.1](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.4.0...v0.4.1) (2020-03-04)


### Bug Fixes

* remove automatic releases ([da51e09](https://github.com/auxmoney/OpentracingBundle-core/commit/da51e0943ef1ea70b9e447354886a039acc1f3d2))

## [0.4.0](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.6...v0.4.0) (2020-03-04)


### ⚠ BREAKING CHANGES

* functional tests must now select startTime: startTime in their $expression for getSpansAsYAML()

contributors: @cHeeSaW 

### Bug Fixes

* sort spans in functional tests by startTime ([#20](https://github.com/auxmoney/OpentracingBundle-core/issues/20)) ([1f3250b](https://github.com/auxmoney/OpentracingBundle-core/commit/1f3250b6dfce42a30647cc4a5474b1eb1b77e4eb))

### [0.3.6](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.5...v0.3.6) (2020-02-21)


### Features

* improve functional tests setup ([#18](https://github.com/auxmoney/OpentracingBundle-core/issues/18)) ([ca8c13b](https://github.com/auxmoney/OpentracingBundle-core/commit/ca8c13b35bdc47f13cfa30aec325d848404be7c8))

### [0.3.5](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.4...v0.3.5) (2020-02-06)


### Features

* add basic framework for functional tests ([#16](https://github.com/auxmoney/OpentracingBundle-core/issues/16)) ([269cc72](https://github.com/auxmoney/OpentracingBundle-core/commit/269cc72524127d09be7ab401939e415df85e5e3e))


### Bug Fixes

* no faulty host resolving ([#15](https://github.com/auxmoney/OpentracingBundle-core/issues/15)) ([17b77d1](https://github.com/auxmoney/OpentracingBundle-core/commit/17b77d17b1f367d3a6ce8232368464ee45b20ef2))

### [0.3.4](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.3...v0.3.4) (2020-01-24)


### Bug Fixes

* add request spanning service definition ([#12](https://github.com/auxmoney/OpentracingBundle-core/issues/12)) ([346b5ce](https://github.com/auxmoney/OpentracingBundle-core/commit/346b5cee56a7afea53ef3d8594568d6566fa6f04))

### [0.3.3](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.2...v0.3.3) (2020-01-17)


### Features

* add PSR-18 client support ([#11](https://github.com/auxmoney/OpentracingBundle-core/issues/11)) ([280d53f](https://github.com/auxmoney/OpentracingBundle-core/commit/280d53f0209a61814188d2c5833acd2ae3ad1145))

### [0.3.2](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.1...v0.3.2) (2020-01-16)


### Bug Fixes

* downgrade opentracing ([#10](https://github.com/auxmoney/OpentracingBundle-core/issues/10)) ([5f2d137](https://github.com/auxmoney/OpentracingBundle-core/commit/5f2d13711c75b6468f40659a256df1ed24f5eef4))

### [0.3.1](https://github.com/auxmoney/OpentracingBundle-core/compare/v0.3.0...v0.3.1) (2020-01-16)


### Bug Fixes

* correct installation hint ([#9](https://github.com/auxmoney/OpentracingBundle-core/issues/9)) ([9911e06](https://github.com/auxmoney/OpentracingBundle-core/commit/9911e062f4c2b7b80d3fa76f4cd071fdf00da405))

## 0.3.0 (2020-01-16)
