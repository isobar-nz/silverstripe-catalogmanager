<?php

/**
 * Class CatalogPageExtension
 */
class CatalogPageExtension extends DataExtension
{
    /**
     * @config
     * @var array
     */
    private static $parentClass;

    /**
     * @config
     * @var bool
     */
    private static $can_duplicate = true;

    /**
     * Name of the sorting column. SiteTree has a col named "Sort", we use this as default
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

        if ($pages = $this->getCatalogParents()) {
            if ($pages && $pages->exists()) {
                if ($pages->count() == 1) {
                    $fields->addFieldToTab('Root.Main', HiddenField::create('ParentID', 'ParentID', $pages->first()->ID));
                } else {
                    $parentID = $this->owner->ParentID ? : $pages->first()->ID;
                    $fields->addFieldToTab('Root.Main', DropdownField::create('ParentID', _t('CatalogManager.PARENTPAGE', 'Parent Page'), $pages->map('ID', 'Title'), $parentID));
                }
            } else {
                throw new Exception('You must create a parent page of class ' . implode(',', $parentClass));
            }
        }
    }

    /**
     * Returns the parent classes defined from the config as an array
     * @return array
     */
    public function getParentClasses()
    {
        $parentClasses = $this->owner->stat('parentClass');

        if (!is_array($parentClasses) && $parentClasses != null) {
            return array($parentClasses);
        }

        return $parentClasses;
    }

    /**
     * Gets the fieldname for the sort column. As we're on a subclass of SiteTree we assume 'Sort' as default.
     * Can be overwritten using $sort_column param on extended class.
     * Set $sort_column config to false to disable sorting in the gridfield
     *
     * @return string
     */
    public function getSortFieldname()
    {
        return ($this->owner->config()->get('sort_column') === false)
            ? false
            : ($this->owner->config()->get('sort_column') ? : 'Sort');
    }

    /**
     * Gets the parents of this page
     *
     * @return bool|DataList
     */
    public function getCatalogParents()
    {
        $parentClass = $this->getParentClasses();
        if (count($parentClass)) {
            $pages = SiteTree::get()->filter(array('ClassName' => array_values($parentClass)));
            return $pages;
        }
        return false;
    }
}
