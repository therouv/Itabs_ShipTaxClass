<?php

/**
 * Shipping Tax Class Config
 *
 * @category   Mage
 * @package    Itabs_ShipTaxClass
 * @author     ITABS GbR - Rouven Alexander Rieker <rouven.rieker@itabs.de>
 */
class Itabs_ShipTaxClass_Model_Config extends Mage_Tax_Model_Config
{	
    const CONFIG_XML_PATH_IGNORE_SHIPPINGTAX_CUSTOMERGROUP = 'tax/classes/ignore_shippingtax_customergroup';
    const CONFIG_XML_PATH_IGNORE_SHIPPINGTAX_CUSTOMERGROUP_TAXCLASSID = 'tax/classes/ignore_shippingtax_customergroup_taxclassid';

    /**
     * Get tax class id specified for shipping tax estimation
     * 
     * CUSTOM: Select shipping tax class based on highest product tax rate
     *
     * @param   store $store
     * @return  int
     */
    public function getShippingTaxClass($store=null) {
    	$ignoreShippingtaxCustomergroups = Mage::getStoreConfig(self::CONFIG_XML_PATH_IGNORE_SHIPPINGTAX_CUSTOMERGROUP);
    	$ignoreShippingtaxCustomergroupsTaxclassid = Mage::getStoreConfig(self::CONFIG_XML_PATH_IGNORE_SHIPPINGTAX_CUSTOMERGROUP_TAXCLASSID);
        $session = Mage::getSingleton('checkout/session');
    	if($ignoreShippingtaxCustomergroups && $ignoreShippingtaxCustomergroupsTaxclassid && $session->hasQuote()) {

		    $customerGroupId = $session->getQuote()->getCustomerGroupId();
		    //$customerGroupTaxId = Mage::getSingleton('customer/group')->load($customerGroupId)->getTaxClassId();

		    if(in_array($customerGroupId, explode(',', $ignoreShippingtaxCustomergroups))) {
		    	return $ignoreShippingtaxCustomergroupsTaxclassid;
		    }
    	}
		if (Mage::getSingleton('checkout/session')->getData('shiptaxclass_highestaxrateclass')) {
			$taxClassId = Mage::getSingleton('checkout/session')->getData('shiptaxclass_highestaxrateclass');
		} else {
			$taxClassId = Mage::getStoreConfig(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
		}
		
		return $taxClassId;
    }
    
    /**
     * Get tax rate specified for shipping tax estimation
     * 
     * CUSTOM: Select shipping tax rate based on highest product tax rate
     *
     * @param   store $store
     * @return  int
     */
    public function getShippingTaxRate($store=null) {
		if (Mage::getSingleton('checkout/session')->getData('shiptaxclass_highestaxrate')) {
			$taxRate = Mage::getSingleton('checkout/session')->getData('shiptaxclass_highestaxrate');
		} else {
			/*
			 * TODO
			 * reading a storeConfig TaxClass instead of a tax rate is not the expected behaviour XD
			 */
			$taxRate = Mage::getStoreConfig(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
		}
		
		return $taxRate;
    }
}