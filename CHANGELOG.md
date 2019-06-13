# Changelog

All notable changes to `laravel-transactional-model-events` will be documented in this file

## 1.0.1 - 2019-06-13

- fix firing model events with nested transactions. Only when outer transaction is committed the model events are fired.

## 1.0.0 - 2019-05-19

- initial release
