<?php

class CatalogPageExtension extends DataExtension
{

    private static $parentClass;
    private static $can_duplicate = true;

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

        if ($pages = $this->getCatalogParents()) {

            if ($pages && $pages->exists()) {
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
            throw new Exception('Parent class ' . implode(',', $parentClass) . ' does not exist.');
        }
        #}
    }

    public function getCatalogParents()
    {
        $parentClass = $this->owner->stat('parentClass');
        if (count($parentClass)) {
            $pages = SiteTree::get()->filter(array('ClassName' => array_values($parentClass)));
            return $pages;
        }
        return false;
    }

}