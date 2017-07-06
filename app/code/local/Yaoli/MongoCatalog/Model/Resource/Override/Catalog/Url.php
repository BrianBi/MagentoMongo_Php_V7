<?php
/**
 * @category  Yaoli
 * @package   Yaoli_MongoCatalog
 */
class Yaoli_MongoCatalog_Model_Resource_Override_Catalog_Url extends Mage_Catalog_Model_Resource_Url
{

    /**
     * Save product attribute
     *
     * @param Varien_Object $product       Product we want to save the attribute for
     * @param string        $attributeCode Attribute code to be saved
     *
     * @return Mage_Catalog_Model_Resource_Url
     */
    public function saveProductAttribute(Varien_Object $product, $attributeCode)
    {
        parent::saveProductAttribute($product, $attributeCode);

        $adapter = Mage::getSingleton('mongocore/resource_connection_adapter');
        $updateCond = $adapter->getQueryBuilder()->getIdsFilter($product->getId());
        $updateField = sprintf('attr_%d_%s', $product->getStoreId(), $attributeCode);
        $attributeValue = $product->getData($attributeCode);

        Mage::getResourceModel('catalog/product')->updateProductFieldFromFilter($updateCond, $updateField, $attributeValue);

        return $this;
    }


}