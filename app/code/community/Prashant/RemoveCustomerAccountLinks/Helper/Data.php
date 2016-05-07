<?php
class Prashant_RemoveCustomerAccountLinks_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param mixed $store
     * @return array
     */
    public function getNavigationLinksToRemove($store = null)
    {
        $items = Mage::getStoreConfig('customer/prashant_removecustomeraccountlinks/items', $store);
        return explode(',', $items);
    }
}