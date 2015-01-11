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
  parentClass:
    - 'CatalogParentPage'
    - 'CatalogParentPage2'
  can_duplicate: true
```

Where `CatalogPage` is the page type you wish to administer (e.g. BlogEntry), `CatalogParentPage` and `CatalogParentPage2` are an array of parent classes where the pages can be stored in the SiteTree (e.g. BlogHolder). You may have multiple instances of the parent, the administration
will provide users with a drop down to choose which page should be the parent.

Then simply extend CatalogPageAdmin instead of ModelAdmin.

### DataObjects

You can also manage DataObjects through the `CatalogDataObjectExtension` (documentation coming soon)

### Translations

If you are using the translatable module, you can use the TranslatableCatalogExtension to provide functionality for
choosing languages.

```yml
CatalogPageAdmin:
  extensions:
    - TranslatableCatalogExtension
```

## License

SilverStripe Single Page Administration is released under the MIT license