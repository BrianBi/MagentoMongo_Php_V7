<?php

class Yaoli_MongoSearch_Model_Resource_Override_CatalogSearch_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{


    /**
     * Load product(s) attributes
     *
     * @param int   $storeId        Store id we want the attribute for
     * @param array $productIds     Product ids we want the attribute for
     * @param array $attributeTypes Attribute ids to get the value for by table
     *
     * @return array
     */
    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        return Mage::getResourceSingleton('mongosearch/product')->getProductAttributes($storeId, $productIds, $attributeTypes);
    }


}