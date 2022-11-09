<?php
declare(strict_types=1);
namespace Tests\Feature;

use App\Services\ScorePasswordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EntityScoringTest extends TestCase
{
    protected ScorePasswordService $scorePasswordService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorePasswordService = new ScorePasswordService();
    }
}
