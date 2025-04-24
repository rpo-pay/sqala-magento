# Sqala - Magento Plugin

The Sqala Magento plugin integrates our payment services into your Magento 2.x store.

## Features

- Pix payments
- Refunds
- Payment status sync

## ⚙️ Installation Guide (Magento 2)

Step 1: Access your Magento root directory

```bash
cd /var/www/html/magento
```

Step 2: Require the Sqala Plugin via Composer

```bash
composer require sqala/magento
```

Step 3: Enable the plugin

```bash
php bin/magento module:enable Sqala_Payment
php bin/magento setup:upgrade
```

Step 4: Deploy static content and recompile (if in production)

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

