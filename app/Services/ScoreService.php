<?php

declare(strict_types=1);

namespace App\Services;

class ScoreService
{
    public function getMethodMaxScore(string $method): ?int
    {
        $reflection = new \ReflectionClass($this);
        $docBlock = $reflection->getMethod($method)->getDocComment();
        if ($docBlock) {
            preg_match_all('/@settings (?<settings>.+)/m', $docBlock, $settings, PREG_SET_ORDER, 0);
            if (isset($settings[0]['settings'])) {
                $settings = explode(',', $settings[0]['settings']);
                foreach ($settings as $setting) {
                    if (!setting($setting) || setting($setting) === false) {
                        return 0;
                    }
                }
            }

            preg_match_all('/@maxMethodScore (?<maxMethodScore>\d+)/m', $docBlock, $maxScore, PREG_SET_ORDER, 0);
            return isset($maxScore[0]['maxMethodScore']) ? (int) $maxScore[0]['maxMethodScore'] : null;
        }
        return null;
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
