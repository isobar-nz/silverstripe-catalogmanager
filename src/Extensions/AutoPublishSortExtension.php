<?php

namespace LittleGiant\CatalogManager\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
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
        if (!Config::forClass($modelClass)->get('automatic_live_sort')) {
            return;
        }

        $sortField = CatalogPageExtension::getClassSortFieldName($modelClass);
        $tableName = DataObject::getSchema()->tableForField($modelClass, $sortField);
        if ($tableName === null) {
            throw new \Exception("Sort field {$sortField} could not be found in table hierarchy for {$modelClass}.");
        }

        foreach ($sortedIDs as $sortValue => $itemID) {
            DB::prepared_query('UPDATE "' . $tableName . '_Live" SET "' . $sortField . '"=? WHERE "ID"=?', [$sortValue, $itemID]);
            $version = DB::prepared_query('SELECT Version FROM "' . $tableName . '_Versions" WHERE "RecordID"=? ORDER BY "ID" DESC', [$itemID]);
            DB::prepared_query('UPDATE "' . $tableName . '_Live" SET "Version"=? WHERE "ID"=?', [$version->first()['Version'], $itemID]);
        }
    }
}
