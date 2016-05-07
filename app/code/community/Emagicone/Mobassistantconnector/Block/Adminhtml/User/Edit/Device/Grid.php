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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_massactionBlockName = 'mobassistantconnector/adminhtml_user_edit_device_massAction';

    public function __construct()
    {
        parent::__construct();

        $this->setId('emagicone_mobassistantconnector_device_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('device_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'emagicone_mobassistantconnector/push_collection';
    }

    protected function _prepareCollection()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');

        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()
            ->joinLeft(
                array('d' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/device')),
                'd.device_unique_id = main_table.device_unique_id',
                array('device_name', 'last_activity', 'account_id')
            )
            ->joinLeft(
                array('a' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/account')),
                'a.id = d.account_id',
                array('account_email', 'status')
            )
            ->where("main_table.user_id = $userId");

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'device_name',
            array(
                'header' => Mage::helper('mobassistantconnector')->__('Device Name'),
                'type'   => 'text',
                'index'  => 'device_name',
            )
        );

        $this->addColumn(
            'account_email',
            array(
                'header' => Mage::helper('mobassistantconnector')->__('Account Email'),
                'type'   => 'text',
                'index'  => 'account_email',
            )
        );

        $this->addColumn(
            'last_activity',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('Last Activity'),
                'type'     => 'date',
                'index'    => 'last_activity',
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Date'
            )
        );

        $this->addColumn(
            'app_connection_id',
            array(
                'header' => Mage::helper('mobassistantconnector')->__('App Connection ID'),
                'type'   => 'text',
                'index'  => 'app_connection_id',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                    'header'   => Mage::helper('mobassistantconnector')->__('Store'),
                    'index'    => 'store_group_id',
                    'filter'   => false,
                    'type'     => 'text',
                    'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Store'
                )
            );
        }

        $this->addColumn(
            'new_order',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('New Order'),
                'type'     => 'text',
                'filter'   => false,
                'index'    => 'new_order',
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_NewOrder'
            )
        );

        $this->addColumn(
            'new_customer',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('New Customer'),
                'type'     => 'text',
                'filter'   => false,
                'index'    => 'new_customer',
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_NewCustomer'
            )
        );

        $this->addColumn(
            'order_statuses',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('Order Statuses'),
                'type'     => 'text',
                'filter'   => false,
                'index'    => 'order_statuses',
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_OrdStatuses'
            )
        );

        $this->addColumn(
            'currency_code',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('Currency Code'),
                'type'     => 'text',
                'filter'   => false,
                'index'    => 'currency_code',
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Currency'
            )
        );

        $this->addColumn(
            'account_status',
            array(
                'header'  => Mage::helper('mobassistantconnector')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    1 => Mage::helper('mobassistantconnector')->__('Enabled'),
                    0 => Mage::helper('mobassistantconnector')->__('Disabled')
                )
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('Action'),
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Action',
                'type'     => 'action',
                'filter'   => false
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('push_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()
            ->addItem(
                'enable',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('Enable Accounts'),
                    'url'   => $this->getUrl(
                        '*/*/massChangeStatusAccount',
                        array('value' => 1, 'user_id' => $this->getRequest()->getParam('user_id'))
                    )
                )
            )
            ->addItem(
                'disable',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('Disable Accounts'),
                    'url'   => $this->getUrl(
                        '*/*/massChangeStatusAccount',
                        array('value' => 0, 'user_id' => $this->getRequest()->getParam('user_id'))
                    )
                )
            )
            ->addItem(
                'delete',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('Delete Rows'),
                    'url'   => $this->getUrl(
                        '*/*/massDeleteDevice',
                        array('user_id' => $this->getRequest()->getParam('user_id'))
                    ),
                    'confirm' => Mage::helper('mobassistantconnector')
                        ->__('Are you sure you want to delete selected records?')
                )
            );

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/devices', array('_current' => true));
    }

}
