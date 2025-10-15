# Mage2 Module Variux EmailNotification

    ``variux/module-emailnotification``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Magento Admin Bulk Emails Alert

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Variux`
 - Enable the module by running `php bin/magento module:enable Variux_EmailNotification`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require variux/module-emailnotification`
 - enable the module by running `php bin/magento module:enable Variux_EmailNotification`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - Enable (bulkemails/general/enabled)

 - Max limit of sent emails (bulkemails/general/max_sentemails)

 - Defined duration (bulkemails/general/duration_threshold)

 - Email Sender (bulkemails/email_template/sender)

 - Email Recevice (bulkemails/email_template/recevice)

 - Template (bulkemails/email_template/template)

 - Email Copy To (bulkemails/email_template/copyto)

 - Email Copy Method (bulkemails/email_template/copyto_method)


## Specifications

 - Controller
	- adminhtml > variux_emailnotification/bulkEmailLogs/index

 - Controller
	- adminhtml > variux_emailnotification/bulkEmailLogs/clear

 - Controller
	- adminhtml > variux_emailnotification/bulkEmailLogs/resume

 - Helper
	- Variux\EmailNotification\Helper\Config

 - Helper
	- Variux\EmailNotification\Helper\Data

 - Model
	- BulkEmailLogs

 - Plugin
	- aroundSendMessage - Magento\Framework\Mail\TransportInterface > Variux\EmailNotification\Plugin\Magento\Framework\Mail\TransportInterface


## Attributes



