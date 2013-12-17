<?php

class CategoryPageHierarchyExtension extends DataExtension
{

    public function augmentAllChildrenIncludingDeleted(&$stageChildren, &$context)
    {
        if ($this->owner->hasExtension('HidePageChildrenExtension')) {
            $stageChildren = new ArrayList();
        }
    }

}