<?php
class Yaoli_MongoSearch_Model_Resource_Override_EnterpriseCatalogSearch_Index_Action_Fulltext_Refresh
    extends Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh
{
    /**
     * @param int   $storeId        
     * @param array $productIds     
     * @param array $attributeTypes 
     *
     * @return array
     */
    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        return Mage::getResourceSingleton('mongosearch/product')->getProductAttributes($storeId, $productIds, $attributeTypes);
    }
}