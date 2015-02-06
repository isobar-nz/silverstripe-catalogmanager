# SilverStripe Catalog Manager

Catalog administration via a LeftAndMain like interface. Lets you edit and create pages outside of the SiteTree.

## Features


## Installation

Installation via composer

```bash
$ composer require littlegiant/silverstripe-catalogmanager
```

## How to use

### Pages

Add the following to a configuration yml file:

```yml
CatalogPage:
  extensions:
    - CatalogPageExtension
  parentClass:
    - 'CatalogParentPage'
```

Where `CatalogPage` is the page type you wish to administer (e.g. BlogEntry) and `CatalogParentPage` is where the pages
should be stored in the SiteTree (e.g. BlogHolder). You may have multiple instances of the parent, the administration
will provide users with a drop down to choose which page should be the parent.

You can also create pages in the root of the project (i.e. ParentID = 0) by not providing a parent class.

```yml
LandingPage:
  extensions:
    - CatalogPageExtension
```

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
  parentClass:
    - 'CatalogParentPage'
  can_duplicate: false
```

You can add drag and drop sorting using GridFieldSortableRows when you add the `sort_column` setting to your configuration.
Uses column `Sort` by default which is default in SiteTree and is added by CatalogDataObjectExtension.

```yml
CatalogPage:
  extensions:
    - CatalogPageExtension
  parentClass:
    - 'CatalogParentPage'
  sort_column: 'CustomSort'
```

If you want to disable drag and drop sorting just set `sort_column` to false

```yml
CatalogPage:
  extensions:
    - CatalogPageExtension
  parentClass:
    - 'CatalogParentPage'
  sort_column: false
```

Sort columns automatically update the sort column of both the staged and live versions of the object. To disable this,
you can set the configuration option `automatic_live_sort` to false through your config.

```yml
CatalogPage:
  extensions:
    - CatalogPageExtension
  parentClass:
    - 'CatalogParentPage'
  automatic_live_sort: false
```

## License

The MIT License (MIT)

Copyright (c) 2015 Little Giant Design Ltd

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

## Contributing


### Code guidelines

This project follows the standards defined in:

* [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
* [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
