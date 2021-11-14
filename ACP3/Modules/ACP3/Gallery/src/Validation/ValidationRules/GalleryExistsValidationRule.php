<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Validation\ValidationRules;

use ACP3\Core\Validation\ValidationRules\AbstractValidationRule;
use ACP3\Modules\ACP3\Gallery\Repository\GalleryRepository;

class GalleryExistsValidationRule extends AbstractValidationRule
{
    public function __construct(private GalleryRepository $galleryRepository)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        return $this->galleryRepository->galleryExists($data);
    }
}
