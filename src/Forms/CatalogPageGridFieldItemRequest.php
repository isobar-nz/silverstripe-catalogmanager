<?php

namespace LittleGiant\CatalogManager\Forms;

use SilverStripe\Versioned\VersionedGridFieldItemRequest;

/**
 * Class CatalogPageGridFieldItemRequest
 * @package LittleGiant\CatalogManager\Forms
 * @property \SilverStripe\CMS\Model\SiteTree|\LittleGiant\CatalogManager\Extensions\CatalogPageExtension $record
 */
class CatalogPageGridFieldItemRequest extends VersionedGridFieldItemRequest
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'ItemEditForm',
    ];

    /**
     * @return \SilverStripe\Forms\Form
     */
    public function ItemEditForm()
    {
        if (!$this->record->ParentID) {
            // set a parent id for the record, even if it will change
            $parents = $this->record->getCatalogParents();
            if ($parents !== null && $parents->exists()) {
                $this->record->ParentID = $parents->first()->ID;
            }
        }

        return parent::ItemEditForm();
    }

    /**
     *
     */
    public function pushCurrent()
    {
        $this->getController()->pushCurrent();
    }
}
