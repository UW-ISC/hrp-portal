<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Core;

class Anonymizer
{
    /** @var string[] */
    private static $sensitiveKeys = ['site_url', 'home_url', 'admin_email', 'user_email', 'email', 'url'];
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function anonymize(array $payload) : array
    {
        $payload['site_id'] = $this->siteId();
        $payload = $this->stripSensitiveKeys($payload);
        return $payload;
    }
    public function siteId() : string
    {
        return \hash('sha256', $this->normalizeSiteUrl(\site_url()));
    }
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function stripSensitiveKeys(array $data) : array
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $data[$key] = $this->stripSensitiveKeys($value);
                continue;
            }
            if (\in_array(\strtolower((string) $key), self::$sensitiveKeys, \true)) {
                unset($data[$key]);
            }
        }
        return $data;
    }
    private function normalizeSiteUrl(string $url) : string
    {
        if (\function_exists('untrailingslashit')) {
            return \untrailingslashit(\strtolower($url));
        }
        return \rtrim(\strtolower($url), '/');
    }
}
