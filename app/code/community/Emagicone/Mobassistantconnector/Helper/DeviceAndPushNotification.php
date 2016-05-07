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

class Emagicone_Mobassistantconnector_Helper_DeviceAndPushNotification extends Mage_Core_Helper_Abstract
{

    public static function deletePushSettingByRegAndCon($registration_id, $app_connection_id)
    {
        $result = false;

        $pushes = Mage::getModel('emagicone_mobassistantconnector/push')
            ->getCollection()
            ->addFieldToFilter(
                array('device_id', 'app_connection_id'),
                array(array('eq' => $registration_id), array('eq' => $app_connection_id))
            );

        foreach ($pushes as $push) {
            try {
                $push->delete();
                $result = true;
            } catch (Exception $e) {
                $result = false;
                Mage::log(
                    "Unable to delete push settings by reg_id and con_id ({$e->getMessage()}).",
                    null,
                    'emagicone_mobassistantconnector.log'
                );
            }
        }

        return $result;
    }

    public static function deleteEmptyDevices()
    {
        $result = false;

        $devices = Mage::getModel('emagicone_mobassistantconnector/device')->getCollection();
        $devices->getSelect()->joinLeft(
            array('p' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/push')),
            'main_table.`device_unique_id` = p.`device_unique_id`',
            'p.device_unique_id AS dev_id'
        );
        $devices->addFieldToFilter('p.device_unique_id', array('null' => true));

        foreach ($devices as $device) {
            try {
                $device->delete();
                $result = true;
            } catch (Exception $e) {
                $result = false;
                Mage::log(
                    "Unable to delete device ({$e->getMessage()}).",
                    null,
                    'emagicone_mobassistantconnector.log'
                );
            }
        }

        return $result;
    }

    public static function deleteEmptyAccounts()
    {
        $result = false;

        $accounts = Mage::getModel('emagicone_mobassistantconnector/account')->getCollection();
        $accounts->getSelect()->joinLeft(
            array('d' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/device')),
            'main_table.`id` = d.`account_id`',
            array()
        );
        $accounts->addFieldToFilter('d.account_id', array('null' => true));

        foreach ($accounts as $account) {
            try {
                $account->delete();
                $result = true;
            } catch (Exception $e) {
                $result = false;
                Mage::log(
                    "Unable to delete account ({$e->getMessage()}).",
                    null,
                    'emagicone_mobassistantconnector.log'
                );
            }
        }

        return $result;
    }

    public static function updateOldPushRegId($registration_id_old, $registration_id_new)
    {
        $result = true;

        try {
            $pushes = Mage::getModel('emagicone_mobassistantconnector/push')
                ->getCollection()
                ->addFieldToFilter('device_id', array('eq' => $registration_id_old));

            foreach ($pushes as $push) {
                $push->setData('device_id', $registration_id_new)->save();
            }
        } catch (Exception $e) {
            $result = false;
            Mage::log(
                "Unable to update push notification ({$e->getMessage()}).",
                null,
                'emagicone_mobassistantconnector.log'
            );
        }

        return $result;
    }

    public static function addDevice($data)
    {
        $result = false;

        if (!empty($data)) {
            try {
                $device = Mage::getModel('emagicone_mobassistantconnector/device')
                    ->loadByDeviceUniqueAndAccountId($data['device_unique'], $data['account_id'])
                    ->setData('device_name', $data['device_name'])
                    ->setData('last_activity', $data['last_activity'])
                    ->save();

                $result = $device->getId();
            } catch (Exception $e) {
                Mage::log("Unable to insert device ({$e->getMessage()}).", null, 'emagicone_mobassistantconnector.log');
            }
        }

        return $result;
    }

    public static function addPushNotification($data)
    {
        $result = true;

        try {
            $push = Mage::getModel('emagicone_mobassistantconnector/push')
                ->loadByRegistrationIdAppConnectionId($data['device_id'], $data['app_connection_id'])
                ->setData('device_unique_id', $data['device_unique_id'])
                ->setData('user_id', $data['user_id'])
                ->setData('store_group_id', $data['store_group_id'])
                ->setData('currency_code', $data['currency_code']);

            if (isset($data['new_order'])) {
                $push->setData('new_order', $data['new_order']);
            }

            if (isset($data['new_customer'])) {
                $push->setData('new_customer', $data['new_customer']);
            }

            if (isset($data['order_statuses'])) {
                $push->setData('order_statuses', $data['order_statuses']);
            }

            $push->save();
        } catch (Exception $e) {
            $result = false;
            Mage::log("Unable to add push notification ({$e->getMessage()}).", null, 'emagicone_mobassistantconnector.log');
        }

        return $result;
    }

}