<?php

namespace App\Support;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer;

/** Baca/tulis XLSX dan CSV dengan OpenSpout (PHP murni, ringan untuk hosting bersama). */
class Spreadsheet
{
    /** @return array<int, array<string, mixed>> baris pertama sebagai kunci (huruf kecil, spasi menjadi _). */
    public static function read(string $path, ?string $extension = null): array
    {
        $extension ??= strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $reader = $extension === 'csv' ? new CsvReader : new XlsxReader;
        $reader->open($path);
        $header = null;
        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $values = array_map(fn ($v) => is_string($v) ? trim($v) : ($v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v), $row->toArray());

                if ($header === null) {
                    $header = array_map(fn ($h) => str_replace([' ', '-'], '_', strtolower(trim((string) $h))), $values);

                    continue;
                }

                if (count(array_filter($values, fn ($v) => $v !== null && $v !== '')) === 0) {
                    continue;
                }

                $rows[] = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), null));
            }
            break;
        }

        $reader->close();

        return $rows;
    }

    /** @param array<string, array{0: array<int,string>, 1: iterable<array<int,mixed>>}> $sheets nama => [header, baris] */
    public static function write(string $path, array $sheets): void
    {
        $writer = new Writer;
        $writer->openToFile($path);
        $bold = (new Style)->setFontBold()->setBackgroundColor('E8EEF7');
        $first = true;

        foreach ($sheets as $name => [$header, $rows]) {
            if (! $first) {
                $writer->addNewSheetAndMakeItCurrent();
            }

            $first = false;
            $writer->getCurrentSheet()->setName(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $name), 0, 31));
            $writer->addRow(Row::fromValues($header, $bold));

            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues(array_map(fn ($v) => $v instanceof \BackedEnum ? (method_exists($v, 'getLabel') ? $v->getLabel() : $v->value) : $v, $row)));
            }
        }

        $writer->close();
    }
}
