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

        $complexityNumbersScore = $this->scoreComplexityNumbers($password);
        $passwordScore += $complexityNumbersScore;
        $complexityLettersScore = $this->scoreComplexityLetters($password);
        $passwordScore += $complexityLettersScore;
        $complexityMixedCaseScore = $this->scoreComplexityMixedCase($password);
        $passwordScore += $complexityMixedCaseScore;
        $complexitySymbolsScore = $this->scoreComplexitySymbols($password);
        $passwordScore += $complexitySymbolsScore;

        return [
            'leaks' => $leaksScore,
            'length' => $lengthScore,
            'complexity' => [
                'numbers' => $complexityNumbersScore,
                'letters' => $complexityLettersScore,
                'mixed_case' => $complexityMixedCaseScore,
                'symbols' => $complexitySymbolsScore,
            ],
            'score' => $passwordScore,
        ];
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.password.leaks
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
     * @settings scoring.password.length
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
     * @settings scoring.password.complexity.numbers
     * @return int scoreComplexityNumbers (Used = 0, Not used = 20)
     */
    private function scoreComplexityNumbers(string $password): int
    {
        $maxComplexityNumbersScore = $this->getMethodMaxScore(__FUNCTION__);
        return !preg_match('/\pN/u', $password) ? $maxComplexityNumbersScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.password.complexity.letters
     * @return int scoreComplexityLetters (Used = 0, Not used = 20)
     */
    private function scoreComplexityLetters(string $password): int
    {
        $maxComplexityLettersScore = $this->getMethodMaxScore(__FUNCTION__);
        return !preg_match('/\pL/u', $password) ? $maxComplexityLettersScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.password.complexity.mixed_case
     * @return int scoreComplexityMixedCase (Used = 0, Not used = 20)
     */
    private function scoreComplexityMixedCase(string $password): int
    {
        $maxComplexityMixedCaseScore = $this->getMethodMaxScore(__FUNCTION__);
        return !preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $password) ? $maxComplexityMixedCaseScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.password.complexity.symbols
     * @return int scoreComplexitySymbols (Used = 0, Not used = 20)
     */
    private function scoreComplexitySymbols(string $password): int
    {
        $maxComplexitySymbolsScore = $this->getMethodMaxScore(__FUNCTION__);
        return !preg_match('/\p{Z}|\p{S}|\p{P}/u', $password) ? $maxComplexitySymbolsScore : 0;
    }
}
