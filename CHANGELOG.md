# Changelog

All notable changes to `dns` will be documented in this file

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
