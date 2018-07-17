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
        $editForm = parent::ItemEditForm();

        // Already has parent
        if ($this->record->ParentID) return $editForm;

        // Set a default parent id for the record, even if it will change
        $parents = $this->record->getCatalogParents();

        if ($parents === null) return $editForm; // Root page

        $first = $parents->first();
        if ($first !== null) {
            $this->record->ParentID = $first->ID;
        }

        return $editForm;
    }

    /**
     *
     */
    public function pushCurrent()
    {
        $this->getController()->pushCurrent();
    }
}
