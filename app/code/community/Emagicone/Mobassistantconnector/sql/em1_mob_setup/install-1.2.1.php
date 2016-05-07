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

$installer->startSetup();

/*$table = $installer->getConnection()
    ->newTable($installer->getTable('emo_assistantconnector/sessions'))
    ->addColumn('key', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'identity'  => false,
        'primary'   => true,
    ), 'Key')
    ->addColumn('date_added', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'nullable'  => false,
        'unsigned'  => false,
    ), 'Date added');
$installer->getConnection()->createTable($table);*/

$installer->run("
        -- DROP TABLE IF EXISTS {$this->getTable('emagicone_mobassistantconnector_sessions')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('emagicone_mobassistantconnector_sessions')} (
        `session_id` int(11) NOT NULL auto_increment,
        `session_key` varchar(100) NOT NULL default '',
        `date_added` int(11) NOT NULL,
        PRIMARY KEY (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");



/*$table = $installer->getConnection()
    ->newTable($installer->getTable('emagicone_mobassistantconnector/failed'))
    ->addColumn('ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'identity'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('date_added', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'nullable'  => false,
        'unsigned'  => false,
    ), 'Date added');
$installer->getConnection()->createTable($table);*/

$installer->run("
        -- DROP TABLE IF EXISTS {$this->getTable('emagicone_mobassistantconnector_failed_login')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('emagicone_mobassistantconnector_failed_login')} (
        `attempt_id` int(11) NOT NULL auto_increment,
        `ip` varchar(20) NOT NULL default '',
        `date_added` int(11) NOT NULL,
        PRIMARY KEY (`attempt_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");


$installer->endSetup();