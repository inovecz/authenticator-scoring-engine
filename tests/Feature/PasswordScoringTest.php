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
    public function password_complexity(): void
    {
        $testTable = [
            0 => ['M-h9tf)KfR', 'X5CM9xno=p', 'um9y]Ew[S~', 'z~kS_o5yTM'],
            5 => ['5mMb0nnVKI', 'x;]^}owgJ=', 'S+42I{!}B[', '7#[,i[04fi'],
            10 => ['MmPmdoIQws', 'A5DYZ32KIR', '{L(Z~~{-=E', 'slvtzo86yi', 'e%vgxk[u_o', '&9%0262[=%'],
            15 => ['QXIVMNUJMK', 'wgtvapzrsu', '6169083839', '(@~#!{&={-'],
            20 => ['']
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $score = self::callMethod($this->scorePasswordService, 'scoreComplexity', [$password]);
                $this->assertEquals($expectedScore, $score);
            }
        }
    }

    /** @test */
    public function password_score(): void
    {
        $testTable = [
            0 => ['&yuys!NvwyM1%sAj2Ed4'],
            5 => ['dx2UIE0u09qw777lhZPwYuxcGd'],
            9 => ['fv5~65sdSDF'],
            10 => ['Taumatawhakatangihangakoauauotamateaturipukakapikimaungahoronukupokaiwhenuakitanatahu'],
            15 => ['fv565sdSDF'],
            16 => ['OlPHHIdwMUWpiS'],
            29 => ['PolniyPizdec0211'],
            36 => ['Password1'],
            38 => ['q1w2e3r4t5y6', '123qweasdzxc'],
            41 => ['teddyBear'],
            47 => ['00000000', '!@#$%^&*'],
            49 => ['123456'],
            60 => [''],
        ];
        foreach ($testTable as $expectedScore => $passwords) {
            foreach ($passwords as $password) {
                $result = self::callMethod($this->scorePasswordService, 'scorePassword', [$password]);
                $this->assertEquals($expectedScore, $result['score']);
            }
        }
    }
}
