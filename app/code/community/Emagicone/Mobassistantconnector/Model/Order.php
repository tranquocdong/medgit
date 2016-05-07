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

class Emagicone_Mobassistantconnector_Model_Order extends Mage_Sales_Model_Order
{

    public function setState($state, $status = false, $comment = '', $isCustomerNotified = null)
    {
        Mage::dispatchEvent('sales_order_status_change', array('order' => $this, 'state' => $state, 'status' => $status, 'comment' => $comment, 'isCustomerNotified' => $isCustomerNotified));

        return parent::setState($state, $status, $comment, $isCustomerNotified);
    }
}