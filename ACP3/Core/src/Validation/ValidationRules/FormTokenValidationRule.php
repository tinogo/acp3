<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation\ValidationRules;

use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Session\SessionConstants;
use ACP3\Core\Validation\Exceptions\InvalidFormTokenException;
use ACP3\Core\Validation\Validator;
use Symfony\Component\HttpFoundation\Session\Session;

class FormTokenValidationRule extends AbstractValidationRule
{
    public function __construct(protected RequestInterface $request, protected Session $sessionHandler)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Validator $validator, $data, $field = '', array $extra = [])
    {
        if (!$this->isValid($data, $field, $extra)) {
            throw new InvalidFormTokenException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        $tokenName = SessionConstants::XSRF_TOKEN_NAME;
        $sessionToken = $this->sessionHandler->get($tokenName, '');

        return $this->request->getPost()->get($tokenName, '') === $sessionToken;
    }
}
