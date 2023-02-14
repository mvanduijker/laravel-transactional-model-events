# Changelog

All notable changes to `laravel-transactional-model-events` will be documented in this file

## 2.7.0 - 2023-02-14

- Add support for Laravel 10
- Added PHP 8.2 in the pipeline

## 2.6.0 - 2022-02-09

- support for laravel 9

## 2.5.0 - 2021-02-05

- support for php 8

## 2.4.0 - 2020-09-08

- support for Laravel 8
- dropped support for php 7.2
- dropped support for Laravel 5.x

## 2.3.1 - 2020-08-27

- fix possible issue with Laravel Nova

## 2.3.0 - 2020-03-03

- support for laravel 7.0

## 2.2.0 - 2020-02-21

- Added support for multiple database connections

## 2.1.0 - 2020-01-07

- Removed support for php 7.1
- Added support for php 7.4
- Fixed dependencies so it properly tests against latest laravel version
- Ignore laravel version 6.9.0 which broke this package and is fixed from 6.10.0 [more information](https://github.com/laravel/framework/issues/30948)

## 2.0.0 - 2019-08-28

- Add support for observers
- removed support for laravel 5.5 and 5.6
- Added support for laravel 6.0

## 1.0.1 - 2019-06-13

- fix firing model events with nested transactions. Only when outer transaction is committed the model events are fired.

## 1.0.0 - 2019-05-19

- initial release
