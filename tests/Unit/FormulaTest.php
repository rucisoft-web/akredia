<?php

namespace Tests\Unit;

use App\Support\Formula;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FormulaTest extends TestCase
{
    public function test_menghitung_persentase_dan_fungsi(): void
    {
        $this->assertSame(25.0, Formula::evaluate('pct(a, b)', ['a' => 10, 'b' => 40]));
        $this->assertSame(0.0, Formula::evaluate('pct(a, b)', ['a' => 10, 'b' => 0]));
        $this->assertSame(20.0, Formula::evaluate('decline(lama, baru)', ['lama' => 100, 'baru' => 80]));
        $this->assertSame(0.0, Formula::evaluate('decline(lama, baru)', ['lama' => 80, 'baru' => 100]));
        $this->assertSame(15.0, Formula::evaluate('avg(10, 20)', []));
        $this->assertSame(7.0, Formula::evaluate('(a + b) * 2 - 1', ['a' => 1, 'b' => 3]));
    }

    public function test_rumus_ditolak_bila_tidak_aman(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Formula::evaluate('system("ls")', []);
    }

    public function test_validasi_variabel_tidak_dikenal(): void
    {
        $this->assertNull(Formula::validate('pct(dosen_doktor, dosen_tetap)', ['dosen_doktor', 'dosen_tetap']));
        $this->assertNotNull(Formula::validate('pct(x, dosen_tetap)', ['dosen_tetap']));
        $this->assertSame(['dosen_doktor', 'dosen_tetap'], Formula::variables('pct(dosen_doktor, dosen_tetap)'));
    }
}
