<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ScorePasswordService;

class PasswordScoringTest extends TestCase
{
    protected ScorePasswordService $scorePasswordService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorePasswordService = new ScorePasswordService();
        $this->setSettings();
    }

    /** @test */
    public function password_leaks(): void
    {
        $testTable = [
            0 => ['9%0ATWr!Z@77tFwk', 'dXW0l*Rq0hLL4DD$', 'P32mr$fg3KmaOxj%', 'f4JC409#Z#tmEd8n', '5UBqXSPY7c2m0%*i'],
            20 => ['123456789', 'password', 'qwe123RTY', '00000000', 'iloveyou'],
        ];

        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreLeaks', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_length(): void
    {
        $password = '012345678901234567890123456789';
        $testTable = [];
        for ($i = 0, $iMax = strlen($password); $i < $iMax; $i++) {
            $testTable[max(20 - ($iMax - $i), 0)][] = substr($password, 0, $iMax - $i);
        }
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreLength', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_complexity_numbers(): void
    {
        $testTable = [
            0 => ['M-h9tf)KfR', '6169083839', 'slvtzo86yi', 'A5DYZ32KIR'],
            20 => ['QXIVMNUJMK', 'wgtvapzrsu', '(@~#!{&={-', 'MmPmdoIQws', ''],
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreComplexityNumbers', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_complexity_letters(): void
    {
        $testTable = [
            0 => ['M-h9tf)KfR', 'QXIVMNUJMK', 'MmPmdoIQws', 'slvtzo86yi', 'A5DYZ32KIR', 'slvtzo86yi'],
            20 => ['6169083839', '(@~#!{&={-', ''],
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreComplexityLetters', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_complexity_mixed_case(): void
    {
        $testTable = [
            0 => ['M-h9tf)KfR', 'MmPmdoIQws', '5mMb0nnVKI'],
            20 => ['QXIVMNUJMK', 'wgtvapzrsu', '6169083839', '(@~#!{&={-', 'A5DYZ32KIR', 'slvtzo86yi', ''],
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreComplexityMixedCase', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_complexity_symbols(): void
    {
        $testTable = [
            0 => ['M-h9tf)KfR', 'x;]^}owgJ==p', '{L(Z~~{-=E', '(@~#!{&={-'],
            20 => ['5mMb0nnVKI', 'A5DYZ32KIR', 'MmPmdoIQws', '6169083839', 'QXIVMNUJMK', 'wgtvapzrsu', ''],
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreComplexitySymbols', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_score(): void
    {
        $testTable = [
            0 => ['&yuys!NvwyM1%sAj2Ed4'],
            9 => ['fv5~65sdSDF'],
            20 => ['dx2UIE0u09qw777lhZPwYuxcGd'],
            30 => ['fv565sdSDF'],
            40 => ['Taumatawhakatangihangakoauauotamateaturipukakapikimaungahoronukupokaiwhenuakitanatahu'],
            44 => ['PolniyPizdec0211'],
            46 => ['OlPHHIdwMUWpiS'],
            51 => ['Password1'],
            68 => ['q1w2e3r4t5y6', '123qweasdzxc'],
            71 => ['teddyBear'],
            92 => ['00000000', '!@#$%^&*'],
            94 => ['123456'],
            120 => [''],
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $result = self::callMethod($this->scorePasswordService, 'scorePassword', [$password]);
                $this->assertEquals($expectedScore, $result['score']);
            }
        }
    }

    private function setSettings(): void
    {
        $settingsJson = '{"scoring":{"password":{"leaks":true,"length":true,"complexity":{"symbols":true,"mixed_case":true,"letters":true,"numbers":true}},"entity":{"device":true,"geodata":true,"disposable_email":true,"leaks":{"phone":true,"email":true}}}}';
        $settings = json_decode($settingsJson, true);
        setting($settings)->save();
    }
}
