<?php

/*
 * This file is part of the RollerworksPasswordStrengthValidator package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\PasswordStrength\Tests\Validator;

use Rollerworks\Component\PasswordStrength\Blacklist\ArrayProvider;
use Rollerworks\Component\PasswordStrength\Tests\BlackListMockProviderTrait;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\Blacklist;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\BlacklistValidator;
use Symfony\Component\Validator\Exception\RuntimeException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @internal
 * @group legacy
 */
final class BlacklistValidationTest extends ConstraintValidatorTestCase
{
    use BlackListMockProviderTrait;

    protected function createValidator()
    {
        $provider = new ArrayProvider(['test', 'foobar']);

        return new BlacklistValidator($provider);
    }

    /**
     * @test
     */
    public function null_is_valid()
    {
        $this->validator->validate(null, new Blacklist());

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function empty_string_is_valid()
    {
        $this->validator->validate('', new Blacklist());

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function expects_string_compatible_type()
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), new Blacklist());
    }

    /**
     * @test
     */
    public function not_black_listed()
    {
        $constraint = new Blacklist();
        $this->validator->validate('weak', $constraint);
        $this->validator->validate('tests', $constraint);

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function black_listed()
    {
        $constraint = new Blacklist([
            'message' => 'myMessage',
        ]);
        $this->validator->validate('test', $constraint);

        $this->buildViolation('myMessage')
            ->setInvalidValue('test')
            ->assertRaised()
        ;
    }

    /**
     * @test
     */
    public function uses_different_provider()
    {
        $loaders = $this->createLoadersContainer(['array' => $this->createMockedProvider('dope')]);
        $defaultProvider = new ArrayProvider(['test', 'foobar']);

        $this->validator = new BlacklistValidator($defaultProvider, $loaders);
        $this->validator->initialize($this->context);

        $this->validator->validate('test', new Blacklist(['message' => 'from-default']));
        $this->validator->validate('dope', new Blacklist(['message' => 'from-custom', 'provider' => 'array']));

        $this
            ->buildViolation('from-default')
                ->setInvalidValue('test')
            ->buildNextViolation('from-custom')
                ->setInvalidValue('dope')
            ->assertRaised()
        ;
    }

    /**
     * @test
     */
    public function throws_exception_for_unsupported_provider()
    {
        $loaders = $this->createLoadersContainer([]);
        $defaultProvider = new ArrayProvider(['test', 'foobar']);

        $this->validator = new BlacklistValidator($defaultProvider, $loaders);
        $this->validator->initialize($this->context);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to use blacklist provider "array", eg. no blacklists were configured or this provider is not supported.');

        $this->validator->validate('dope', new Blacklist(['message' => 'myMessage', 'provider' => 'array']));
    }

    /**
     * @test
     */
    public function throws_exception_when_no_providers_were_given()
    {
        $defaultProvider = new ArrayProvider(['test', 'foobar']);

        $this->validator = new BlacklistValidator($defaultProvider);
        $this->validator->initialize($this->context);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to use blacklist provider "array", eg. no blacklists were configured or this provider is not supported.');

        $this->validator->validate('dope', new Blacklist(['message' => 'myMessage', 'provider' => 'array']));
    }
}
