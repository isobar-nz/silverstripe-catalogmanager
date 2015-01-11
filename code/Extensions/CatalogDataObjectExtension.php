<?php

class CatalogDataObjectExtension extends DataExtension
{
    private static $parentClass;
    private static $can_duplicate = true;

    private static $db = array(
        'Sort' => 'Int'
    );

    private static $summary_fields = array(
        'getEnabledStatus' => 'Enabled'
    );

    public function getEnabledStatus()
    {
        return $this->owner->isPublished() ? 'Yes' : 'No';
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Version');
        $fields->removeByName('Versions');

        $parentClass = $this->owner->stat('parentClass');

        if ($pages = DataObject::get()->filter(array('ClassName' => array_values($parentClass)))) {

            if ($pages->exists()) {
                if ($pages->count() == 1) {

                    $fields->push(HiddenField::create('ParentID', 'ParentID', $pages->first()->ID));

                } else {
                    $parentID = $this->owner->ParentID ? : $pages->first()->ID;
                    $fields->push(DropdownField::create('ParentID', 'Parent Page', $pages->map('ID', 'Title'), $parentID));
                }
            } else {
                throw new Exception('You must create a parent page of class ' . $parentClass);
            }

        } else {
            throw new Exception('Parent class ' . $parentClass . ' does not exist.');
        }
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        $id = $this->owner->ID;
        if (empty($id)) {
            return true;
        }
        if (is_numeric($id)) {
            return false;
        }
    }
    /**
     * @return bool
     */
    public function isPublished()
    {
        if ($this->isNew()) {
            return false;
        }

        $table = $this->owner->class;

        while (($p = get_parent_class($table)) !== 'DataObject') {
            $table = $p;
        }

        return (bool) DB::query("SELECT \"ID\" FROM \"{$table}_Live\" WHERE \"ID\" = {$this->owner->ID}")->value();
    }
    /**
     * @param $value
     * @return string
     */
    protected function getBooleanNice($value)
    {
        return $value ? 'Yes' : 'No';
    }
    /**
     * @return mixed
     */
    public function isPublishedNice()
    {
        return $this->getBooleanNice($this->isPublished());
    }
    /**
     * @return mixed
     */
    public function isModifiedNice()
    {
        return $this->getBooleanNice($this->stagesDiffer('Stage', 'Live'));
    }

    public function doPublish()
    {
        $original = Versioned::get_one_by_stage($this->owner->ClassName, "Live", "\"{$this->owner->ClassName}\".\"ID\" = {$this->owner->ID}");
        if(!$original) $original = new $this->owner->ClassName();

        //$this->PublishedByID = Member::currentUser()->ID;
        $this->owner->write();
        $this->owner->publish("Stage", "Live");

        DB::query("UPDATE \"{$this->owner->ClassName}_Live\"
			SET \"Sort\" = ( SELECT \"{$this->owner->ClassName}\".\"Sort\" FROM \"{$this->owner->ClassName}\" WHERE \"{$this->owner->ClassName}\".\"ID\" = \"{$this->owner->ClassName}_Live\".\"ID\")
			WHERE EXISTS ( SELECT \"{$this->owner->ClassName}_Live\".\"Sort\" FROM \"{$this->owner->ClassName}\" WHERE \"{$this->owner->ClassName}\".\"ID\" = \"{$this->owner->ClassName}_Live\".\"ID\")");

        return true;
    }

    public function doUnpublish()
    {
        if(!$this->owner->ID) return false;
        $origStage = Versioned::current_stage();
        Versioned::reading_stage('Live');
        // This way our ID won't be unset
        $clone = clone $this;
        $clone->owner->delete();
        Versioned::reading_stage($origStage);
        // If we're on the draft site, then we can update the status.
        // Otherwise, these lines will resurrect an inappropriate record
        if(DB::query("SELECT \"ID\" FROM \"{$this->owner->ClassName}\" WHERE \"ID\" = {$this->owner->ID}")->value()
            && Versioned::current_stage() != 'Live') {
            $this->owner->write();
        }

        return true;
    }
}