# Changelog

All notable changes to `dns` will be documented in this file

## 2.4.4 - 2021-12-21

## What's Changed

- fix test in windows by @ManojKiranA in https://github.com/spatie/dns/pull/85
- add support for Symfony 6 by @Nielsvanpach in https://github.com/spatie/dns/pull/86

## New Contributors

- @Nielsvanpach made their first contribution in https://github.com/spatie/dns/pull/86

**Full Changelog**: https://github.com/spatie/dns/compare/2.4.3...2.4.4

## 2.4.3 - 2021-12-18

## What's Changed

- Fix Undefined constant DNS_CAA in windows platform by @ManojKiranA in https://github.com/spatie/dns/pull/84

## New Contributors

- @ManojKiranA made their first contribution in https://github.com/spatie/dns/pull/84

**Full Changelog**: https://github.com/spatie/dns/compare/2.4.2...2.4.3

## 2.4.2 - 2021-11-25

- make compatible with PHP 8.1
- add `time` and `tries` parameters

## 2.4.1 - 2021-11-18

## What's Changed

- Added support for long TXT records (see rfc4408, section 3.1.3) by @hostep in https://github.com/spatie/dns/pull/81

## New Contributors

- @hostep made their first contribution in https://github.com/spatie/dns/pull/81

**Full Changelog**: https://github.com/spatie/dns/compare/2.4.0...2.4.1

## 2.4.0 - 2021-11-17

## What's Changed

- Add ability to use custom handlers by @freekmurze in https://github.com/spatie/dns/pull/78

**Full Changelog**: https://github.com/spatie/dns/compare/2.3.2...2.4.0

## 2.3.2 - 2021-11-02

- fix macroable behaviour

## 2.3.1 - 2021-11-02

- allow v1 of spatie/macroable

## 2.3.0 - 2021-11-02

- make `Record` macroable

## 2.2.0 - 2021-10-19

- add `guess` method to `Factory`

## 2.1.1 - 2021-08-04

- ensure accessed indexes do exist (#68)

## 2.1.0 - 2021-08-01

- Make records arrayable (#70)

## 2.0.2 - 2021-06-01

- always only return the requests record types (#63)

## 2.0.1 - 2021-06-01

- let `getRecords` return an array instead of a custom collection

## 2.0.0 - 2021-05-20

- near-total rewrite
- added methods on record types
- added support for multiple handlers
- drop support for any PHP 7.x (require >= 8.0)

## 1.6.0 - 2020-12-02

- add `of` method

## 1.5.0 - 2020-08-24

- add `noidnout`

## 1.4.3 - 2019-11-30

- drop support for PHP 7.3 and below

## 1.4.2 - 2019-05-17

- add support for NAPTR record type
- resolve symfony/process deprecation

## 1.4.1 - 2018-12-06

- throw a custom exception when dig fails

## 1.4.0 - 2018-09-13

- add CNAME and SRV record types

## 1.3.1 - 2018-02-20

- fix tests
- allow Symfony 4

## 1.3.0 - 2017-11-29

- add support for `CAA` records

## 1.2.0 - 2017-11-29

- add `useNameserver`

## 1.1.0 - 2017-11-03

- add `getDomain`

## 1.0.0 - 2017-11-03

- initial release
