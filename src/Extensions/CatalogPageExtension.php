<?php

namespace LittleGiant\CatalogManager\Extensions;

use Exception;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataExtension;

/**
 * Class CatalogPageExtension
 * @package LittleGiant\CatalogManager\Extensions
 * @property \SilverStripe\CMS\Model\SiteTree|\SilverStripe\Versioned\Versioned|\SilverStripe\Versioned\RecursivePublishable $owner
 */
class CatalogPageExtension extends DataExtension
{
    use ExtensionDefinesDefaultConfig;

    const CONFIG_SETTINGS_WITH_DEFAULTS = [
        'parent_classes',
        'can_duplicate',
        'sort_column',
        'automatic_live_sort',
    ];

    /**
     * @config
     * @var array
     */
    private static $parent_classes = [];

    /**
     * @config
     * @var bool
     */
    private static $can_duplicate = true;

    /**
     * Name of the sorting column. SiteTree has a column named "Sort", we use this as default.
     *
     * @config
     * @var string
     */
    private static $sort_column = 'Sort';

    /**
     * @config
     * @var bool
     */
    private static $automatic_live_sort = true;

    /**
     * @inheritdoc
     * @param array $fields
     */
    public function updateSummaryFields(&$fields)
    {
        $fields['isPublishedNice'] = 'Enabled';
    }

    /**
     * Returns whether this page is published for the GridField
     * @return string
     */
    public function isPublishedNice()
    {
        return $this->owner->isPublished() ? 'Yes' : 'No';
    }

    /**
     * Adds functionality to CMS fields
     *
     * @param FieldList $fields
     * @throws Exception
     */
    public function updateCMSFields(FieldList $fields)
    {
        $parentClass = $this->getParentClasses();
        $pages = $this->getCatalogParents();

        if ($pages === null) {
            return;
        }

        $parentCount = $pages->count();

        if ($parentCount === 0) {
            throw new Exception('You must create a parent page with one of these classes: ' . implode(', ', $parentClass));
        } elseif ($parentCount === 1) {
            $field = HiddenField::create('ParentID', 'ParentID', $pages->first()->ID);
        } else {
            $defaultParentID = $this->owner->ParentID ?: $pages->first()->ID;
            $field = DropdownField::create('ParentID', _t(__CLASS__ . '.PARENTPAGE', 'Parent Page'), $pages->map(), $defaultParentID);
        }

        $fields->addFieldToTab('Root.Main', $field);
    }

    /**
     * Returns the parent classes defined from the config as an array
     * @return array
     */
    public function getParentClasses()
    {
        $parentClasses = $this->owner->config()->get('parent_classes');

        return $parentClasses === null || is_array($parentClasses)
            ? $parentClasses
            : [$parentClasses];
    }

    /**
     * Gets the parents of this page
     *
     * @return null|\SilverStripe\ORM\DataList
     */
    public function getCatalogParents()
    {
        $parentClasses = $this->getParentClasses();
        $parents = null;

        if ($parentClasses !== null) {
            $parents = SiteTree::get()->filter('ClassName', $parentClasses);
        }
        $this->owner->extend('updateCatalogParents', $parents);

        return $parents;
    }

    /**
     * Gets the field name for the sort column. As we're on a subclass of SiteTree we assume 'Sort' as default.
     * Can be overwritten using $sort_column param on extended class.
     * Set $sort_column config to false to disable sorting in the gridfield
     *
     * @return string|null
     */
    public function getSortFieldName()
    {
        $sortColumn = $this->owner->config()->get('sort_column');

        if ($sortColumn === false) {
            return null;
        } else {
            return $sortColumn ?: 'Sort';
        }
    }

    /**
     * Prevent object creation if no parents exist.
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canCreate($member)
    {
        return $this->getCatalogParents()->count() > 0;
    }
}
