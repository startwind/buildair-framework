<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testRolesAlwaysContainRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesAreUniqueAfterManualAdd(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testIsAdminReturnsFalseByDefault(): void
    {
        $user = new User();
        $this->assertFalse($user->isAdmin());
    }

    public function testIsAdminReturnsTrueWhenRoleAdminSet(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($user->isAdmin());
    }

    public function testIsPayingReturnsFalseByDefault(): void
    {
        $user = new User();
        $this->assertFalse($user->isPaying());
    }

    public function testSetIsPayingTrue(): void
    {
        $user = new User();
        $user->setIsPaying(true);
        $this->assertTrue($user->isPaying());
    }

    public function testSetIsPayingFalse(): void
    {
        $user = new User();
        $user->setIsPaying(true);
        $user->setIsPaying(false);
        $this->assertFalse($user->isPaying());
    }

    public function testIsVerifiedReturnsFalseByDefault(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
    }

    public function testSetIsVerified(): void
    {
        $user = new User();
        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testVerificationTokenNullByDefault(): void
    {
        $user = new User();
        $this->assertNull($user->getVerificationToken());
    }

    public function testSetVerificationToken(): void
    {
        $user = new User();
        $user->setVerificationToken('abc-123');
        $this->assertSame('abc-123', $user->getVerificationToken());
        $user->setVerificationToken(null);
        $this->assertNull($user->getVerificationToken());
    }
}
