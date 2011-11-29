<?php

namespace Respect\Structural;

use Respect\Data\AbstractMapper;
use Respect\Data\Collection;
use Respect\Data\CollectionIterator;

class Mapper extends AbstractMapper
{
    protected $db;
    
    protected static function prefixArrayKeys(array $array, $prefix)
    {
        $new = array();
        
        foreach ($array as $key => $value)
            $new["{$prefix}{$key}"] = $value;
            
        return $new;
    }
    
    public function __construct(MongoDb $db=null) //remove null from param list TODO
    {
        $this->db = $db;
    }
    
    public function persist($object, Collection $onCollection)
    {
        
    }

    public function remove($object, Collection $fromCollection)
    {
        
    }

    public function fetch(Collection $fromCollection, $withExtra=null)
    {
        
    }

    public function fetchAll(Collection $fromCollection, $withExtra=null)
    {
        
    }
    
    public function generateQuery(Collection $collection)
    {
        return array(
            $collection->getName() => $this->parseConditions($collection)
        );
    }
    
    protected function parseConditions(Collection $collection)
    {
        $allCollections = CollectionIterator::recursive($collection);
        $allCollections = iterator_to_array($allCollections);
        $allCollections = array_slice($allCollections, 1);
        
        $condition = $this->getConditionArray($collection);
        
        foreach ($allCollections as $name => $coll) 
            $condition += $this->getConditionArray($coll, true);
        
        return $condition;
    }
    
    protected function getConditionArray(Collection $collection, $prefix=false)
    {
        $condition = $collection->getCondition();
        
        if (!is_array($condition)) 
            $condition = array('_id' => $condition);
        
        if ($prefix)
            $condition = static::prefixArrayKeys(
                $condition, $collection->getName()."."
            );
        
        return $condition;
    }
}