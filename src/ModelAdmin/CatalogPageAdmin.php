<?php

namespace LittleGiant\CatalogManager\ModelAdmin;

use LittleGiant\CatalogManager\Actions\GridFieldPublishAction;
use LittleGiant\CatalogManager\Extensions\CatalogPageExtension;
use LittleGiant\CatalogManager\Forms\CatalogPageGridFieldItemRequest;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Class CatalogPageAdmin
 * @package LittleGiant\CatalogManager\ModelAdmin
 */
abstract class CatalogPageAdmin extends ModelAdmin
{
    /**
     * Initialize requirements for this view
     */
    public function init()
    {
        parent::init();
        Requirements::javascript('silverstripe/cms: client/dist/js/bundle.js');
        Requirements::css('silverstripe/cms: client/dist/styles/bundle.css');
    }

    /**
     * @inheritdoc
     */
    public function getEditForm($id = null, $fields = null)
    {
        /** @var \SilverStripe\CMS\Model\SiteTree|\LittleGiant\CatalogManager\Extensions\CatalogPageExtension $model */
        $model = singleton($this->modelClass);

        if ($model->has_extension(CatalogPageExtension::class)) {
            $form = $this->getCatalogEditForm($id, $fields, $model);
        } elseif (method_exists($model, 'getAdminListField')) {
            $form = Form::create(
                $this,
                'EditForm',
                new FieldList($model->getAdminListField()),
                new FieldList(FormAction::create('doSave', 'Save'))
            )->setHTMLID('Form_EditForm');
        } else {
            $form = parent::getEditForm();
        }

        $this->extend('updateEditForm', $form);
        return $form;
    }

    /**
     * @param null $id
     * @param null $fields
     * @param \SilverStripe\CMS\Model\SiteTree|\LittleGiant\CatalogManager\Extensions\CatalogPageExtension $model
     * @return \SilverStripe\Forms\Form
     */
    protected function getCatalogEditForm($id = null, $fields = null, $model)
    {
        $originalStage = Versioned::get_stage();
        Versioned::set_stage(Versioned::DRAFT);
        $listField = GridField::create(
            $this->sanitiseClassName($this->modelClass),
            false,
            $this->getList(),
            $fieldConfig = GridFieldConfig_RecordEditor::create(static::config()->get('page_length'))
                ->removeComponentsByType(GridFieldFilterHeader::class)
                ->removeComponentsByType(GridFieldDeleteAction::class)
                ->addComponent(new GridfieldPublishAction())
        );
        Versioned::set_stage($originalStage);

        /** @var \SilverStripe\Forms\GridField\GridFieldDetailForm $detailForm */
        $detailForm = $listField->getConfig()->getComponentByType(GridFieldDetailForm::class);
        if ($detailForm !== null) {
            $detailForm->setItemRequestClass(CatalogPageGridFieldItemRequest::class);
        }

        $sortField = $model->getSortFieldName();
        if ($sortField !== null) {
            $fieldConfig->addComponent(new GridFieldOrderableRows($sortField));
        }

        return Form::create(
            $this,
            'EditForm',
            new FieldList($listField),
            new FieldList()
        )->setHTMLID('Form_EditForm');
    }

    /**
     * Hook to update sort column on live versions of items after a sort has occured.
     * @param $items
     */
    public function onAfterGridFieldRowSort($items)
    {
        /** @var \LittleGiant\CatalogManager\Extensions\CatalogPageExtension|\SilverStripe\CMS\Model\SiteTree $model */
        $model = singleton($this->modelClass);
        if (!$model::config()->get('automatic_live_sort')) {
            return;
        }

        // if the sort is the default, then we should update SiteTree. If its a custom sort, update the model.
        $sortField = $model->getSortFieldName();
        $tableName = DataObjectSchema::singleton()->tableForField($this->modelClass, $sortField);
        if ($tableName === null) {
            throw new \Exception("Sort field {$sortField} could not be found in table hierarchy for {$this->modelClass}.");
        }

        foreach ($items as $item) {
            if ($item instanceof $this->modelClass) {
                DB::query("UPDATE \"{$tableName}_Live\" SET \"{$model->getSortFieldname()}\"=\"{$item->{$sortField}}\" WHERE \"ID\"=\"{$item->ID}\"");
            }
        }
    }
}
