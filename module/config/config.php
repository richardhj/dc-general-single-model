<?php
/**
 * Single model DataProvider for DcGeneral
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package DcGeneral
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['sqlGetFromDca'][] = ['DcGeneral\Contao\SqlHook', 'addSqlDefinition'];
