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
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ValidationResult;
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

        $form = Form::create(
            $this,
            'EditForm',
            new FieldList($listField),
            new FieldList()
        )->setHTMLID('Form_EditForm');

        if ($model->getCatalogParents()->count() === 0) {
            $form->setMessage($this->getMissingParentsMessage($model), ValidationResult::TYPE_WARNING);
        }

        return $form;
    }

    /**
     * @param \SilverStripe\ORM\DataObject|CatalogPageExtension $model
     * @return string
     */
    protected function getMissingParentsMessage(DataObject $model)
    {
        return _t(self::class . '.PARENT_REQUIRED',
            'You must create a {parent_class_list} before you can create a {model_name}.', [
                'parent_class_list' => $this->getParentClassesForMessage($model->getParentClasses()),
                'model_name'        => $model->i18n_singular_name(),
            ]);
    }

    /**
     * @param string[] $classes
     * @return string
     */
    protected function getParentClassesForMessage(array $classes)
    {
        $parentNames = [];
        foreach ($classes as $parentClass) {
            if ($parentClass === $this->modelClass) {
                continue;
            }

            /** @var DataObject $parent */
            $parent = singleton($parentClass);
            $parentNames[] = $parent->i18n_singular_name();
        }

        if (count($parentNames) === 1) {
            return $parentNames[0];
        }

        if (count($parentNames) === 2) {
            return "{$parentNames[0]} or {$parentNames[1]}";
        }

        $last = array_pop($parentNames);
        return implode(', ', $parentNames) . " or {$last}";
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
