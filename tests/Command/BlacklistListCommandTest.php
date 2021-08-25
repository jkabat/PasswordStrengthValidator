<?php

/*
 * This file is part of the RollerworksPasswordStrengthValidator package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\PasswordStrength\Tests\Command;

use Rollerworks\Component\PasswordStrength\Command\BlacklistListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
final class BlacklistListCommandTest extends BlacklistCommandTestCase
{
    /**
     * @test
     */
    public function list()
    {
        $application = new Application();
        $command = new BlacklistListCommand($this->createLoadersContainer(['default' => self::$blackListProvider]));
        $application->add($command);

        $command = $application->find('rollerworks-password:blacklist:list');

        $blackListedWords = ['test', 'foobar', 'kaboom'];

        foreach ($blackListedWords as $word) {
            self::$blackListProvider->add($word);
        }

        foreach ($blackListedWords as $word) {
            self::assertTrue(self::$blackListProvider->isBlacklisted($word));
            self::$blackListProvider->add($word);
        }

        $commandTester = new CommandTester($command);

        $commandTester->execute(['command' => $command->getName()]);

        $display = $commandTester->getDisplay(true);

        // Words may be displayed in any order, so check each of them
        foreach ($blackListedWords as $word) {
            self::assertMatchesRegularExpression("/([\n]|^){$word}[\n]/s", $display);
            self::$blackListProvider->add($word);
        }
    }
}
