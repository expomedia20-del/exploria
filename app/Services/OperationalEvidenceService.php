<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Throwable;

class OperationalEvidenceService
{
    /** @var list<string> */
    public const REQUIRED_CHECKS = [
        'mail',
        'storage',
        'monitoring',
        'queue',
        'cache',
        'session',
        'scheduler',
    ];

    /**
     * @return array{valid: bool, status: string, checks: array<string, bool>}
     */
    public function report(string $environment): array
    {
        $checks = array_fill_keys(self::REQUIRED_CHECKS, false);
        $configuredPath = config('production_readiness.evidence_path');

        if (! is_string($configuredPath) || trim($configuredPath) === '') {
            return $this->result(false, 'missing', $checks);
        }

        $configuredPath = trim($configuredPath);

        if (! $this->isAbsolutePath($configuredPath)) {
            return $this->result(false, 'path-must-be-absolute', $checks);
        }

        $evidencePath = realpath($configuredPath);

        if ($evidencePath === false || ! is_file($evidencePath) || ! is_readable($evidencePath)) {
            return $this->result(false, 'file-unavailable', $checks);
        }

        if ($this->isInsideRepository($evidencePath)) {
            return $this->result(false, 'repository-evidence-refused', $checks);
        }

        try {
            $contents = file_get_contents($evidencePath);
            $manifest = is_string($contents)
                ? json_decode($contents, true, 32, JSON_THROW_ON_ERROR)
                : null;
        } catch (Throwable) {
            return $this->result(false, 'invalid-json', $checks);
        }

        if (! is_array($manifest) || ($manifest['environment'] ?? null) !== $environment) {
            return $this->result(false, 'environment-mismatch', $checks);
        }

        if (! $this->isFresh($manifest['verified_at'] ?? null)) {
            return $this->result(false, 'stale-or-invalid-timestamp', $checks);
        }

        $manifestChecks = $manifest['checks'] ?? null;

        if (! is_array($manifestChecks)) {
            return $this->result(false, 'checks-missing', $checks);
        }

        foreach (self::REQUIRED_CHECKS as $key) {
            $entry = $manifestChecks[$key] ?? null;

            $checks[$key] = is_array($entry)
                && ($entry['status'] ?? null) === 'pass'
                && $this->validReference($entry['reference'] ?? null);
        }

        $valid = ! in_array(false, $checks, true);

        return $this->result($valid, $valid ? 'verified' : 'incomplete', $checks);
    }

    /**
     * @param  array<string, bool>  $checks
     * @return array{valid: bool, status: string, checks: array<string, bool>}
     */
    private function result(bool $valid, string $status, array $checks): array
    {
        return compact('valid', 'status', 'checks');
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }

    private function isInsideRepository(string $path): bool
    {
        $repository = realpath(base_path());

        if ($repository === false) {
            return true;
        }

        $repository = rtrim(str_replace('\\', '/', $repository), '/').'/';
        $path = str_replace('\\', '/', $path);

        return str_starts_with(strtolower($path), strtolower($repository));
    }

    private function isFresh(mixed $verifiedAt): bool
    {
        if (! is_string($verifiedAt) || trim($verifiedAt) === '') {
            return false;
        }

        $maxAge = config('production_readiness.evidence_max_age_minutes');

        if (! is_int($maxAge) || $maxAge < 1) {
            return false;
        }

        try {
            $verifiedAt = CarbonImmutable::parse($verifiedAt);
        } catch (Throwable) {
            return false;
        }

        $now = CarbonImmutable::now();

        return $verifiedAt->lessThanOrEqualTo($now->addMinutes(5))
            && $verifiedAt->greaterThanOrEqualTo($now->subMinutes($maxAge));
    }

    private function validReference(mixed $reference): bool
    {
        if (! is_string($reference)) {
            return false;
        }

        $reference = trim($reference);

        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._:\/#-]{7,199}$/', $reference) !== 1) {
            return false;
        }

        foreach (['placeholder', 'example', 'pending', 'changeme', 'todo', 'tbd'] as $refusedValue) {
            if (str_contains(strtolower($reference), $refusedValue)) {
                return false;
            }
        }

        return true;
    }
}
