# Sqala - Magento Plugin

The Sqala Magento plugin integrates our payment services into your Magento 2.x store.

## Features

- Pix payments
- Refunds
- Payment status sync

## ⚙️ Installation

You can install the plugin using Composer or manually, depending on your project setup.

### Option 1: Manual Installation

1. Download the Sqala folder and extract it into the following directory:

```bash
curl -L  https://github.com/rpo-pay/sqala-magento/archive/refs/tags/1.0.0.zip > sqala-magento.zip
unzip sqala-magento.zip
mkdir -p YOUR_MAGENTO_ROOT/app/code/Sqala/Payment && mv sqala-magento-1.0.0/* YOUR_MAGENTO_ROOT/app/code/Sqala/Payment
```

2. Register the module and compile the code

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
```

3. Clear the cache

```bash
bin/magento cache:clean
```

4. (Production only) Deploy static content:

```bash
bin/magento setup:static-content:deploy -f
```

✅ Troubleshooting Tip: If you face permission issues during installation, make sure the following directories have the correct write permissions:

var/
pub/
generated/


### Option 2: Composer Installation

Coming soon.

## ⚙️ Configuration

### Configuration in Magento Admin

Once installed, you can configure the plugin in the Magento Admin Panel:

> Stores → Configuration → Sales → Payment Methods

Look for the section Sqala Payment and fill in your credentials:

- AppId
- AppSecret
- Refresh Token
- Webhook Secret Id

Enable the desired payment method and save the configuration.

## 🔔 Webhook Setup
To ensure payment updates are processed correctly, set up the following webhook in your Sqala dashboard:

Event: Payment Paid
URL: https://YOUR_DOMAIN/sqala/webhook/notify
