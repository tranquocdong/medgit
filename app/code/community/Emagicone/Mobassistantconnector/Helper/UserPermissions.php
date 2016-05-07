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

class Emagicone_Mobassistantconnector_Helper_UserPermissions extends Mage_Core_Helper_Abstract
{

    /**
     * Data are visible in back-end
     * @return array
     */
    public static function getRestrictedActions()
    {
        $restricted_actions = array(
            array(
                'group_name' => 'Push notification settings',
                'child'      => array(
                    array(
                        'code' => 'push_notification_settings_new_order',
                        'name' => 'New order',
                    ),
                    array(
                        'code' => 'push_notification_settings_new_customer',
                        'name' => 'New customer',
                    ),
                    array(
                        'code' => 'push_notification_settings_order_statuses',
                        'name' => 'Order statuses',
                    ),
                ),
            ),
            array(
                'group_name' => 'Store statistics',
                'child'      => array(
                    array(
                        'code' => 'store_stats',
                        'name' => 'Store statistics',
                    ),
                ),
            ),
            array(
                'group_name' => 'Products',
                'child'      => array(
                    array(
                        'code' => 'products_list',
                        'name' => 'Product list',
                    ),
                    array(
                        'code' => 'product_details',
                        'name' => 'Product details',
                    ),
                ),
            ),
            array(
                'group_name' => 'Customers',
                'child'      => array(
                    array(
                        'code' => 'customers_list',
                        'name' => 'Customer list',
                    ),
                    array(
                        'code' => 'customer_details',
                        'name' => 'Customer details',
                    ),
                ),
            ),
            array(
                'group_name' => 'Orders',
                'child'      => array(
                    array(
                        'code' => 'orders_list',
                        'name' => 'Order list',
                    ),
                    array(
                        'code' => 'order_details',
                        'name' => 'Order details',
                    ),
                    array(
                        'code' => 'order_details_pdf',
                        'name' => 'Order details PDF',
                    ),
                    array(
                        'code' => 'order_invoice',
                        'name' => 'Invoice order',
                    ),
                    array(
                        'code' => 'order_ship',
                        'name' => 'Ship order',
                    ),
                    array(
                        'code' => 'order_cancel',
                        'name' => 'Cancel order',
                    ),
                    array(
                        'code' => 'order_hold',
                        'name' => 'Hold order',
                    ),
                    array(
                        'code' => 'order_unhold',
                        'name' => 'Unhold order',
                    ),
                    array(
                        'code' => 'order_delete_track_number',
                        'name' => 'Delete track number',
                    ),
                ),
            ),
            array(
                'group_name' => 'Abandoned carts',
                'child'      => array(
                    array(
                        'code' => 'abandoned_carts_list',
                        'name' => 'Abandoned cart list',
                    ),
                    array(
                        'code' => 'abandoned_cart_details',
                        'name' => 'Abandoned cart details',
                    ),
                ),
            ),
        );

        return $restricted_actions;
    }

    public static function getRestrictedActionsToFunctions()
    {
        $restricted_actions_to_functions = array(
            'push_notification_settings_new_order' => array(
                'push_notification_settings',
                'delete_push_config',
            ),
            'push_notification_settings_new_customer' => array(
                'push_notification_settings',
                'delete_push_config',
            ),
            'push_notification_settings_order_statuses' => array(
                'push_notification_settings',
                'delete_push_config',
            ),
            'store_stats' => array(
                'get_store_stats',
                'get_data_graphs',
                'get_status_stats',
            ),
            'products_list' => array(
                'search_products',
                'search_products_ordered',
            ),
            'product_details' => array(
                'get_products_info',
                'get_products_descr',
            ),
            'customers_list' => array(
                'get_customers',
            ),
            'customer_details' => array(
                'get_customers_info',
            ),
            'orders_list' => array(
                'get_orders',
            ),
            'order_details' => array(
                'get_orders_info',
            ),
            'order_details_pdf' => array(
                'get_order_pdf',
            ),
            'order_invoice' => array(
                'set_order_action',
                'invoice_order',
            ),
            'order_ship' => array(
                'set_order_action',
                'ship_order',
            ),
            'order_cancel' => array(
                'set_order_action',
                'cancel_order',
            ),
            'order_hold' => array(
                'set_order_action',
                'hold_order',
            ),
            'order_unhold' => array(
                'set_order_action',
                'unhold_order',
            ),
            'order_delete_track_number' => array(
                'set_order_action',
                'delete_track_number',
            ),
            'abandoned_carts_list' => array(
                'get_abandoned_carts_list',
            ),
            'abandoned_cart_details' => array(
                'get_abandoned_cart_details',
            ),
        );

        return $restricted_actions_to_functions;
    }

    public static function getAlwaysAllowedFunctions()
    {
        $allowed_functions_always = array(
            'run_self_test',
            'get_stores',
            'get_currencies',
            'get_store_title',
            'get_orders_statuses',
            'get_carriers',
        );

        return $allowed_functions_always;
    }

    /**
     * Return codes of all actions
     * @return array
     */
    public static function getActionsCodes()
    {
        $actions      = self::getRestrictedActions();
        $result       = array();
        $groups_count = count($actions);

        for ($i = 0; $i < $groups_count; $i++) {
            $child_count = count($actions[$i]['child']);

            for ($j = 0; $j < $child_count; $j++) {
                $result[] = $actions[$i]['child'][$j]['code'];
            }
        }

        return $result;
    }

    public static function getUserAllowedActionsAsString($action_codes)
    {
        $is_allowed_all     = true;
        $result             = '';
        $restricted_actions = self::getRestrictedActions();
        $groups_count       = count($restricted_actions);

        for ($i = 0; $i < $groups_count; $i++) {
            $child_count = count($restricted_actions[$i]['child']);
            $tmp = '';

            for ($j = 0; $j < $child_count; $j++) {
                if (in_array($restricted_actions[$i]['child'][$j]['code'], $action_codes)) {
                    if (!empty($tmp)) {
                        $tmp .= ', ';
                    }

                    $tmp .= $restricted_actions[$i]['child'][$j]['name'];
                } else {
                    $is_allowed_all = false;
                }
            }

            if (!empty($tmp)) {
                if (!empty($result)) {
                    $result .= ', ';
                }

                $result .= $restricted_actions[$i]['group_name'].' ('.$tmp.')';
            }
        }

        if ($is_allowed_all) {
            $result = 'All';
        } elseif (empty($result)) {
            $result = 'Nothing';
        }

        return $result;
    }

}