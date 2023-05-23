<?php

declare(strict_types=1);

namespace Calculation\CommissionTask\Tests\Service;

use PHPUnit\Framework\TestCase;
use Calculation\CommissionTask\Service\User;

class UserTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        $this->user = new User(2, 'private');
    }
}
