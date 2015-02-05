<?php

/**
 * Class CategoryPageHierarchyExtension
 */
class CategoryPageHierarchyExtension extends DataExtension
{

    /**
     * @param $stageChildren
     * @param $context
     */
    public function augmentAllChildrenIncludingDeleted(&$stageChildren, &$context)
    {
        if ($this->owner->hasExtension('HidePageChildrenExtension')) {
            $stageChildren = new ArrayList();
        }
    }

}