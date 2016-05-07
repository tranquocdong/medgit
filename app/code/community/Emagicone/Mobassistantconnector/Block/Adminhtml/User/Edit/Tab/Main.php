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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
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
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('mobassistantconnector')->__('User Information')));

        if ($model->getId()) {
            $fieldset->addField('user_id', 'hidden', array('name' => 'user_id',));
        }

        $fieldset->addField(
            'status',
            'select',
            array(
                'label'     => Mage::helper('mobassistantconnector')->__('Status'),
                'title'     => Mage::helper('mobassistantconnector')->__('User Status'),
                'name'      => 'status',
                'options'   => $model->getStatuses(),
                'note'      => 'If status is disabled user will not retrieve data in Magento Mobile Assistant App.',
                'disabled'  => $isElementDisabled,
            )
        );

        $fieldset->addField(
            'username',
            'text',
            array(
                'name'      => 'username',
                'label'     => Mage::helper('mobassistantconnector')->__('Username'),
                'title'     => Mage::helper('mobassistantconnector')->__('Username'),
                'required'  => true,
                'note'      => 'Login for accessing Mobile Assistant Connector from Magento Mobile Assistant App.',
                'disabled'  => $isElementDisabled
            )
        );

        $fieldset->addField(
            'password',
            'password',
            array(
                'name'     => 'password',
                'label'    => Mage::helper('mobassistantconnector')->__('Password'),
                'title'    => Mage::helper('mobassistantconnector')->__('Password'),
                'required' => true,
                'note'     => 'Password for accessing Mobile Assistant Connector from Magento Mobile Assistant App.',
                'disabled' => $isElementDisabled
            )
        );

        if ($model->getId()) {
            $hash = (string)$model->getQrCodeHash();
//            $baseUrl = $this->getBaseUrl();
//            $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $url = "{$this->getBaseUrl()}mobassistantconnector/index?call_function=get_qr_code&hash=$hash";
            $fieldset->addField(
                'qr_code_data',
                'hidden',
                array(
                    'name'  => 'qr_code_data',
                    'value' => Mage::helper('mobassistantconnector/data')->getDataToQrCode(
                        Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
                        $model->getUsername(),
                        $model->getPassword()
                    )
                )
            );

            $fieldset->addField(
                'qrcode_image',
                'label',
                array(
                    'label' => Mage::helper('mobassistantconnector')->__('QR-code'),
                    'value' => '',
                    'note' => 'Store URL and access details (login and password) for Mobile Assistant Connector are encoded
                        in this QR code. Scan it with special option available on connection settings page of Magento
                        Mobile Assistant App to autofill access settings and connect to your Magento store.',
                    'disabled' => $isElementDisabled
                )
            );

            $fieldset->addField(
                'qrcode_link',
                'link',
                array(
                    'label'    => Mage::helper('mobassistantconnector')->__('QR-code link'),
                    'href'     => $url,
                    'value'    => 'Copy link to share QR-code',
                    'target'   => '_blank',
                    'note'     => 'QR-code can be got by this link only if status of user is active',
                    'disabled' => $isElementDisabled
                )
            );

//            $fieldset->addField('qrcode_hash', 'hidden', array('name' => 'qrcode_hash', 'value' => $hash));
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
        return Mage::helper('mobassistantconnector')->__('User Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('mobassistantconnector')->__('User Information');
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
