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
        if (!empty($this->record) && !$this->record->ParentID) {
            // Set a default parent id for the record, even if it will change
            $parents = $this->record->getCatalogParents();
            $first = $parents !== null ? $parents->first() : null;
            if ($first !== null) {
                $this->record->ParentID = $first->ID;
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
