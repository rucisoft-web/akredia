<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Evaluator rumus yang aman: hanya angka, variabel, + - * / ( ), dan fungsi yang diizinkan.
 * Tidak memakai eval(); rumus metrik dapat diubah administrator tanpa risiko eksekusi kode.
 */
class Formula
{
    public const FUNCTIONS = ['pct', 'decline', 'avg', 'min', 'max', 'abs', 'round'];

    protected array $tokens = [];

    protected int $pos = 0;

    protected array $vars = [];

    public static function evaluate(string $expression, array $variables): float
    {
        return (new self)->run($expression, $variables);
    }

    /** @return array<int, string> nama variabel yang dipakai rumus. */
    public static function variables(string $expression): array
    {
        preg_match_all('/[a-z_][a-z0-9_]*/i', $expression, $m);

        return array_values(array_unique(array_diff($m[0], self::FUNCTIONS)));
    }

    public static function validate(string $expression, array $knownVariables): ?string
    {
        try {
            $vars = array_fill_keys($knownVariables, 1);
            self::evaluate($expression, $vars);

            return null;
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    protected function run(string $expression, array $variables): float
    {
        $this->vars = $variables;
        $this->tokens = $this->tokenize($expression);
        $this->pos = 0;
        $value = $this->expr();

        if ($this->pos < count($this->tokens)) {
            throw new InvalidArgumentException('Rumus tidak valid di dekat "'.$this->tokens[$this->pos].'".');
        }

        return round($value, 4);
    }

    protected function tokenize(string $s): array
    {
        $pattern = '/\s*(\d+(?:\.\d+)?|[a-z_][a-z0-9_]*|[-+*\/(),])\s*/i';

        if (trim(preg_replace($pattern, '', $s)) !== '') {
            throw new InvalidArgumentException('Rumus mengandung karakter yang tidak diizinkan.');
        }

        preg_match_all($pattern, $s, $m);

        return $m[1];
    }

    protected function peek(): ?string
    {
        return $this->tokens[$this->pos] ?? null;
    }

    protected function take(?string $expected = null): string
    {
        $t = $this->tokens[$this->pos] ?? throw new InvalidArgumentException('Rumus berakhir terlalu cepat.');

        if ($expected !== null && $t !== $expected) {
            throw new InvalidArgumentException("Diharapkan \"{$expected}\", ditemukan \"{$t}\".");
        }

        $this->pos++;

        return $t;
    }

    protected function expr(): float
    {
        $v = $this->term();

        while (in_array($this->peek(), ['+', '-'], true)) {
            $v = $this->take() === '+' ? $v + $this->term() : $v - $this->term();
        }

        return $v;
    }

    protected function term(): float
    {
        $v = $this->factor();

        while (in_array($this->peek(), ['*', '/'], true)) {
            if ($this->take() === '*') {
                $v *= $this->factor();
            } else {
                $d = $this->factor();
                $v = $d == 0 ? 0 : $v / $d;
            }
        }

        return $v;
    }

    protected function factor(): float
    {
        $t = $this->take();

        if ($t === '-') {
            return -$this->factor();
        }

        if ($t === '(') {
            $v = $this->expr();
            $this->take(')');

            return $v;
        }

        if (is_numeric($t)) {
            return (float) $t;
        }

        if (preg_match('/^[a-z_][a-z0-9_]*$/i', $t)) {
            if ($this->peek() === '(') {
                return $this->call($t);
            }

            if (! array_key_exists($t, $this->vars)) {
                throw new InvalidArgumentException("Variabel \"{$t}\" tidak dikenal.");
            }

            return (float) $this->vars[$t];
        }

        throw new InvalidArgumentException("Token \"{$t}\" tidak diizinkan.");
    }

    protected function call(string $fn): float
    {
        if (! in_array($fn, self::FUNCTIONS, true)) {
            throw new InvalidArgumentException("Fungsi \"{$fn}\" tidak diizinkan.");
        }

        $this->take('(');
        $args = [$this->expr()];

        while ($this->peek() === ',') {
            $this->take();
            $args[] = $this->expr();
        }

        $this->take(')');

        return match ($fn) {
            'pct' => ($args[1] ?? 0) == 0 ? 0 : $args[0] / $args[1] * 100,
            'decline' => $args[0] > 0 ? max(0, ($args[0] - ($args[1] ?? 0)) / $args[0] * 100) : 0,
            'avg' => array_sum($args) / count($args),
            'min' => min($args),
            'max' => max($args),
            'abs' => abs($args[0]),
            'round' => round($args[0], (int) ($args[1] ?? 0)),
        };
    }
}
