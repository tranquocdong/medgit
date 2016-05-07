<?php
class Prashant_RemoveCustomerAccountLinks_Block_Navigation extends Mage_Customer_Block_Account_Navigation
{
    /**
     * @return $this
     */
    public function removeLink()
    {
        foreach (Mage::helper('prashant_removecustomeraccountlinks')->getNavigationLinksToRemove() as $link) {
            unset($this->_links[$link]);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    protected function _toHtml()
    {
        $this->removeLink();
        return parent::_toHtml();
    }
}