<?php

/**
 * @category  Yaoli
 * @package   Yaoli_MongoCatalog
 */
class Yaoli_MongoCatalog_Model_Resource_Override_Catalog_Product_Action extends Mage_Catalog_Model_Resource_Product_Action
{

    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds Ids of the products to be updated
     * @param array $attrData  Attribute to be updated and their values
     * @param int   $storeId   Store the product have to be updated
     *
     * @throws Exception If something goes wrong an Exception is raised
     *
     * @return Mage_Catalog_Model_Resource_Product_Action
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new Varien_Object();
        $object->setIdFieldName('entity_id')
            ->setStoreId($storeId);

        $sqlAttributeCodes = Mage::getResourceSingleton('catalog/product')->getSqlAttributesCodes();

        $this->_getWriteAdapter()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {

                if (in_array($attrCode, $sqlAttributeCodes)) {
                    $attribute = $this->getAttribute($attrCode);
                    if (!$attribute->getAttributeId()) {
                        continue;
                    }

                    $i = 0;
                    foreach ($entityIds as $entityId) {
                        $i++;
                        $object->setId($entityId);
                        // collect data for save
                        $this->_saveAttributeValue($object, $attribute, $value);
                        // save collected data every 1000 rows
                        if ($i % 1000 == 0) {
                            $this->_processAttributeValues();
                        }
                    }
                    $this->_processAttributeValues();
                }
            }

            Mage::getResourceSingleton('catalog/product')->massDataUpdate($entityIds, $attrData, $storeId);

            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        return $this;
    }
}
