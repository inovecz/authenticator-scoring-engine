<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Str;

class ScoreService
{
    public function getMethodMaxScore(string $method): ?int
    {
        $reflection = new \ReflectionClass($this);
        $docBlock = $reflection->getMethod($method)->getDocComment();
        preg_match_all('/@maxMethodScore (?<maxMethodScore>\d+)/m', $docBlock, $matches, PREG_SET_ORDER, 0);
        return isset($matches[0]['maxMethodScore']) ? (int)$matches[0]['maxMethodScore'] : null;
    }

    public function getMaxScore(): ?int
    {
        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PRIVATE);
        if (count($methods) === 0) {
            return null;
        }
        $maxScore = 0;
        foreach ($methods as $method) {
            $maxScore += $this->getMethodMaxScore($method->name);
        }
        return $maxScore;
    }
}
