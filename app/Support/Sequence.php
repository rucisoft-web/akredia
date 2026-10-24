<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Nomor berurutan per institusi per tahun, misalnya AMI/2026/001. */
class Sequence
{
    public const PREFIXES = [
        'task' => 'TGS',
        'finding' => 'TMN',
        'decision' => 'KPT',
        'audit' => 'AMI',
        'document' => 'BKT',
    ];

    public static function next(string $code, int $institutionId, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        $number = DB::transaction(function () use ($code, $institutionId, $year) {
            DB::table('sequences')->insertOrIgnore(['institution_id' => $institutionId, 'code' => $code, 'year' => $year, 'last_number' => 0]);
            $row = DB::table('sequences')->where(['institution_id' => $institutionId, 'code' => $code, 'year' => $year])->lockForUpdate()->first();
            DB::table('sequences')->where('id', $row->id)->update(['last_number' => $row->last_number + 1]);

            return $row->last_number + 1;
        });

        return sprintf('%s/%d/%03d', self::PREFIXES[$code] ?? strtoupper($code), $year, $number);
    }
}
