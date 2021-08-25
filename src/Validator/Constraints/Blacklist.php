<?php

/*
 * This file is part of the RollerworksPasswordStrengthValidator package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\PasswordStrength\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Blacklist extends Constraint
{
    public $message = 'password_blacklisted';

    /**
     * @var string
     */
    public $provider;

    public function __construct(
        $options = null,
        array $groups = null,
        $payload = null,
        string $provider = null,
        string $message = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);

        $this->provider = $provider ?? $this->provider;
        $this->message = $message ?? $this->message;
    }
}
