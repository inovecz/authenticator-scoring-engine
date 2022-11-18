<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Enums\BlacklistTypeEnum;
use App\Services\BlacklistService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BlacklistCheckTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    protected BlacklistService $blacklistService;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => 'BlacklistTestSeeder']);
        $this->blacklistService = new BlacklistService();
    }

    // <editor-fold desc="Region: TEST DOMAIN">

    /** @test */
    public function not_blacklisted_domain_not_found_in_blacklist(): void
    {
        $notBlacklistedDomains = ['gmail.com', 'seznam.cz', 'outlook.com'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($notBlacklistedDomains as $domain) {
            $this->assertNull($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$domain, BlacklistTypeEnum::DOMAIN]));
        }
    }

    /** @test */
    public function inactive_domain_not_found_in_blacklist(): void
    {
        $blacklistedDeactivatedDomains = ['inactive.net', 'inactive.org'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($blacklistedDeactivatedDomains as $domain) {
            $this->assertNull($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$domain, BlacklistTypeEnum::DOMAIN]));
        }
    }

    /** @test */
    public function active_domain_found_in_blacklist(): void
    {
        $blacklistedDomains = ['blacklisted.cz', 'blacklisted.com'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($blacklistedDomains as $domain) {
            $this->assertIsInt($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$domain, BlacklistTypeEnum::DOMAIN]));
        }
    }
    // </editor-fold desc="Region: TEST DOMAIN">

    // <editor-fold desc="Region: TEST IP">
    /** @test */
    public function not_blacklisted_ip_not_found_in_blacklist(): void
    {
        $notBlacklistedIps = ['123.123.123.123', '127.0.0.1', '192.168.0.1'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($notBlacklistedIps as $ip) {
            $this->assertNull($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$ip, BlacklistTypeEnum::IP]));
        }
    }

    /** @test */
    public function inactive_ip_not_found_in_blacklist(): void
    {
        /** Deactivated IP array:    ↓ standalone      ↓ start range  ↓ mid range        ↓ end range */
        $blacklistedDeactivedIps = ['255.255.255.255', '200.200.0.0', '200.200.125.125', '200.200.255.255'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($blacklistedDeactivedIps as $ip) {
            $this->assertNull($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$ip, BlacklistTypeEnum::IP]));
        }
    }

    /** @test */
    public function active_ip_found_in_blacklist(): void
    {
        /** Active IP array ↓ standalone      ↓ start range    ↓ mid range        ↓ end range */
        $blacklistedIps = ['50.100.150.200', '100.100.100.0', '100.100.100.125', '100.100.100.255'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($blacklistedIps as $ip) {
            $this->assertIsInt($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$ip, BlacklistTypeEnum::IP]));
        }
    }
    // </editor-fold desc="Region: TEST IP">

    // <editor-fold desc="Region: TEST EMAIL">
    /** @test */
    public function not_blacklisted_email_not_found_in_blacklist(): void
    {
        $notBlacklistedEmails = ['not.blacklisted@gmail.com', 'not.blacklisted@seznam.cz', 'not.blacklisted@outlook.com'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($notBlacklistedEmails as $email) {
            $this->assertNull($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$email, BlacklistTypeEnum::EMAIL]));
        }
    }

    /** @test */
    public function inactive_email_not_found_in_blacklist(): void
    {
        $blacklistedDeactivatedEmails = ['blacklisted@inactive.net', 'blacklisted@inactive.org'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($blacklistedDeactivatedEmails as $email) {
            $this->assertNull($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$email, BlacklistTypeEnum::EMAIL]));
        }
    }

    /** @test */
    public function active_email_found_in_blacklist(): void
    {
        $blacklistedEmails = ['blacklisted@active.cz', 'blacklisted@active.com'];
        $reflector = new \ReflectionClass($this->blacklistService);
        $checkInBlacklistMethod = $reflector->getMethod('checkInBlacklist');
        $checkInBlacklistMethod->setAccessible(true);
        foreach ($blacklistedEmails as $email) {
            $this->assertIsInt($checkInBlacklistMethod->invokeArgs($this->blacklistService, [$email, BlacklistTypeEnum::EMAIL]));
        }
    }
    // </editor-fold desc="Region: TEST EMAIL">
}
