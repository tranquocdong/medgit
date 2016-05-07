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

var $j = jQuery.noConflict();

$j(document).ready(function() {
    var container = $j("#emagicone_mobassistantconnector_user_grid");
    container.append("<div style='text-align: center'><div style='display: inline-flex; margin-top: 15px'>" +
        "<div id='mobassistantconnector_qr_code_app_img'></div>" +
        "<div style='display: flex; align-items: center; padding-left: 7px'>" +
        "Magento Mobile Assistant App</div></div></div>");
    var qrcodeContainer = document.getElementById("mobassistantconnector_qr_code_app_img");

    if (qrcodeContainer != null) {
        var qrCode = new QRCode(qrcodeContainer, {
            width : 66,
            height : 66
        });

        qrCode.makeCode("https://goo.gl/6Wjxme");
    }
});