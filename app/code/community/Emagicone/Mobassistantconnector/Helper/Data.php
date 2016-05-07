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

class Emagicone_Mobassistantconnector_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function price_format($iso_code, $curr_format, $price, $convert_to, $force = false, $format = true) {
        $currency_symbol = '';
        $price = str_replace(' ', '', $price);
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();

        if(strlen($convert_to) == 3){
            try {
                // $price2 = Mage::helper('directory')->currencyConvert($price, $baseCurrencyCode, $convert_to);
//                $price = $this->currencyConvert($price, $baseCurrencyCode, $convert_to);

                $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
                if (!empty($rates[$convert_to])) {
                    $price = $price * $rates[$convert_to];
                }

                $iso_code = $convert_to;
            } catch (Exception $e) {
                Mage::log(
                    "Error while currency converting (". var_export($e->getMessage(), true). ")",
                    null,
                    'emagicone_mobassistantconnector.log'
                );
            }
        }

        if ($format) {
            $price = number_format(floatval($price), 2, '.', ' ');
        }

        preg_match('/^[a-zA-Z]+$/', $iso_code, $matches);

        if (count($matches) > 0) {
            if (strlen($matches[0]) == 3) {
                $currency_symbol = Mage::app()->getLocale()->currency($iso_code)->getSymbol();
            }
        } else {
            $currency_symbol = $iso_code;
        }

        if ($force) {
            return $currency_symbol;
        }
//        $sign = '<span>' . $currency_symbol . '</span>';
        $sign = $currency_symbol;
        if ($curr_format == 1) {
            $price = $sign . $price;
        } elseif ($curr_format == 2) {
            $price = $price;
        } else {
            $price = $price . ' ' . $sign;
        }

        return $price;
    }

    public static function getDataToQrCode($base_url, $username, $password)
    {
        $base_url = str_replace('http://', '', $base_url);
        $base_url = str_replace('https://', '', $base_url);
        preg_replace('/\/*$/i', '', $base_url);

        $data = array(
            'url' => $base_url,
            'login' => $username,
            'password' => $password
        );

        return base64_encode($data = Mage::helper('core')->jsonEncode($data));
    }

}