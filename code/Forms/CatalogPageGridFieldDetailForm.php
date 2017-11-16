<?php

use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Versioned\Versioned;

/**
 * Class CatalogPageGridFieldDetailForm
 */
class CatalogPageGridFieldDetailForm extends GridFieldDetailForm
{
}

/**
 * Class CatalogPageGridFieldDetailForm_ItemRequest
 */
class CatalogPageGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'ItemEditForm'
    );

    /**
     * @return Form
     */
    public function ItemEditForm()
    {
        if (!$this->record->isPublished()) {
            Versioned::reading_stage('Stage');
        }
        if (!$this->record->ParentID) {
            // set a parent id for the record, even if it will change
            $parents = $this->record->getCatalogParents();
            if ($parents && $parents->count()) {
                $this->record->ParentID = $parents->first()->ID;
            }
        }

        return parent::ItemEditForm();
    }
}
