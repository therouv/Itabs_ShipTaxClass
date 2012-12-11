<?php

/**
 * Shipping Tax Class Config
 *
 * @category   Mage
 * @package    Itabs_ShipTaxClass
 * @author     ITABS GmbH - Rouven Alexander Rieker <rouven.rieker@itabs.de>
 */
class Itabs_ShipTaxClass_Model_Config extends Mage_Tax_Model_Config
{    
    
    /**
     * Get tax class id specified for shipping tax estimation
     * 
     * CUSTOM: Select shipping tax class based on highest product tax rate
     *
     * @param   store $store
     * @return  int
     */
    public function getShippingTaxClass($store=null)
    {
        $_session = Mage::getSingleton('checkout/session');                                // Get current checkout session
        $_quoteItems = $_session->getQuote()->getAllItems();                            // Get all products in cart
        $_taxClassIds = array();                                                        // Save all tax class ids found in cart in this array
        $_highestTaxRate = null;                                                        // highest Tax value found in all available tax classes
        
        if(count($_quoteItems) > 0) {
            
            foreach($_quoteItems as $_item) {
                if($_item->getParentItem())
                    continue;
                
                $_taxPercent = $_item->getTaxPercent();                                    // Get tax percent of product
                if(is_float($_taxPercent) && !in_array($_taxPercent, $_taxClassIds)) {
                    $_taxClassIds[$_taxPercent] = $_item->getTaxClassId();
                }
            }
            
            ksort($_taxClassIds);
            if(count($_taxClassIds)) {
                $_highestTaxRate = array_pop($_taxClassIds);                            // Find highest tax rate
            }
            
            if(!$_highestTaxRate) {                                                        // If _highestTaxRate is null, return 0 for no shipping taxes
                $taxClassId = 0;
            } else {
                $taxClassId = $_highestTaxRate;
            }
        
        } else {
            $taxClassId = Mage::getStoreConfig(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
        }

        return (int)$taxClassId;
    }
}
