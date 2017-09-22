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

namespace Richardhj\DcGeneral\View;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;


/**
 * Class SingleModelView
 * @package Richardhj\DcGeneral\View
 */
class SingleModelView extends BaseView
{

//    /**
//     * {@inheritdoc}
//     *
//     * @throws DcGeneralRuntimeException When the model could not be found by the data provider.
//     */
//    public function edit(Action $action)
//    {
//        $environment = $this->getEnvironment();
//        $dataProvider = $environment->getDataProvider(null);
//
//        $this->checkRestoreVersion();
//
//        $model = $dataProvider->fetch($dataProvider->getEmptyConfig());
//
//        if (!$model) {
//            throw new DcGeneralRuntimeException('Could not retrieve model');
//        }
//
//        // We need to keep the original data here.
//        $originalModel = clone $model;
//        $originalModel->setId($model->getId());
//
//        return $this->createEditMask($model, $originalModel, null, null);
//    }
}
