<?php
/**
 * @category  Yaoli
 * @package   Yaoli_MongoCore
 */
class Yaoli_MongoCore_Model_Resource_Connection_Query_Builder
{
    /**
     * Build a filter on an array of integer ids or on a single id
     *
     * @param int|array $ids Id the filter must target
     *
     * @return array
     */
    public function getIdsFilter($ids)
    {
        $result = array();

        if (is_array($ids)) {

            foreach ($ids as $position => $entityId) {
                //$ids[$position] = new MongoInt32($entityId);
                $ids[$position] = intval($entityId);
            }

            /**
             * Since version 2.6, MongoDB is attending real array in $in condition
             * Ensure sending real arrays when filtering, otherwise, associative or non-sequential arrays are considered
             * as BSON objects and cause exception.
             *
             * @see https://jira.mongodb.org/browse/PHP-1051
             */
            $ids = array_values($ids);

            $result = array('_id' => array('$in' => $ids));

        } else {
            //$result = array('_id' => new MongoInt32($ids));
            $result = array('_id' => intval($ids));
        }

        return $result;
    }
}
