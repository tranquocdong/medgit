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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_OrdStatuses extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    public function render(Varien_Object $row)
    {
        $pushOrderStatuses = (string)$row->getData($this->getColumn()->getIndex());

        if (empty($pushOrderStatuses)) {
            return '-';
        } else if ($pushOrderStatuses == '-1') {
            return 'All';
        }

        $finalOrderStatuses = array();
        $all = true;
        $pushOrderStatuses = explode('|', $pushOrderStatuses);
        $orderStatuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $countOrderStatuses = count($orderStatuses);

        for ($i = 0; $i < $countOrderStatuses; $i++) {
            if (in_array($orderStatuses[$i]['status'], $pushOrderStatuses)) {
                $finalOrderStatuses[] = $orderStatuses[$i]['label'];
            } else {
                $all = false;
            }
        }

        if ($all) {
            return 'All';
        } elseif (empty($finalOrderStatuses)) {
            return '-';
        }

        return implode(', ', $finalOrderStatuses);
    }

}
