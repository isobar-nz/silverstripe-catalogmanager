<?php

namespace LittleGiant\CatalogManager\ModelAdmin;

use SilverStripe\ORM\DB;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\FormAction;
use SilverStripe\View\Requirements;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use LittleGiant\CatalogManager\Actions\GridFieldPublishAction;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use LittleGiant\CatalogManager\Extensions\CatalogPageExtension;
use LittleGiant\CatalogManager\Forms\CatalogPageGridFieldItemRequest;

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
            $form = $this->getCatalogEditForm($model, $id, $fields);
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
     * @param \SilverStripe\CMS\Model\SiteTree|\LittleGiant\CatalogManager\Extensions\CatalogPageExtension $model
     * @param null|string $id
     * @param null|string $fields
     * @return \SilverStripe\Forms\Form
     */
    protected function getCatalogEditForm($model, $id = null, $fields = null)
    {
        $originalStage = Versioned::get_stage();
        Versioned::set_stage(Versioned::DRAFT);
        $exportButton = new GridFieldExportButton('buttons-before-left');
        $exportButton->setExportColumns($this->getExportFields());
        $listField = GridField::create(
            $this->sanitiseClassName($this->modelClass),
            false,
            $this->getList(),
            $fieldConfig = GridFieldConfig_RecordEditor::create(static::config()->get('page_length'))
                ->addComponent($exportButton)
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
        
        if ($this->showImportForm) {
            $fieldConfig->addComponent(
                GridFieldImportButton::create('buttons-before-left')
                    ->setImportForm($this->ImportForm())
                    ->setModalTitle(_t('SilverStripe\\Admin\\ModelAdmin.IMPORT', 'Import from CSV'))
            );
        }

        $form = Form::create(
            $this,
            'EditForm',
            new FieldList($listField),
            new FieldList()
        )->setHTMLID('Form_EditForm');

        if (count($model->getCatalogParents()) === 0) {
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
}
