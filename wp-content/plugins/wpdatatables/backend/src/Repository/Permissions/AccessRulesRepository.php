<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Permissions;

/**
 * Persists wpDataTables access rules in a dedicated option.
 *
 * @package WPDataTables\Repository\Permissions
 */
class AccessRulesRepository
{
    public const OPTION_KEY = 'wpdt_access_rules';

    /**
     * @return array{version:int, rules:list<array<string,mixed>>}
     */
    public function getPayload(): array
    {
        $raw = get_option(self::OPTION_KEY, null);
        if (!is_array($raw) || !isset($raw['rules']) || !is_array($raw['rules'])) {
            return [
                'version' => 1,
                'rules'   => [],
            ];
        }

        $version = isset($raw['version']) ? (int) $raw['version'] : 1;

        return [
            'version' => max(1, $version),
            'rules'   => self::filterRuleEntries($raw['rules']),
        ];
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return void
     */
    public function saveRules(array $rules): void
    {
        $filtered = self::filterRuleEntries($rules);

        $prev    = get_option(self::OPTION_KEY, null);
        $version = 1;
        if (is_array($prev) && array_key_exists('version', $prev)) {
            $version = max(1, (int) $prev['version']);
        }

        update_option(
            self::OPTION_KEY,
            [
                'version' => $version,
                'rules'   => array_values($filtered),
            ],
            false
        );
    }

    /**
     * @param string $resource `tables` or `charts`.
     * @return list<array<string,mixed>>
     */
    public function getRulesForResource(string $resource): array
    {
        $out = [];
        foreach ($this->getPayload()['rules'] as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if (($rule['resource'] ?? '') === $resource) {
                $out[] = $rule;
            }
        }

        return $out;
    }

    /**
     * @param mixed[] $rules
     * @return list<array<string,mixed>>
     */
    private static function filterRuleEntries(array $rules): array
    {
        return array_values(array_filter($rules, 'is_array'));
    }
}
