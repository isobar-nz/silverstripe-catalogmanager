<?php

namespace LittleGiant\CatalogManager\Actions;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\ValidationException;

/**
 * Class GridFieldPublishAction
 * @package LittleGiant\CatalogManager\Actions
 *
 * Gridfield Action to publish / unpublish a page in catalog manager
 * @author Werner Krauß <werner.krauss@netwerkstatt.at>
 */
class GridFieldPublishAction implements GridField_ColumnProvider, GridField_ActionProvider
{
    /**
     * {@inheritDoc}
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }


    /**
     * {@inheritDoc}
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        return $columnName === 'Actions'
            ? ['title' => '']
            : [];
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        /** @var \SilverStripe\ORM\DataObject|\SilverStripe\Versioned\Versioned $record */
        if (!$record->canEdit()) {
            return null;
        }

        if ($record->isPublished()) {
            $field = GridField_FormAction::create($gridField, "UnPublish {$record->ID}", false, "unpublish", ['RecordID' => $record->ID])
                ->addExtraClass('gridfield-button-unpublish font-icon-cancel')
                ->setAttribute('title', _t(__CLASS__ . '.BUTTONUNPUBLISH', 'Unpublish'))
                ->setDescription(_t(__CLASS__ . '.BUTTONUNPUBLISHDESC', 'Unpublish'));
        } else {
            $field = GridField_FormAction::create($gridField, "Publish {$record->ID}", false, "publish", ['RecordID' => $record->ID])
                ->addExtraClass('gridfield-buttonnpublish font-icon-rocket')
                ->setAttribute('title', _t(__CLASS__ . '.BUTTONSAVEPUBLISH', 'Save & Publish'))
                ->setDescription(_t(__CLASS__ . '.BUTTONSAVEPUBLISHDESC', 'Save & Publish'));
        }

        $field->addExtraClass('btn--icon-md btn--no-text grid-field__icon-action');
        return $field->Field();
    }

    /**
     * {@inheritDoc}
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if (!in_array($actionName, $this->getActions($gridField))) {
            return;
        }

        /** @var null|\SilverStripe\ORM\DataObject|\SilverStripe\Versioned\Versioned|\SilverStripe\Versioned\RecursivePublishable $item */
        $item = $gridField->getList()->byID($arguments['RecordID']);
        if ($item === null) {
            return;
        }

        if (!$item->canEdit()) {
            throw new ValidationException(_t(__CLASS__ . '.PUBLISHPERMISSIONFAILURE',
                'No permission to publish or unpublish item'));
        }

        if ($actionName === 'publish') {
            $item->publishRecursive();
        } elseif ($actionName === 'unpublish') {
            $item->doUnpublish();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getActions($gridField)
    {
        return ['publish', 'unpublish'];
    }
}
