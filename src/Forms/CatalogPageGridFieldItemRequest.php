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

        if ($this->record->ParentID) {
            // Already has parent
            return $editForm;
        }

        // Set a default parent id for the record, even if it will change
        $parents = $this->record->getCatalogParents();
        if ($parents !== null && $first = $parents->first()) {
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
