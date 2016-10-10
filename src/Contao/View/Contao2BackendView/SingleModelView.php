<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 *
 * Copyright (c) 2015-2016 Richard Henkenjohann
 *
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */

namespace DcGeneral\Contao\View\Contao2BackendView;


use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;


class SingleModelView extends BaseView
{

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When the model could not be found by the data provider.
     */
    public function edit(Action $action)
    {
        $environment = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider(null);

        $this->checkRestoreVersion();

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig());

        if (!$model) {
            throw new DcGeneralRuntimeException('Could not retrieve model');
        }

        // We need to keep the original data here.
        $originalModel = clone $model;
        $originalModel->setId($model->getId());

        return $this->createEditMask($model, $originalModel, null, null);
    }
}
