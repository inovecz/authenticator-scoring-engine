<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Validation\NotPwnedVerifier;

class ScorePasswordService
{
    public int $maxScore = 60;

    /** @return int scoreUncompromised (Unccmpromised = 0, Compromised = 20) */
    public function scoreUncompromised(string $password, int $allowedLeaks = 0): int
    {
        return (new NotPwnedVerifier(new \Illuminate\Http\Client\Factory()))->verify([
            'value' => $password,
            'threshold' => $allowedLeaks,
        ]) ? 0 : 20;
    }

    /** @return int scoreLength (Best = 0, Worst = 20) */
    public function scoreLength(string $password): int
    {
        $length = mb_strlen($password);
        return $length >= 20 ? 0 : 20 - $length;
    }

    /** @return int scoreComplexity (Best = 0, Worst = 20) */
    public function scoreComplexity(string $password): int
    {
        $hasNumbers = preg_match('/\pN/u', $password);
        $hasLetters = preg_match('/\pL/u', $password);
        $hasMixedCase = preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $password);
        $hasSymbols = preg_match('/\p{Z}|\p{S}|\p{P}/u', $password);
        return 20 - ($hasNumbers + $hasLetters + $hasMixedCase + $hasSymbols) * 5;
    }
}
