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

class Emagicone_Mobassistantconnector_Block_Adminhtml_User_Edit_Device_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{

    public function render(Varien_Object $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());

        if (!$data || $data == '0000-00-00 00:00:00') {
            return '-';
        }

        return parent::render($row);
    }

}
