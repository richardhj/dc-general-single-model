<?php

/**
 * This file is part of richardhj/dc-general-single-model.
 *
 * Copyright (c) 2016-2017 Richard Henkenjohann
 *
 * @package   richardhj/contao-textfield-multiple
 * @author    Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright 2016-2017 Richard Henkenjohann
 * @license   https://github.com/richardhj/dc-general-single-model/blob/master/LICENSE LGPL-3.0
 */


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['sqlGetFromDca'][] = ['Richardhj\DcGeneral\Contao\SqlHook', 'addSqlDefinition'];
