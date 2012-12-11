<?php

class Itabs_ShipTaxClass_Model_Observer {
	
	protected function _loadTaxCalculationRate(Mage_Sales_Model_Quote_Item $item) {
		/*
		 * If no tax class is set
		 * the rate should be zero
		 */
		if(!$item->getTaxClassId()) {
			return 0.0;
		}
		
		$_taxCalculationRateId = Mage::getModel('tax/calculation')
			->getCollection()
			->getItemById($item->getTaxClassId())
			->getTaxCalculationRateId();
		$_taxPercent = Mage::getModel('tax/calculation_rate')
			->getCollection()
			->getItemById($_taxCalculationRateId)
			->getRate();
		if(is_string($_taxPercent)) {
			return $_taxPercent;
		}
		
		return NULL;
	}
	
	public function salesQuoteCollectTotalsBefore($observer) {
		$store = Mage::app()->getStore();
		
		$_quoteItems = $observer->getEvent()->getQuote()->getAllItems();
		$countQuoteItems = count($_quoteItems);
		
		if($countQuoteItems > 0) {
    	
	    	$_taxClassIds = array();														// Save all tax class ids found in cart in this array
			$_highestTaxRate = null;														// highest Tax value found in all available tax classes
	    	
	    	if(count($_quoteItems) > 0) {
	    		foreach($_quoteItems as $_item) {
                    if($_item->getParentItem() && $_item->getParentItem()->getProduct()->getTypeId() != 'bundle')
	    				continue;
	    			$_taxPercent = $_item->getTaxPercent();									// Get tax percent of product
	    			if(!$_taxPercent || $_taxPercent == '') {
	    				$_taxPercent = $this->_loadTaxCalculationRate($_item);
	    			}
	    			if(!in_array($_taxPercent, $_taxClassIds)) {
	    				$_taxClassIds[$_taxPercent] = $_item->getTaxClassId();
	    			}
	    		}
	    		ksort($_taxClassIds);
	    		if(count($_taxClassIds)) {
	    			$_arrayKeysTaxClassIds = array_keys($_taxClassIds);
	    			$_highestTaxRate = array_pop($_arrayKeysTaxClassIds);
	    			$_highestTaxRateClass = array_pop($_taxClassIds);						// Find highest tax rate
	    		}
	    	
		    	if(!$_highestTaxRateClass) {												// If _highestTaxRate is null, return 0 for no shipping taxes
		    		$taxClassId = 0;
		    	} else {
		    		$taxClassId = $_highestTaxRateClass;
		    	}
	    	
	    	} else {
	    		$taxClassId = Mage::getStoreConfig(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
	    	}

			Mage::getSingleton('checkout/session')->setData('shiptaxclass_highestaxrate', $_highestTaxRate);
			Mage::getSingleton('checkout/session')->setData('shiptaxclass_highestaxrateclass', $taxClassId);
		}
	}
}