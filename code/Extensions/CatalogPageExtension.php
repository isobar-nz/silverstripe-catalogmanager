<?php

class CatalogPageExtension extends DataExtension
{

    private static $parentClass;
    private static $can_duplicate = true;

	/**
	 * Name of the sorting column. SiteTree has a col named "Sort", we use this as default
	 * @var string
	 */
	private  static $sort_column = 'Sort';

    public function getEnabledStatus()
    {
        return $this->owner->isPublished() ? 'Yes' : 'No';
    }

    public function updateCMSFields(FieldList $fields)
    {
        $parentClass = $this->owner->stat('parentClass');

        if (class_exists($parentClass)) {
            if ($pages = $parentClass::get()->filter(array('ClassName' => $parentClass))) {

                if ($pages->exists()) {
                    if ($pages->count() == 1) {

                        $fields->addFieldToTab('Root.Main', HiddenField::create('ParentID', 'ParentID', $pages->first()->ID));

                    } else {
                        $parentID = $this->owner->ParentID ? : $pages->first()->ID;
                        $fields->addFieldToTab('Root.Main', DropdownField::create('ParentID', 'Parent Page', $pages->map('ID', 'Title'), $parentID));
                    }
                } else {
                    throw new Exception('You must create a parent page of class ' . get_class($this->owner->stat('parentClass')));
                }

            } else {
                throw new Exception('Parent class ' . $this->owner->stat('parentClass') . ' does not exist.');
            }
        }
    }

	/**
	 * @inheritdoc
	 * @param array $fields
	 */
	public function updateSummaryFields(&$fields){
		$fields['getEnabledStatus'] = 'Enabled';
	}

	/**
	 * Gets the fieldname for the sort column. As we're on a subclass of SiteTree we assume 'Sort' as default.
	 * Can be overwritten using $sort_column param on extended class.
	 *
	 * @return string
	 */
	public function getSortFieldname(){
		return $this->owner->config()->get('sort_column') ?: 'Sort';
	}

}