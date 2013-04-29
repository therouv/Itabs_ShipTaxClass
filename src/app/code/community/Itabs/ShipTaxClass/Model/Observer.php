<?php
/**
 * This file is part of the Itabs_ShipTaxClass project.
 *
 * Itabs_ShipTaxClass is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category  Itabs
 * @package   Itabs_ShipTaxClass
 * @author    Rouven Alexander Rieker <rouven.rieker@itabs.de>
 * @author    Steffen Meuser <magento@flagbit.de>
 * @copyright 2012 ITABS GmbH + Flagbit GmbH & Co. KG. All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   0.1.0
 * @since     0.1.0
 */
/**
 * Observer
 *
 * @category  Itabs
 * @package   Itabs_ShipTaxClass
 * @author    Rouven Alexander Rieker <rouven.rieker@itabs.de>
 * @author    Steffen Meuser <magento@flagbit.de>
 * @copyright 2012 ITABS GmbH + Flagbit GmbH & Co. KG. All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   0.1.0
 * @since     0.1.0
 */
class Itabs_ShipTaxClass_Model_Observer
{
    /**
     * Retrieve the correct tax calculation rate
     *
     * @param  Mage_Sales_Model_Quote_Item $item
     * @return decimal|null Tax Calculation Rate
     */
    protected function _loadTaxCalculationRate(Mage_Sales_Model_Quote_Item $item)
    {
        /*
         * If no tax class is set
         * the rate should be zero
         */
        if (!$item->getTaxClassId()) {
            return 0.0;
        }

        $_taxCalculationRateId = Mage::getModel('tax/calculation')
            ->getCollection()
            ->addFieldToFilter('product_tax_class_id', $item->getTaxClassId())
            ->getFirstItem()
            ->getTaxCalculationRateId();
        $_taxPercent = Mage::getModel('tax/calculation_rate')
            ->getCollection()
            ->getItemById($_taxCalculationRateId)
            ->getRate();

        if (is_string($_taxPercent)) {
            return $_taxPercent;
        }

        return NULL;
    }

    /**
     * Find the correct shipping tax calculation rate and class and set the
     * values on the checkout session
     *
     * @event  sales_quote_collect_totals_before
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function salesQuoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $store = Mage::app()->getStore();

        $_quoteItems = $observer->getEvent()->getQuote()->getAllItems();
        $countQuoteItems = count($_quoteItems);

        if ($countQuoteItems > 0) {

            $_taxClassIds = array();														// Save all tax class ids found in cart in this array
            $_highestTaxRate = null;														// highest Tax value found in all available tax classes

            if (count($_quoteItems) > 0) {
                foreach ($_quoteItems as $_item) {
                    if ($_item->getParentItem() && $_item->getParentItem()->getProduct()->getTypeId() != 'bundle') {
                        continue;
                    }
                    $_taxPercent = $_item->getTaxPercent();									// Get tax percent of product
                    if (!$_taxPercent || $_taxPercent == '') {
                        $_taxPercent = $this->_loadTaxCalculationRate($_item);
                    }
                    if (!in_array($_taxPercent, $_taxClassIds)) {
                        $_taxClassIds[$_taxPercent] = $_item->getTaxClassId();
                    }
                }
                ksort($_taxClassIds);
                if (count($_taxClassIds)) {
                    $_arrayKeysTaxClassIds = array_keys($_taxClassIds);
                    $_highestTaxRate = array_pop($_arrayKeysTaxClassIds);
                    $_highestTaxRateClass = array_pop($_taxClassIds);						// Find highest tax rate
                }

                if (!$_highestTaxRateClass) {												// If _highestTaxRate is null, return 0 for no shipping taxes
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
