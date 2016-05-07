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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();

        $this->setId('user_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('User Information'));
    }

    protected function _beforeToHtml()
    {
        /*$this->addTab(
            'main_section',
            array(
                'label'   => Mage::helper('mobassistantconnector')->__('User Information'),
                'title'   => Mage::helper('mobassistantconnector')->__('User Information'),
                'content' => $this->getLayout()->createBlock('mobassistantconnector/adminhtml_user_edit_tab_main')
                    ->toHtml(),
            )
        );

        $this->addTab(
            'permissions_section',
            array(
                'label'   => Mage::helper('mobassistantconnector')->__('Permissions'),
                'title'   => Mage::helper('mobassistantconnector')->__('Permissions'),
                'content' => $this->getLayout()->createBlock('mobassistantconnector/adminhtml_user_edit_tab_permissions')
                    ->toHtml(),
            )
        );*/

        if ($this->getRequest()->getParam('user_id', false)) {
            $this->addTab(
                'devices_section',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('Devices'),
                    'title' => Mage::helper('mobassistantconnector')->__('Devices'),
//                    'content' => $this->getLayout()->createBlock('mobassistantconnector/adminhtml_user_edit_device_grid')
//                        ->toHtml(),
                    'url' => $this->getUrl('*/*/devices', array('_current' => true)),
                    'class' => 'ajax'
                )
            );
        }

        return parent::_beforeToHtml();
    }

}
