<?php

class CatalogPageExtension extends DataExtension
{

    private static $parentClass;
    private static $can_duplicate;

    private static $summary_fields = array(
        'Enabled' => 'Enabled'
    );

    public function Enabled()
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
                    throw new Exception('You must create a parent page of class ' . $parentClass);
                }

            } else {
                throw new Exception('Parent class ' . $parentClass . ' does not exist.');
            }
        }
    }

}