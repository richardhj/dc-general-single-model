# DcGeneral "Single Model" data provider

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]]()
[![Dependency Status][ico-dependencies]][link-dependencies]

This package provides a data provider and view for the DcGeneral. You can use it if your dca has to handle one dataset exactly – if you do not need a list view, just the edit mask. It is great to get rid of the DC_File.
## Install

Via Composer

``` bash
$ composer require richardhj/dc-general-single-model
```

## Usage

For extension developers.

### Minimum dca

```php
/**
 * DCA
 */
$GLOBALS['TL_DCA'][$table] = [

    // Config
    'config' => [
        'dataContainer' => 'General',
        'forceEdit'     => true,
    ],
    'dca_config'   => [
        'data_provider' => [
            'default' => [
                'class' => 'DcGeneral\Data\SingleModelDataProvider',
            ],
        ],
        'view'          => 'DcGeneral\Contao\View\Contao2BackendView\SingleModelView',
    ],

```
The extension create a database table (with key=>value structure) on its when using the database installer.

[ico-version]: https://img.shields.io/packagist/v/richardhj/dc-general-single-model.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-LGPL-brightgreen.svg?style=flat-square
[ico-dependencies]: https://www.versioneye.com/php/richardhj:dc-general-single-model/badge.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/richardhj/dc-general-single-model
[link-dependencies]: https://www.versioneye.com/php/richardhj:dc-general-single-model
