<?php

namespace LittleGiant\CatalogManager\Extensions;

use Exception;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ArrayList;
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
        'automatic_live_sort',
        'include_parent_subclasses',
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
     * @config
     * @var bool
     */
    private static $automatic_live_sort = true;

    /**
     * @config
     * @var bool
     */
    private static $include_parent_subclasses = false;

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
        if ($this->owner->ParentID === 0) {
            // Root page
            return;
        }
        
        $parentPages = $this->getCatalogParents();
        if ($parentPages) {
            $parentCount = $parentPages->count();
            if ($parentCount === 0) {
                throw new Exception('You must create a parent page with one of these classes: ' . implode(', ', $this->getParentClasses()));
            } elseif ($parentCount === 1) {
                $field = HiddenField::create('ParentID', 'ParentID', $parentPages->first()->ID);
            } else {
                $defaultParentID = $this->owner->ParentID ?: $parentPages->first()->ID;
                $field = DropdownField::create('ParentID', _t(__CLASS__ . '.PARENTPAGE', 'Parent Page'), $parentPages->map(), $defaultParentID);
            }

            $fields->addFieldToTab('Root.Main', $field);
        }
    }

    /**
     * Returns the parent classes defined from the config as an array
     * @return string[]
     */
    public function getParentClasses()
    {
        $parentClasses = $this->owner->config()->get('parent_classes');

        if (empty($parentClasses)) {
            return [];
        } elseif (!is_array($parentClasses)) {
            $parentClasses = [$parentClasses];
        }

        if ($this->owner->config()->get('include_parent_subclasses')) {
            $parentClasses = array_reduce($parentClasses, function (array $list, $parentClass) {
                foreach (ClassInfo::subclassesFor($parentClass) as $key => $class) {
                    $list[$key] = $class;
                }

                return $list;
            }, []);
        }

        return $parentClasses;
    }

    /**
     * Gets the parents of this page
     *
     * @return \SilverStripe\ORM\DataList|\SilverStripe\ORM\ArrayList
     */
    public function getCatalogParents()
    {
        $parentClasses = $this->getParentClasses();
        $parents = null;

        if (!empty($parentClasses)) {
            $parents = SiteTree::get()->filter('ClassName', $parentClasses);
        }

        $this->owner->extend('updateCatalogParents', $parents);

        return $parents ?: new ArrayList();
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
        return static::getClassSortFieldName($this->owner);
    }

    /**
     * Gets the field name for a class's sort column. As CatalogPageExtension is applied to subclasses of SiteTree,
     * 'Sort' is default.
     * Can be overwritten using $sort_column config on extended class.
     * Set $sort_column config to false to disable sorting in the gridfield
     *
     * @param string|object $class
     * @return null|string
     */
    public static function getClassSortFieldName($class)
    {
        $sortColumn = Config::forClass($class)->get('sort_column');

        return $sortColumn === false
            ? null
            : ($sortColumn ?: 'Sort');
    }

    /**
     * Prevent object creation if no parents exist.
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canCreate($member)
    {
        // Deny create if parent doesn't exists
        $parents = $this->getCatalogParents();
        return (!$parents || $parents->count() === 0)
            ? false
            : null;
    }
}
