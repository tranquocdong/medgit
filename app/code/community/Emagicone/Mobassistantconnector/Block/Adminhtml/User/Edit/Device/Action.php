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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    public function render(Varien_Object $row)
    {
        $actions = array();
        $status = (int)$row->getData('status');

        if ($row->getData('account_email')) {
            $actions[] = array(
                'caption' => $status == 1
                    ? Mage::helper('mobassistantconnector')->__('Disable&nbsp;Account')
                    : Mage::helper('mobassistantconnector')->__('Enable&nbsp;Account'),
                'url' => $this->getUrl(
                    '*/*/changeStatusAccount',
                    array(
                        'push_id' => $row->getData('id'),
                        'user_id' => $this->getRequest()->getParam('user_id'),
                        'value'   => $status == 1 ? 0: 1
                    )
                ),
                'field' => 'id',
                'value' => $row->getData('account_id'),
                'data-column' => 'action'
            );
        }

        $actions[] = array(
            'caption' => Mage::helper('mobassistantconnector')->__('Delete&nbsp;Row'),
            'url' => $this->getUrl(
                '*/*/deleteDevice',
                array(
                    'push_id' => $row->getData('id'),
                    'user_id' => $this->getRequest()->getParam('user_id')
                )
            ),
            'confirm' => Mage::helper('mobassistantconnector')
                ->__('Are you sure you want to delete selected record?'),
            'field' => 'id',
            'data-column' => 'action'
        );

        $this->getColumn()->setData('actions', $actions);

        return parent::render($row);
    }

}
