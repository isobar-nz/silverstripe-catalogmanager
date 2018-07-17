<?php

namespace LittleGiant\CatalogManager\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\SS_List;

/**
 * Class AutoPublishSortExtension
 * @package LittleGiant\CatalogManager\Extensions
 */
class AutoPublishSortExtension extends Extension
{
    /**
     * @see \Symbiote\GridFieldExtensions\GridFieldOrderableRows::reorderItems()
     * @param \SilverStripe\ORM\ArrayList|\SilverStripe\ORM\DataList $list
     * @param array $values [listItemID => currentSortValue];
     * @param array $sortedIDs [newSortValue => listItemID]
     */
    public function onAfterReorderItems(SS_List &$list, array $values, array $sortedIDs)
    {
        $modelClass = $list->dataClass();
        /** @var CatalogPageExtension|SiteTree $model */
        $model = singleton($modelClass);
        if (!$model::config()->get('automatic_live_sort')) {
            return;
        }

        $sortField = CatalogPageExtension::getClassSortFieldName($modelClass);
        $tableName = DataObject::getSchema()->tableForField($modelClass, $sortField);
        if ($tableName === null) {
            throw new \Exception("Sort field {$sortField} could not be found in table hierarchy for {$modelClass}.");
        }

        foreach ($sortedIDs as $sortValue => $itemID) {
            DB::prepared_query('UPDATE "' . $tableName . '_Live" SET "' . $sortField . '"=? WHERE "ID"=?', [$sortValue, $itemID]);
        }
    }
}
