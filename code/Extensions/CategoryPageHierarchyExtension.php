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
        if ($this->shouldFilter() && $this->owner->hasExtension('HidePageChildrenExtension')) {
            $stageChildren = $stageChildren->exclude("ClassName", $this->getExcludedSiteTreeClassNames());
        }
    }

    /**
     * Loops through subclasses of the owner (intended to be SiteTree) and checks if they've been hidden.
     *
     * See https://github.com/silverstripe/silverstripe-lumberjack/blob/1.1/code/extensions/Lumberjack.php#L64-L72
     *
     *
     * @todo: cache result as it's global
     * @return array
     **/
    public function getExcludedSiteTreeClassNames()
    {
        $classes = array();
        $siteTreeClasses = ClassInfo::subclassesFor('SiteTree');
        foreach ($siteTreeClasses as $class) {
            if (singleton($class)->hasExtension('CatalogPageExtension')) {
                $classes[$class] = $class;
            }
        }
        return $classes;
    }


    /**
     * Checks if we're on a controller where we should filter. ie. Are we loading the SiteTree?
     *
     * See https://github.com/silverstripe/silverstripe-lumberjack/blob/1.1/code/extensions/Lumberjack.php#L64-L72
     *
     * @return bool
     */
    protected function shouldFilter()
    {
        $controller = Controller::curr();
        return $controller instanceof LeftAndMain
        && in_array($controller->getAction(), array("treeview", "listview", "getsubtree"));
    }
}
