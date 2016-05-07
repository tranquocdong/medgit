<?php
/**
 *    This file is part of Mobile Assistant Connector.
 *
 *   Mobile Assistant Connector is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Mobile Assistant Connector is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Mobile Assistant Connector.  If not, see <http://www.gnu.org/licenses/>.
 */

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

// Create table emagicone_mobassistantconnector_sessions
$tableName = $installer->getTable('emagicone_mobassistantconnector/sessions');
if (!$connection->isTableExists($tableName)) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'session_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'       => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
                'auto_increment' => true
            ),
            'Session Id'
        )
        ->addColumn('session_key', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array('nullable' => false), 'Session Key')
        ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'User Id')
        ->addColumn('date_added', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Date Added');
    $connection->createTable($table);
} else {
    $keysCollection = Mage::getModel('emagicone_mobassistantconnector/sessions')->getCollection();
    try {
        foreach ($keysCollection as $key) {
            $key->delete();
        }
    } catch (Exception $e) {
        Mage::log(
            'Error occurred while deleting session keys: ' . $e->getMessage(), null, 'emagicone_mobassistantconnector.log'
        );
    }

    if (!$connection->tableColumnExists($tableName, 'user_id')) {
        $connection->addColumn(
            $tableName,
            'user_id',
            array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 'comment' => 'User Id')
        );
        $connection->resetDdlCache($tableName);
    }
}

// Create table emagicone_mobassistantconnector_failed_login
$tableName = $installer->getTable('emagicone_mobassistantconnector/failed');
if (!$connection->isTableExists($tableName)) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'attempt_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'       => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
                'auto_increment' => true
            ),
            'Attempt Id'
        )
        ->addColumn('ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Ip')
        ->addColumn('date_added', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Date Added');
    $connection->createTable($table);
}

// Create table emagicone_mobassistantconnector_users
$tableName = $installer->getTable('emagicone_mobassistantconnector/user');
if (!$connection->isTableExists($tableName)) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'user_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'       => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
                'auto_increment' => true
            ),
            'User Id'
        )
        ->addColumn('username', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array('nullable' => false), 'Username')
        ->addColumn('password', Varien_Db_Ddl_Table::TYPE_VARCHAR, 35, array('nullable' => false), 'Password')
        ->addColumn('allowed_actions', Varien_Db_Ddl_Table::TYPE_VARCHAR, 1000, array(), 'Allowed Actions')
        ->addColumn('qr_code_hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(), 'QR Code Hash')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(), 'Status')
        ->addIndex($installer->getIdxName($tableName, array('username')), array('username'));
    $connection->createTable($table);
}

// Create table emagicone_mobassistantconnector_push_notifications
$tableName = $installer->getTable('emagicone_mobassistantconnector/push');
if (!$connection->isTableExists($tableName)) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'       => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
                'auto_increment' => true
            ),
            'Id'
        )
        ->addColumn('device_unique_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Device Unique Id')
        ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'User Id')
        ->addColumn('device_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 200, array(), 'Device Id')
        ->addColumn('new_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(), 'New Order')
        ->addColumn('new_customer', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(), 'New Customer')
        ->addColumn('order_statuses', Varien_Db_Ddl_Table::TYPE_VARCHAR, 1000, array(), 'Order Statuses')
        ->addColumn('app_connection_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(), 'App Connection Id')
        ->addColumn('store_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array('default' => -1), 'Store Group Id')
        ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 25, array(), 'Currency Code');
    $connection->createTable($table);
}

// Create table emagicone_mobassistantconnector_devices
$tableName = $installer->getTable('emagicone_mobassistantconnector/device');
if (!$connection->isTableExists($tableName)) {
    $table = $connection->newTable($tableName)
        ->addColumn(
            'device_unique_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'       => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
                'auto_increment' => true
            ),
            'Device Unique Id'
        )
        ->addColumn('device_unique', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(), 'Device Unique')
        ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => true), 'Account Id')
        ->addColumn('device_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 150, array(), 'Device Name')
        ->addColumn('last_activity', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array('nullable' => false), 'Last Activity')
        ->addIndex(
            $installer->getIdxName($tableName, array('device_unique', 'account_id')), array('device_unique', 'account_id')
        );
    $connection->createTable($table);
}

// Create table emagicone_mobassistantconnector_accounts
$tableName = $installer->getTable('emagicone_mobassistantconnector/account');
if (!$connection->isTableExists($tableName)) {
    $table = $connection->newTable($tableName)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'       => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
                'auto_increment' => true
            ),
            'Id'
        )
        ->addColumn('account_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 150, array('nullable' => false), 'Account Email')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(), 'Status');
    $connection->createTable($table);
}

$installer->endSetup();