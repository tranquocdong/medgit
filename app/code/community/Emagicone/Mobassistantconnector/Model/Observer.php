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

class Emagicone_Mobassistantconnector_Model_Observer
{

    private static function getActiveDevices()
    {
        $pushesCollection = Mage::getModel('emagicone_mobassistantconnector/push')->getCollection();
        $pushesCollection->getSelect()
            ->joinLeft(
                array('d' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/device')),
                'main_table.`device_unique_id` = d.`device_unique_id`',
                array()
            )
            ->joinLeft(
                array('a' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/account')),
                'a.`id` = d.`account_id`',
                array()
            )
            ->joinLeft(
                array('u' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/user')),
                'u.`user_id` = main_table.`user_id`',
                array()
            )
            ->where('a.`status` = 1 AND u.`status` = 1 OR main_table.`user_id` IS NULL');

        return $pushesCollection;
    }

    private static function sendRequestAboutOrder($device, $type, $order, $statusLabel)
    {
        $currencyCode = $order->getGlobalCurrencyCode();

        $deviceCurrencyCode = $device->getCurrencyCode();
        if (empty($deviceCurrencyCode) || (string)$deviceCurrencyCode == 'base_currency') {
            $deviceCurrencyCode = $currencyCode;
        }

        $total = $order->getBaseGrandTotal();
        $total = number_format((float)$total, 2, '.', ' ');
        $total = Mage::helper('mobassistantconnector')
            ->price_format($currencyCode, 1, $total, $deviceCurrencyCode, 0, true);

        $fields = array(
            'registration_ids' => array($device->getDeviceId()),
            'data' => array(
                'message' => array(
                    'push_notif_type'   => $type,
                    'email'             => $order->getCustomerEmail(),
                    'customer_name'     => "{$order->getCustomerFirstname()} {$order->getCustomerLastname()}",
                    'order_id'          => $order->getId(),
                    'total'             => $total,
                    'store_url'         => self::getBaseStoreUrl(),
                    'group_id'          => $order->getStore()->getGroupId(),
                    'app_connection_id' => $device->getAppConnectionId()
                )
            )
        );

        if ($type == 'order_changed') {
            $fields['data']['message']['new_status'] = $statusLabel;
        }

        self::sendPushMessage(Mage::helper('core')->jsonEncode($fields), $device->getDeviceId());
    }

    private static function sendPushMessage($data, $deviceRegistrationId)
    {
        $apiKey = Mage::getStoreConfig('mobassistantconnectorinfosec/access/api_key');

        if (!$apiKey) {
            return;
        }

        $headers = array("Authorization: key=$apiKey", 'Content-Type: application/json');
        $result = false;

        if (is_callable('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                Mage::log(
                    "Push message error while sending CURL request: {$result}",
                    null,
                    'emagicone_mobassistantconnector.log'
                );
            }

            curl_close($ch);
        }

        self::proceedGoogleResponse($result, $deviceRegistrationId);
    }

    private static function proceedGoogleResponse($response, $deviceRegistrationId)
    {
        $json = array();

        if ($response && strpos($response, '{') === 0) {
            try {
                $json = Mage::helper('core')->jsonDecode($response);
            } catch (Exception $e) {
                Mage::log('Error on json decoding', null, 'emagicone_mobassistantconnector.log');
                return;
            }
        }

        if (!$json || !is_array($json) || !isset($json['results'])) {
            return;
        }

        foreach ($json['results'] as $result) {
            if (
                isset($result['registration_id']) && isset($json['canonical_ids']) && (int)$json['canonical_ids'] > 0 ||
                isset($result['error']) &&
                ($result['error'] == 'NotRegistered' || $result['error'] == 'InvalidRegistration')
            ) {
                $deviceCollection = Mage::getModel('emagicone_mobassistantconnector/push')
                    ->getCollection()
                    ->addFieldToFilter('device_id', $deviceRegistrationId);

                if (
                    isset($result['registration_id']) && isset($json['canonical_ids']) && (int)$json['canonical_ids'] > 0
                ) {
                    foreach ($deviceCollection as $device) {
                        $collection = Mage::getModel('emagicone_mobassistantconnector/push')
                            ->getCollection()
                            ->addFieldToFilter('device_id', $result['registration_id'])
                            ->addFieldToFilter('user_id', $device->getUserId())
                            ->addFieldToFilter('app_connection_id', $device->getAppConnectionId());

                        if ($collection->getSize() > 0) {
                            $device->delete();
                        } else {
                            $device->setDeviceId($result['registration_id']);
                            $device->save();
                        }
                    }
                } else {
                    foreach ($deviceCollection as $device) {
                        $device->delete();
                    }

                    Mage::helper('mobassistantconnector/deviceAndPushNotification')->deleteEmptyDevices();
                    Mage::helper('mobassistantconnector/deviceAndPushNotification')->deleteEmptyAccounts();
                    Mage::log("Google error response: {$response}", null, 'emagicone_mobassistantconnector.log');
                }
            }
        }
    }

    private static function getBaseStoreUrl()
    {
        $storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $storeUrl = str_replace('http://', '', $storeUrl);
        $storeUrl = str_replace('https://', '', $storeUrl);
        $storeUrl = rtrim($storeUrl, '/');

        return $storeUrl;
    }

    private static function isPushNotificationAllowed($userId, $code)
    {
        $userId = (int)$userId;

        if ($userId < 1) {
            return true;
        }

        $result         = false;
        $userModel      = Mage::getModel('emagicone_mobassistantconnector/user')->load($userId);
        $allowedActions = $userModel->getData('allowed_actions');

        if ($allowedActions) {
            $allowedActions = explode(';', $allowedActions);

            if (!empty($allowedActions) && in_array($code, $allowedActions)) {
                $result = true;
            }
        }

        return $result;
    }

    public function checkOrder($observer)
    {
        $order     = $observer->getEvent()->getOrder();
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();

        if ($oldStatus && $oldStatus != $newStatus) {
            $type = 'order_changed';
        } elseif (!$oldStatus) {
            $type = 'new_order';
        } else {
            return;
        }

        $orderGroupId = $order->getStore()->getGroupId();
        $statusLabel  = $newStatus;
        $devices      = self::getActiveDevices();

        $statuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        foreach ($statuses as $st) {
            if ($st['status'] == $newStatus) {
                $statusLabel = $st['label'];
                break;
            }
        }

        foreach ($devices as $device) {
            $deviceId        = $device->getDeviceId();
            $appConnectionId = (int)$device->getAppConnectionId();
            $storeGroupId    = (int)$device->getStoreGroupId();

            $deviceOrderStatuses = $device->getOrderStatuses();
            if (!empty($deviceOrderStatuses)) {
                $deviceOrderStatuses = explode('|', $deviceOrderStatuses);
            }
            if (!is_array($deviceOrderStatuses)) {
                $deviceOrderStatuses = array();
            }

            if (
                $type == 'new_order' &&
                !empty($deviceId) &&
                (int)$device->getNewOrder() == 1 &&
                $appConnectionId > 0 &&
                ($storeGroupId == -1 || $storeGroupId == $orderGroupId) &&
                self::isPushNotificationAllowed($device->getData('user_id'), 'push_notification_settings_new_order') ||
                $type == 'order_changed' &&
                !empty($deviceId) &&
                (in_array('-1', $deviceOrderStatuses) || in_array($newStatus, $deviceOrderStatuses)) &&
                $appConnectionId > 0 &&
                ($storeGroupId == -1 || $storeGroupId == $orderGroupId) &&
                self::isPushNotificationAllowed($device->getData('user_id'), 'push_notification_settings_order_statuses')
            ) {
                self::sendRequestAboutOrder($device, $type, $order, $statusLabel);
            }
        }
    }

    public function customerRegisterSuccess($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $devices  = self::getActiveDevices();

        foreach ($devices as $device) {
            $deviceId        = $device->getDeviceId();
            $appConnectionId = (int)$device->getAppConnectionId();
            $storeGroupId    = (int)$device->getStoreGroupId();

            if (
                !empty($deviceId) &&
                (int)$device->getNewCustomer() == 1 &&
                $appConnectionId > 0 &&
                ($storeGroupId == -1 || $storeGroupId == $customer->getGroupId()) &&
                self::isPushNotificationAllowed($device->getData('user_id'), 'push_notification_settings_new_customer')
            ) {
                $fields = array(
                    'registration_ids' => array($deviceId),
                    'data' => array(
                        'message' => array(
                            'push_notif_type'   => 'new_customer',
                            'email'             => $customer->getEmail(),
                            'customer_name'     => "{$customer->getFirstname()} {$customer->getLastname()}",
                            'customer_id'       => $customer->getId(),
                            'store_url'         => self::getBaseStoreUrl(),
                            'group_id'          => $storeGroupId,
                            'app_connection_id' => $appConnectionId
                        )
                    )
                );

                self::sendPushMessage(Mage::helper('core')->jsonEncode($fields), $deviceId);
            }
        }
    }

}