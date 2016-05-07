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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('emagicone_mobassistantconnector_user_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('user_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'emagicone_mobassistantconnector/user_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'user_id',
            array(
                'header' => Mage::helper('mobassistantconnector')->__('ID'),
                'type'   => 'text',
                'index'  => 'user_id',
                'width'  => '80px'
            )
        );

        $this->addColumn(
            'username',
            array(
                'header' => Mage::helper('mobassistantconnector')->__('Username'),
                'type'   => 'text',
                'index'  => 'username',
            )
        );

        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('mobassistantconnector')->__('Status'),
                'width'   => '100px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Emagicone_Mobassistantconnector_Model_User::getStatuses(),
            )
        );

        $this->addColumn(
            'allowed_actions',
            array(
                'header'   => Mage::helper('mobassistantconnector')->__('Permissions'),
                'type'     => 'text',
                'index'    => 'allowed_actions',
                'renderer' => 'Emagicone_Mobassistantconnector_Block_Adminhtml_User_Renderer_Permissions',
                'filter'   => false
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'  => Mage::helper('mobassistantconnector')->__('Action'),
                'width'   => '100px',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption'     => Mage::helper('mobassistantconnector')->__('Edit'),
                        'url'         => array('base'=>'*/*/edit'),
                        'field'       => 'user_id',
                        'data-column' => 'action',
                    ),
                    array(
                        'caption'     => Mage::helper('mobassistantconnector')->__('Delete'),
                        'url'         => array('base'=>'*/*/delete'),
                        'field'       => 'user_id',
                        'data-column' => 'action',
                        'confirm'     => Mage::helper('mobassistantconnector')
                            ->__('Are you sure you want to delete selected user?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('user_id');
        $this->getMassactionBlock()->setFormFieldName('user_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()
            ->addItem(
                'enable',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('Enable'),
                    'url'   => $this->getUrl('*/user/massEnable'),
                )
            )
            ->addItem(
                'disable',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('Disable'),
                    'url'   => $this->getUrl('*/user/massDisable'),
                )
            )
            ->addItem(
                'delete',
                array(
                    'label'   => Mage::helper('mobassistantconnector')->__('Delete'),
                    'url'     => $this->getUrl('*/user/massDelete'),
                    'confirm' => Mage::helper('mobassistantconnector')
                        ->__('Are you sure you want to delete selected users?')
                )
            );

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('user_id' => $row->getId()));
    }

}
