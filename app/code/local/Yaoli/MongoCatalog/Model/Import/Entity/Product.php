<?php
/**
 * Catalog Product Import using MongoDB
 *
 * @category  Yaoli
 * @package   Yaoli_MongoCatalog
 */
class Yaoli_MongoCatalog_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
    /**
     * Save product attributes.
     *
     * @param array $attributesData Attribute to be saved into products
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _saveProductAttributes(array $attributesData)
    {
        $sqlAttributeCodes = Mage::getResourceModel('catalog/product')->getSqlAttributesCodes();

        $sqlAttributeIdByCode = array();
        $docData   = array();

        foreach ($attributesData as $tableName => $skuData) {
            $tableData = array();

            foreach ($skuData as $sku => $attributes) {
                $productId = $this->_newSku[$sku]['entity_id'];

                foreach ($attributes as $attributeId => $storeValues) {

                    if (!isset($sqlAttributeIdByCode[$attributeId])) {
                        $sqlAttributeIdByCode[$attributeId] = Mage::getModel('eav/entity_attribute')->load($attributeId);
                    }

                    $attributeCode = $sqlAttributeIdByCode[$attributeId]->getAttributeCode();

                    $sqlAttribute = false;

                    if (in_array($attributeCode, $sqlAttributeCodes)) {
                        $sqlAttribute = true;
                    }

                    foreach ($storeValues as $storeId => $storeValue) {

                        if ($sqlAttribute) {
                            $tableData[] = array(
                                'entity_id'      => $productId,
                                'entity_type_id' => $this->_entityTypeId,
                                'attribute_id'   => $attributeId,
                                'store_id'       => $storeId,
                                'value'          => $storeValue
                            );
                        }

                        $docData[$productId]['attr_' . $storeId . '.' . $attributeCode] = $storeValue;
                    }

                    if (!isset($docData[$productId])) {
                        $docData[$productId] = array();
                    }
                }

            }

            if (!empty($tableData)) {
                $this->_connection->insertOnDuplicate($tableName, $tableData, array('value'));
            }
        }

        foreach ($docData as $productId => $currentDocData) {
            Mage::getResourceModel('catalog/product')->updateRawDocument($productId, $currentDocData, '$set');
        }

        return $this;
    }

}
