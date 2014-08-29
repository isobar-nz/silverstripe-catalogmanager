<?php

class CatalogPageExtension extends DataExtension
{

    private static $parentClass;

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

}