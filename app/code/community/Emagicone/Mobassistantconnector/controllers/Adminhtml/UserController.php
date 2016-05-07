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

class Emagicone_Mobassistantconnector_Adminhtml_UserController extends Mage_Adminhtml_Controller_Action
{

    private function changeUserStatus($status)
    {
        $userIds = $this->getRequest()->getPost('user_ids', array());
        $count = count($userIds);

        for ($i = 0; $i < $count; $i++) {
            Mage::getModel('emagicone_mobassistantconnector/user')->load($userIds[$i])
                ->setData('status', $status)
                ->save();
        }

        $text = $status == 1
            ? Mage::helper('mobassistantconnector')->__('%s user(s) have been enabled.', count($userIds))
            : Mage::helper('mobassistantconnector')->__('%s user(s) have been disabled.', count($userIds));

        $this->_getSession()->addSuccess($text);
        $this->_redirect('*/*/');
    }

    private function deleteUser($userIds)
    {
        if (!$userIds || empty($userIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mobassistantconnector')->__('User ID is incorrect')
            );
            $this->_redirect('*/*/');
            return;
        }

        $model = Mage::getModel('emagicone_mobassistantconnector/user');
        $count = count($userIds);

        try {
            for ($i = 0; $i < $count; $i++) {
                $model->load($userIds[$i])->delete();

                // Delete session keys of user
                $sessionKeyCollection = Mage::getModel('emagicone_mobassistantconnector/sessions')->getCollection()
                    ->addFieldToFilter('user_id', $userIds[$i]);
                foreach ($sessionKeyCollection as $key) {
                    $key->delete();
                }

                // Delete push data of user
                $pushCollection = Mage::getModel('emagicone_mobassistantconnector/push')->getCollection()
                    ->addFieldToFilter('user_id', $userIds[$i]);
                foreach ($pushCollection as $push) {
                    $push->delete();
                }

                Mage::helper('mobassistantconnector/deviceAndPushNotification')->deleteEmptyDevices();
                Mage::helper('mobassistantconnector/deviceAndPushNotification')->deleteEmptyAccounts();
            }

            $this->_getSession()->addSuccess($this->__('A total of %s user(s) have been deleted.', $count));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    private function changeStatusAccount($pushIds)
    {
        if (empty($pushIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mobassistantconnector')->__('Nothing selected')
            );
        } else {
            $i = 0;
            $value = (int)$this->getRequest()->getParam('value');
            $accountCollection = Mage::getModel('emagicone_mobassistantconnector/account')->getCollection();
            $accountCollection->getSelect()
                ->joinLeft(
                    array('d' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/device')),
                    'd.account_id = main_table.id',
                    array()
                )
                ->joinLeft(
                    array('p' => Mage::getSingleton('core/resource')->getTableName('emagicone_mobassistantconnector/push')),
                    'd.device_unique_id = p.device_unique_id',
                    array()
                )
                ->where('p.id IN (' . implode(',', $pushIds) . ')')
                ->group('main_table.id');

            foreach ($accountCollection as $account) {
                $account->setData('status', $value)->save();
                $i++;
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('mobassistantconnector')->__('A total of %s account(s) have been updated.', $i)
            );
        }

        $this->_redirect(
            '*/*/edit',
            array('user_id' => $this->getRequest()->getParam('user_id'), 'active_tab' => 'devices_section')
        );
    }

    private function deleteDevice($pushIds)
    {
        if (empty($pushIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mobassistantconnector')->__('Nothing selected')
            );
        } else {
            $pushCollection = Mage::getModel('emagicone_mobassistantconnector/push')->getCollection();
            $pushCollection->getSelect()->where('main_table.id IN (' . implode(',', $pushIds) . ')');

            try {
                foreach ($pushCollection as $push) {
                    $push->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mobassistantconnector')->__('A total of %s record(s) have been deleted.', count($pushIds))
                );

                Mage::helper('mobassistantconnector/deviceAndPushNotification')->deleteEmptyDevices();
                Mage::helper('mobassistantconnector/deviceAndPushNotification')->deleteEmptyAccounts();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError('Could not delete records. ' . $e->getMessage());
            }
        }

        $this->_redirect(
            '*/*/edit',
            array('user_id' => $this->getRequest()->getParam('user_id'), 'active_tab' => 'devices_section')
        );
    }

    /**
     * Users grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Users'))->_title($this->__('Manage Users'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('mobassistantconnector/mobassistantconnector_users');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('mobassistantconnector/adminhtml_user')
        );

        $this->getLayout()->getBlock('head')
            ->addJs('emagicone/mobassistantconnector/jquery-2.2.2.min.js')
            ->addJs('emagicone/mobassistantconnector/qrcode.min.js')
            ->addJs('emagicone/mobassistantconnector/qrcode_app_user_index.js');

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb($this->__('Users'), $this->__('Users'));
        $this->_addBreadcrumb($this->__('Manage Users'), $this->__('Manage Users'));

        // Check if default user exists
        $collection = Mage::getModel('emagicone_mobassistantconnector/user')->getCollection()
            ->addFieldToFilter('username', '1')
            ->addFieldToFilter('password', md5('1'));
        if ($collection->getSize() > 0) {
            Mage::getSingleton('adminhtml/session')->addWarning(
                Mage::helper('mobassistantconnector')
                    ->__('Some user has default login and password are "1". Change them because of security reasons, please!')
            );
        }

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit user
     */
    public function editAction()
    {
        $this->_title($this->__('Mobile Assistant Connector'))
            ->_title($this->__('Manage Users'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('emagicone_mobassistantconnector/user');

        // 2. Initial checking
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('mobassistantconnector')->__('This user no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getUsername() : $this->__('New User'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('mobassistantconnector_user', $model);

        $this->loadLayout();
        $this->_setActiveMenu('mobassistantconnector/mobassistantconnector_users');
        $this->renderLayout();
    }

    /**
     * Save user action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();

        if (!$data) {
            $this->_redirect('*/*/');
        }

        $model = Mage::getModel('emagicone_mobassistantconnector/user');

        // Check if another user exists with the same username
        $model->load($data['username'], 'username');
        if (
            $model->getData('username') == $data['username'] &&
            (!isset($data['user_id']) || $data['user_id'] != $model->getId())
        ) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mobassistantconnector')
                    ->__("Another user exists with the same username '{$data['username']}'")
            );

            if (!isset($data['user_id'])) {
                $this->_redirect('*/*/new', array('_current' => true));
            } else {
                $this->_redirect('*/*/edit', array('user_id' => $data['user_id'], '_current' => true));
            }

            return;
        }

        if ($id = $this->getRequest()->getParam('user_id')) {
            $model->load($id);
        }

        if ($model->getData('username') != $data['username'] || $model->getData('password') != $data['password']) {
            $model->setData('qr_code_hash', hash('sha256', time()));

            if ($model->getData('password') != $data['password']) {
                $model->setData('password', md5($data['password']));
            }
        }

        $model->setData('status', $data['status']);
        $model->setData('username', $data['username']);
        $model->setData(
            'allowed_actions',
            empty($data['allowed_actions']) ? '' : implode(';', $data['allowed_actions'])
        );

        try {
            $model->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('mobassistantconnector')->__('The user has been saved.')
            );

            // clear previously saved data from session
            Mage::getSingleton('adminhtml/session')->setFormData(false);

            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('user_id' => $model->getId(), '_current' => true));
                return;
            }

            // Go to grid
            $this->_redirect('*/*/');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('mobassistantconnector')->__('An error occurred while saving the user.')
            );
        }

        $this->_getSession()->setFormData($data);
        $this->_redirect('*/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
    }

    /**
     * Delete user action
     */
    public function deleteAction()
    {
        $this->deleteUser(array($this->getRequest()->getParam('user_id')));
    }

    /**
     * Delete selected users action
     */
    public function massDeleteAction()
    {
        $this->deleteUser($this->getRequest()->getPost('user_ids', array()));
    }

    public function devicesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Enable selected users
     */
    public function massEnableAction()
    {
        $this->changeUserStatus(1);
    }

    /**
     * Disable selected users
     */
    public function massDisableAction()
    {
        $this->changeUserStatus(0);
    }

    /**
     * Mass change status of selected accounts
     */
    public function massChangeStatusAccountAction()
    {
        $this->changeStatusAccount($this->getRequest()->getPost('push_ids', array()));
    }

    /**
     * Change status of selected account
     */
    public function changeStatusAccountAction()
    {
        $this->changeStatusAccount(array($this->getRequest()->getParam('push_id')));
    }

    /**
     * Mass delete selected push records
     */
    public function massDeleteDeviceAction()
    {
        $this->deleteDevice($this->getRequest()->getPost('push_ids', array()));
    }

    /**
     * Delete push record
     */
    public function deleteDeviceAction()
    {
        $this->deleteDevice(array($this->getRequest()->getParam('push_id', array())));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'new':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('user/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('user/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('user');
                break;
        }
    }

}
