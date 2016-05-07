<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$configTable = $this->getTable('core/config_data');
$oldConfigPath = 'removecustomeraccountlinks/settings/remove';
$newConfigPath = 'customer/prashant_removecustomeraccountlinks/items';

$query = sprintf("UPDATE %s SET path = '%s' WHERE path = '%s'", $configTable, $newConfigPath, $oldConfigPath);
$installer->getConnection()->query($query);

$installer->endSetup();