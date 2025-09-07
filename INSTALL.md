# Magento Console Installation Commands

This document describes the new console installation commands that replace the traditional `install.php` script.

## Available Commands

### 1. `install:run` - Install Magento

This command installs Magento using the console interface, replacing the traditional `install.php` script.

#### Usage

```bash
php bin/console install:run [options]
```

#### Required Options

- `--license_agreement_accepted=yes` - Accept license agreement
- `--locale=en_US` - Locale (e.g., en_US, de_DE, fr_FR)
- `--timezone="America/Los_Angeles"` - Time zone
- `--default_currency=USD` - Default currency
- `--db_host=localhost` - Database host
- `--db_name=magento_database` - Database name
- `--db_user=magento_user` - Database username
- `--url="http://magento.example.com/"` - Store URL
- `--use_rewrites=yes` - Use web server rewrites
- `--use_secure=yes` - Use secure URLs
- `--secure_base_url="https://magento.example.com/"` - Secure base URL
- `--use_secure_admin=yes` - Use secure admin
- `--admin_lastname=Owner` - Admin last name
- `--admin_firstname=Store` - Admin first name
- `--admin_email="admin@example.com"` - Admin email
- `--admin_username=admin` - Admin username
- `--admin_password=password123` - Admin password

#### Optional Options

- `--db_model=mysql4` - Database type (mysql4 by default)
- `--db_pass=password` - Database password
- `--db_prefix=magento_` - Database table prefix
- `--skip_url_validation=yes` - Skip URL validation
- `--encryption_key="YourEncryptionKey"` - Encryption key (auto-generated if not provided)
- `--session_save=files` - Session save method (files/db)
- `--admin_frontname=admin` - Admin front name (admin by default)
- `--enable_charts=yes` - Enable charts

#### Example

```bash
php bin/console install:run \
  --license_agreement_accepted=yes \
  --locale=en_US \
  --timezone="America/Los_Angeles" \
  --default_currency=USD \
  --db_host=localhost \
  --db_name=magento_database \
  --db_user=magento_user \
  --db_pass=password123 \
  --url="http://magento.example.com/" \
  --use_rewrites=yes \
  --use_secure=yes \
  --secure_base_url="https://magento.example.com/" \
  --use_secure_admin=yes \
  --admin_lastname=Owner \
  --admin_firstname=Store \
  --admin_email="admin@example.com" \
  --admin_username=admin \
  --admin_password=password123
```

### 2. `install:status` - Check Installation Status

This command checks whether Magento is installed and displays version information.

#### Usage

```bash
php bin/console install:status
```

#### Output

- Shows if Magento is installed or not
- Displays version and edition information if installed

### 3. `install:options` - Display Available Options

This command displays all available options for Magento installation including supported locales, currencies, and timezones.

#### Usage

```bash
php bin/console install:options
```

#### Output

- Lists all available locales
- Lists all available currencies
- Lists all available timezones

## Environment Configuration

The installation commands can also use the `env.php` file for configuration. The `env.php` file should contain an array with the same structure as the `local.xml` file but in PHP array format.

### Example env.php

```php
<?php
return [
    'global' => [
        'install' => [
            'date' => 'Sun, 07 Sep 2025 00:25:28 +0000'
        ],
        'crypt' => [
            'key' => '9e2a0be4dfde5e1ce4c2caa098b8398f'
        ],
        'disable_local_modules' => false,
        'resources' => [
            'db' => [
                'table_prefix' => ''
            ],
            'default_setup' => [
                'connection' => [
                    'host' => 'db',
                    'username' => 'magento',
                    'password' => 'magento',
                    'dbname' => 'magento',
                    'initStatements' => 'SET NAMES utf8',
                    'model' => 'mysql4',
                    'type' => 'pdo_mysql',
                    'pdoType' => '',
                    'active' => 1
                ]
            ]
        ],
        'session_save' => 'files'
    ],
    'admin' => [
        'routers' => [
            'adminhtml' => [
                'args' => [
                    'frontName' => 'admin'
                ]
            ]
        ]
    ]
];
```

## Migration from install.php

The new console commands provide the same functionality as the traditional `install.php` script but with a more modern interface:

1. **Better error handling** - Clear error messages and proper exit codes
2. **Consistent interface** - Uses Symfony Console components
3. **Environment support** - Can use `env.php` for configuration
4. **Modular design** - Separate commands for different operations
5. **Better documentation** - Built-in help and option descriptions

## Troubleshooting

### Common Issues

1. **"Magento is already installed"** - Use `install:status` to check current status
2. **Database connection errors** - Verify database credentials and connectivity
3. **Permission errors** - Ensure proper file permissions for var/ directories
4. **URL validation errors** - Use `--skip_url_validation=yes` if needed

### Getting Help

Use the built-in help system:

```bash
php bin/console install:run --help
php bin/console install:status --help
php bin/console install:options --help
```

## Security Notes

- Never commit `env.php` or `local.xml` files to version control
- Use strong passwords for admin accounts
- Generate secure encryption keys
- Ensure proper file permissions after installation
