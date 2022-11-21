<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ScoreEntityService;

class EntityScoringTest extends TestCase
{
    protected ScoreEntityService $scoreEntityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoreEntityService = new ScoreEntityService();
    }
}
