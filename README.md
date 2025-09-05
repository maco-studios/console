# Maco Console Module

A Magento 1 console module that provides Magento 2-style CLI commands for cache management, database seeding, and indexer operations.

## Overview

This module brings Magento 2's command-line interface experience to Magento 1, offering familiar commands like `cache:clean`, `cache:flush`, `indexer:reindex`, and `db:seed` that developers expect from modern Magento installations.

## Installation

```bash
composer require maco-studios/console
```

## Features

- **Cache Management**: Clean, flush, enable/disable cache types with granular control
- **Indexer Operations**: Reindex, invalidate, and manage indexer modes
- **Database Seeding**: Seed database with test data using factory patterns
- **Magento 2 Compatibility**: Commands follow Magento 2 CLI conventions and syntax
- **Configurable Commands**: Commands are registered via config.xml for easy extension
- **Fallback Support**: Works even when database connection fails (for cache operations)

## Usage

Run commands using the console script:

```bash
php bin/console [command] [options]
```

## Available Command Categories

- **Cache Commands**: `cache:clean`, `cache:flush`, `cache:enable`, `cache:disable`, `cache:status`
- **Indexer Commands**: `indexer:reindex`, `indexer:invalidate`, `indexer:status`, `indexer:info`

## Bonus Commands

- **Database Commands**: `db:seed`, `seeder:make`, `seeder:list`
Have a look at the [Dev_Database](https://github.com/maco-studios/dev) module for more information.

## Architecture

Built on Symfony Console with Magento-specific abstractions, the module provides a base command class that handles Magento initialization, logging, and common utilities. Commands are organized by functionality and can be easily extended or customized.

## Benefits

- **Familiar Interface**: Magento 2 developers feel at home with these commands
- **Production Ready**: Includes safety checks and proper error handling
- **Extensible**: Easy to add new commands following the established patterns
- **Robust**: Handles edge cases like database connection failures gracefully
