<?php

namespace LittleGiant\CatalogManager\Extensions;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataExtension;

/**
 * Class CategoryPageHierarchyExtension
 * @package LittleGiant\CatalogManager\Extensions
 */
class CategoryPageHierarchyExtension extends DataExtension
{
    private $excludedSiteTreeClassNames = null;

    /**
     * @param \SilverStripe\ORM\DataList $stageChildren
     * @param $context
     */
    public function augmentAllChildrenIncludingDeleted(&$stageChildren)
    {
        if ($this->shouldFilter() && $this->owner->hasExtension(HidePageChildrenExtension::class)) {
            $stageChildren = $stageChildren->exclude('ClassName', $this->getExcludedSiteTreeClassNames());
        }
    }

    /**
     * Checks if we're on a controller where we should filter. ie. Are we loading the SiteTree?
     * See https://github.com/silverstripe/silverstripe-lumberjack/blob/1.1/code/extensions/Lumberjack.php#L64-L72
     *
     * @return bool
     */
    protected function shouldFilter()
    {
        $controller = Controller::curr();
        return $controller instanceof LeftAndMain
            && in_array($controller->getAction(), ["treeview", "listview", "getsubtree"]);
    }

    /**
     * Loops through subclasses of the owner (intended to be SiteTree) and checks if they've been hidden.
     * See https://github.com/silverstripe/silverstripe-lumberjack/blob/1.1/code/extensions/Lumberjack.php#L64-L72
     *
     * @return array
     */
    public function getExcludedSiteTreeClassNames()
    {
        if ($this->excludedSiteTreeClassNames === null) {
            $this->excludedSiteTreeClassNames = [];

            foreach (ClassInfo::subclassesFor(SiteTree::class) as $class) {
                if (singleton($class)->hasExtension(CatalogPageExtension::class)) {
                    $this->excludedSiteTreeClassNames[] = $class;
                }
            }
        }

        return $this->excludedSiteTreeClassNames;
    }
}
