<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Validation\NotPwnedVerifier;

class ScorePasswordService extends ScoreService
{
    public function scorePassword(string $password): array
    {
        $passwordScore = 0;

        $leaksScore = $this->scoreLeaks($password);
        $passwordScore += $leaksScore;

        $lengthScore = $this->scoreLength($password);
        $passwordScore += $lengthScore;

        $complexityScore = $this->scoreComplexity($password);
        $passwordScore += $complexityScore;

        return [
            'leaks' => $leaksScore,
            'length' => $lengthScore,
            'complexity' => $complexityScore,
            'score' => $passwordScore,
        ];
    }

    /**
     * @maxMethodScore 20
     * @return int scoreLeaked (Not leaked = 0, Leaked = 20)
     */
    private function scoreLeaks(string $password, int $allowedLeaks = 0): int
    {
        if (!setting('scoring.password.leaks')) {
            return 0;
        }

        $maxLeaksScore = $this->getMethodMaxScore(__FUNCTION__);
        return (new NotPwnedVerifier(new \Illuminate\Http\Client\Factory()))->verify([
            'value' => $password,
            'threshold' => $allowedLeaks,
        ]) ? 0 : $maxLeaksScore;
    }

    /**
     * @maxMethodScore 20
     * @return int scoreLength (Best = 0, Worst = 20)
     */
    private function scoreLength(string $password): int
    {
        if (!setting('scoring.password.length')) {
            return 0;
        }

        $maxLengthScore = $this->getMethodMaxScore(__FUNCTION__);
        $length = mb_strlen($password);
        return $length >= $maxLengthScore ? 0 : $maxLengthScore - $length;
    }

    /**
     * @maxMethodScore 20
     * @return int scoreComplexity (Best = 0, Worst = 20)
     */
    private function scoreComplexity(string $password): int
    {
        if (!setting('scoring.password.complexity')) {
            return 0;
        }

        $maxComplexityScore = $this->getMethodMaxScore(__FUNCTION__);
        $tests = [
            'has_numbers' => '/\pN/u',
            'has_letters' => '/\pL/u',
            'has_mixed_case' => '/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u',
            'has_symbols' => '/\p{Z}|\p{S}|\p{P}/u'
        ];

        $passed = 0;
        foreach ($tests as $dummy => $regex) {
            $passed += preg_match($regex, $password) ? 1 : 0;
        }

        return (int) $maxComplexityScore - ($passed) * ($maxComplexityScore / count($tests));
    }
}
