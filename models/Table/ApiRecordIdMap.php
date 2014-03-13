<?php

class Table_ApiRecordIdMap extends Omeka_Db_Table
{
    public function localRecord($recordType, $externalId, $endpointUri)
    {
        $select = $this->getSelect();
        $alias = $this->getTableAlias();
        $recordTable = $this->getDb()->getTable($recordType);
        $recordTableAlias = $recordTable->getTableAlias();
        $select = $recordTable->getSelect();

        $select->join(
                    array('api_record_id_maps' => $this->getTableName()),
                    "api_record_id_maps.local_id = $recordTableAlias.id",
                    null
                );
        $select->where("$alias.record_type = ?", $recordType);
        $select->where("$alias.external_id = ?", $externalId);
        $select->where("$alias.endpoint_uri = ?", $endpointUri);
        return $recordTable->fetchObject($select);
    }
    
    public function getImportedEndpoints()
    {
        $sql = "
            SELECT DISTINCT `endpoint_uri`
            FROM `omeka_api_record_id_maps`
            WHERE `record_type` NOT
            IN (
            'Element', 'ElementSet', 'File', 'ItemType'
            )
        ";
        return $this->getDb()->fetchCol($sql);
        
    }
    
    public function getSelectForExternalIds()
    {
        $select = new Omeka_Db_Select($this->getDb()->getAdapter());
        $alias = $this->getTableAlias();
        $select->from(array($alias=>$this->getTableName()), "$alias.external_id");
        return $select;
    }    
    
    public function findExternalIdsByParams($params = array())
    {
        $select = $this->getSelectForExternalIds($params);
        $this->applySearchFilters($select, $params);
        $data = $this->getDb()->fetchAssoc($select);
        return $data;
    }
}