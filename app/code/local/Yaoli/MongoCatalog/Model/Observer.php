<?php
/**
 * Events reletated code for MongoCatalog module
 *
 * @category  Yaoli
 * @package   Yaoli_MongoCatalog
 */
class Yaoli_MongoCatalog_Model_Observer extends Mage_Catalog_Model_Resource_Product
{
    /**
     * @param Varien_Event_Observer $observer Observer
     * @return Yaoli_MongoCatalog_Model_Observer
     */
    public function saveUpdatedCategories(Varien_Event_Observer $observer)
    {
        $updatedProductIds = $observer->getEvent()->getData('product_ids');
        $categoryId = $observer->getEvent()->getData('category')->getId();
        Mage::getResourceModel('catalog/product')->saveChangedCategory($categoryId, $updatedProductIds);

        return $this;
    }
}