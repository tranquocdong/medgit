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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Tab_Permissions extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareForm()
    {
        /* @var $model Emagicone_Mobassistantconnector_Model_User */
        $model = Mage::registry('mobassistantconnector_user');
        $data = array();

        if ($model) {
            $data = $model->getData();
        }

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = $this->_isAllowedAction('save') ? true : false;

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => Mage::helper('mobassistantconnector')->__('Permissions'))
        );
        $userPermissionCodes = array();

        if ($model->getId()) {
            $fieldset->addField('user_id', 'hidden', array('name' => 'user_id',));
            $userPermissionCodes = explode(';', $model->getAllowedActions());
        }

        $contentField = $fieldset->addField(
            'mass_checked',
            'hidden',
            array('name' => 'mass_checked')
        );
        $renderer = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element')
            ->setTemplate('mobassistantconnector/permissions.phtml');
        $contentField->setRenderer($renderer);

        $permissions = Mage::helper('mobassistantconnector/userPermissions')->getRestrictedActions();
        $count = count($permissions);

        for ($i = 0; $i < $count; $i++) {
            $countChild = count($permissions[$i]['child']);
            $values = array();
            $checked = array();

            for ($j = 0; $j < $countChild; $j++) {
                if (in_array($permissions[$i]['child'][$j]['code'], $userPermissionCodes)) {
                    $checked[] = $permissions[$i]['child'][$j]['code'];
                }

                $values[] = array(
                    'label' => $permissions[$i]['child'][$j]['name'],
                    'value' => $permissions[$i]['child'][$j]['code'],
                );
            }

            $fieldset->addField(
                "permissions_$i",
                'checkboxes',
                array(
                    'name'     => 'allowed_actions[]',
                    'label'    => $permissions[$i]['group_name'],
                    'values'   => $values,
                    'value'    => $checked,
                    'class'    => 'admin__control-checkbox',
                    'disabled' => $isElementDisabled
                )
            );
        }

        $form->addValues($data);
        $this->setForm($form);
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('mobassistantconnector')->__('Permissions');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('mobassistantconnector')->__('Permissions');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('user/' . $action);
    }

}
