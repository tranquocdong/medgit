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

class Emagicone_Mobassistantconnector_Model_Login extends Mage_Core_Model_Config_Data
{
    public function save()
    {
		Mage::app()->cleanCache();
        $old_login = Mage::getStoreConfig('mobassistantconnectorinfosec/emoaccess/login');
		
        $login = $this->getValue();

        if($login == ''){
            Mage::getSingleton('core/session')->addWarning(Mage::helper('mobassistantconnector/data')->__('<span style="color:green">Mobile Assistant Connector:</span> Login field cannot be empty.  Please specify Login.'));
			$this->setValue($old_login);
        }

        $sessions = Mage::getModel("emagicone_mobassistantconnector/sessions")->getCollection();
        foreach ($sessions as $session) {
            $session->delete();
        }

        return parent::save();
    }
}