# SilverStripe Catalog Manager

Catalog administration via a LeftAndMain like interface. Lets you edit and create pages outside of the SiteTree.

## Features


## Installation

Installation via composer

## How to use

### Pages

Add the following to a configuration yml file:

```yml
CatalogPage:
  extensions:
    - CatalogPageExtension
  parentClass: 'CatalogParentPage'
```

Where `CatalogPage` is the page type you wish to administer (e.g. BlogEntry) and `CatalogParentPage` is where the pages
should be stored in the SiteTree (e.g. BlogHolder). You may have multiple instances of the parent, the administration
will provide users with a drop down to choose which page should be the parent.

Then simply extend `CatalogPageAdmin` instead of `ModelAdmin`.

### DataObjects

You can also manage DataObjects through the `CatalogDataObjectExtension`

### Translations

If you are using the translatable module, you can use the TranslatableCatalogExtension to provide functionality for
choosing languages.

```yml
CatalogPageAdmin:
  extensions:
    - TranslatableCatalogExtension
```

### Options

You can disable the ability to duplicate pages through the `can_duplicate` configuration setting per object.

```yml
CatalogPage:
  extensions:
    - CatalogPageExtension
  parentClass: 'CatalogParentPage'
  can_duplicate: false
```

You can add drag and drop sorting using GridFieldSortableRows when you add the `sort_column` setting to your configuration.
Uses column `Sort` by default which is default in SiteTree and is added by CatalogDataObjectExtension.

If you want to disable drag and drop sorting just set `sort_column` to false

## License

SilverStripe Single Page Administration is released under the MIT license