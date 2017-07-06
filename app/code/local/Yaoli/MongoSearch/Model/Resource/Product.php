<?php

class Yaoli_MongoSearch_Model_Resource_Product extends Yaoli_MongoCatalog_Model_Resource_Override_Catalog_Product
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
    public function getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        $result = array();
        $adapter = $this->getAdapter();
        $collection = $this->_getDocumentCollection();
        $findCond = array();

        if ($productIds) {
            $findCond = $this->getIdsFilter($productIds);
        }

        $it = $collection->find($findCond);

        foreach ($it as $currentDoc)
        {
            foreach (array('attr_0', 'attr_' . $storeId) as $currentStoreField) {
                if (isset($currentDoc[$currentStoreField])) {
                    $currentDoc = array_merge($currentDoc, $currentDoc[$currentStoreField]);
                    unset($currentDoc[$currentStoreField]);

                }
            }

            $result[$currentDoc['_id']] = $this->_mapAttributeValues($currentDoc);
        }

        /*while ($it->hasNext()) {
            $currentDoc = $it->getNext();

            foreach (array('attr_0', 'attr_' . $storeId) as $currentStoreField) {
                if (isset($currentDoc[$currentStoreField])) {
                    $currentDoc = array_merge($currentDoc, $currentDoc[$currentStoreField]);
                    unset($currentDoc[$currentStoreField]);

                }
            }

            $result[$currentDoc['_id']] = $this->_mapAttributeValues($currentDoc);
        }*/

        return $result;
    }


    /**
     * Map index value from the document loaded from MongoDB.
     *
     * @param array &$document Product attributes loaded from MongoDB (attributeCode => value)
     *
     * @return array
     */
    protected function _mapAttributeValues(&$document)
    {
        $result = array();
        foreach ($document as $attributeCode => $attributeValue) {
            $attribute = $this->getAttribute($attributeCode);
            if ($attribute && $attribute->getId()) {
                $result[$attribute->getId()] = $document[$attribute->getAttributeCode()];
            }
        }

        return $result;
    }


}