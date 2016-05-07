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

class Emagicone_Mobassistantconnector_Helper_Access extends Mage_Core_Helper_Abstract
{
    const HASH_ALGORITHM = 'sha256';
    const MAX_LIFETIME = 43200; /* 12 hours */
//    const TABLE_SESSION_KEYS = 'emagicone_mobassistantconnector_sessions';
//    const TABLE_FAILED_ATTEMPTS = 'emagicone_mobassistantconnector_failed_login';

    public static function clearOldData()
    {
        $timestamp = time();
        $date = Mage::getStoreConfig('mobassistantconnectorinfosec/access/cl_date');
        $dateDelete = $timestamp - self::MAX_LIFETIME;

        if ($date === false || ($timestamp - (int)$date) > self::MAX_LIFETIME) {
            // Delete old session keys
            $sessions = Mage::getModel('emagicone_mobassistantconnector/sessions')->getCollection()
                ->addFieldToFilter('date_added', array('lt' => $dateDelete));
            foreach ($sessions as $session) {
                $session->delete();
            }

            // Delete old failed logins
            $attempts = Mage::getModel('emagicone_mobassistantconnector/failed')->getCollection()
                ->addFieldToFilter('date_added', array('lt' => $dateDelete));
            foreach ($attempts as $attempt) {
                $attempt->delete();
            }

            // Update clearing date in core_config_data table
            Mage::getModel('core/config')->saveConfig('mobassistantconnectorinfosec/access/cl_date', $timestamp);
        }
    }

    public static function getSessionKey($hash, $user_id = false)
    {
        if (!$user_id) {
            $login_data = self::checkAuth($hash);

            if ($login_data) {
                $user_id = (int)$login_data['user_id'];
            }
        }

        if ($user_id) {
            return self::generateSessionKey($user_id);
        }

        self::addFailedAttempt();
        return false;
    }

    public static function checkSessionKey($key, $user_id = false)
    {
        $sessions = Mage::getModel('emagicone_mobassistantconnector/sessions')
            ->getCollection()
            ->addFieldToFilter('date_added', array('gt' => (time() - self::MAX_LIFETIME)))
            ->addFieldToFilter('session_key', array('eg' => $key));

        if ($user_id) {
            $sessions->addFieldToFilter('user_id', array('eg' => (int)$user_id));
        }

        if ($sessions->getSize() > 0) {
            return true;
        }

        self::addFailedAttempt();
        return false;
    }

    public static function generateSessionKey($user_id)
    {
        $timestamp = time();
        $sessions = Mage::getModel('emagicone_mobassistantconnector/sessions')
            ->getCollection()
            ->addFieldToFilter('date_added', array('gt' => ($timestamp - self::MAX_LIFETIME)))
            ->addFieldToFilter('user_id', array('eg' => (int)$user_id));

        foreach ($sessions as $session) {
            return $session->getSessionKey();
        }

        $enc_key = (string)Mage::getConfig()->getNode('global/crypt/key');
        $key = hash(self::HASH_ALGORITHM, $enc_key . $user_id . $timestamp);

        Mage::getModel('emagicone_mobassistantconnector/sessions')
            ->loadByUserId($user_id)
            ->setData(array('user_id' => $user_id, 'session_key' => $key, 'date_added' => $timestamp))
            ->save();

        return $key;
    }

    public static function addFailedAttempt()
    {
        $timestamp = time();

        // Add data to database
        Mage::getModel('emagicone_mobassistantconnector/failed')
            ->setData(array('ip' => $_SERVER['REMOTE_ADDR'], 'date_added' => $timestamp))
            ->save();

        // Get count of failed attempts for last time and set delay
        $attempts = Mage::getModel('emagicone_mobassistantconnector/failed')->getCollection()
            ->addFieldToFilter('date_added', array('gt' => ($timestamp - self::MAX_LIFETIME)))
            ->addFieldToFilter('ip', array('eq' => $_SERVER['REMOTE_ADDR']));
        $count_failed_attempts = $attempts->getSize();

        self::setDelay((int)$count_failed_attempts);
    }

    public static function checkAuth($hash, $is_log = false)
    {
        $users = Mage::getModel('emagicone_mobassistantconnector/user')->getCollection()
            ->addFieldToFilter('status', array('eq' => 1));

        foreach ($users as $user) {
            if (hash(self::HASH_ALGORITHM, $user->getUsername() . $user->getPassword()) == $hash) {
                return $user->toArray();
            }
        }

        if ($is_log) {
            Mage::log('Hash accepted is incorrect', null, 'emagicone_mobassistantconnector.log');
        }

        return false;
    }

    public static function getAllowedActionsBySessionKey($key)
    {
        $result = array();
        $sessionKey = Mage::getModel('emagicone_mobassistantconnector/sessions')->load($key, 'session_key');

        if (!$sessionKey->getId()) {
            return $result;
        }

        $user = Mage::getModel('emagicone_mobassistantconnector/user')->load($sessionKey->getUserId());
        $allowedActions = $user->getAllowedActions();

        if (!empty($allowedActions)) {
            $result = explode(';', $allowedActions);
        }

        return $result;
    }

    public static function getAllowedActionsByUserId($user_id)
    {
        $result = array();
        $user = Mage::getModel('emagicone_mobassistantconnector/user')->load($user_id);

        if (!$user->getId()) {
            return $result;
        }

        $allowedActions = $user->getAllowedActions();

        if (!empty($allowedActions)) {
            $result = explode(';', $allowedActions);
        }

        return $result;
    }

    public static function getUserIdBySessionKey($key)
    {
        $user_id = false;
        $users = Mage::getModel('emagicone_mobassistantconnector/user')->getCollection();
        $users->getSelect()
            ->joinLeft(
                array('k' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/sessions')),
                'k.user_id = main_table.user_id'
            )
            ->where("k.session_key = '$key' AND main_table.`status` = 1");

        foreach ($users as $user) {
            $user_id = $user->getUserId();
            break;
        }

        return $user_id;
    }

    private static function setDelay($count_attempts)
    {
        if ($count_attempts > 3 && $count_attempts <= 10) {
            sleep(1);
        } elseif ($count_attempts <= 20) {
            sleep(5);
        } elseif ($count_attempts <= 50) {
            sleep(10);
        } else {
            sleep(20);
        }
    }

}