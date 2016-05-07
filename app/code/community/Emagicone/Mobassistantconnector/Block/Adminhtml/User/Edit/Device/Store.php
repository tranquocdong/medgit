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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store
{

    public function render(Varien_Object $row)
    {
        $storeGroup = (int)$row->getData($this->getColumn()->getIndex());

        if ($storeGroup == -1) {
            return Mage::helper('mobassistantconnector')->__('All Stores');
        } else if ($storeGroup < 1) {
            return '-';
        }

        $out = '';
        $data = $this->_getStoreModel()->getStoresStructure(false, [], [$storeGroup]);

        foreach ($data as $website) {
            $out .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $out .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
            }
        }

        return $out;
    }

}
