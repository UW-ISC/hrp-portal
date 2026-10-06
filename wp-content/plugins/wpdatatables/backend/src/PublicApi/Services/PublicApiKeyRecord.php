<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Services;

/**
 * Stored API key metadata (never includes plaintext).
 *
 * @package WPDataTables\PublicApi\Services
 */
class PublicApiKeyRecord
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $hash;

    /** @var string */
    private $last4;

    /** @var string[] */
    private $scopes;

    /** @var int|null */
    private $expiresAt;

    /** @var int */
    private $createdAt;

    /**
     * @param string[] $scopes
     */
    public function __construct(
        string $id,
        string $label,
        string $hash,
        string $last4,
        array $scopes,
        ?int $expiresAt,
        int $createdAt
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->hash = $hash;
        $this->last4 = $last4;
        $this->scopes = $scopes;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        return new self(
            (string) ($data['id'] ?? ''),
            (string) ($data['label'] ?? ''),
            (string) ($data['hash'] ?? ''),
            (string) ($data['last4'] ?? ''),
            is_array($data['scopes'] ?? null) ? array_values($data['scopes']) : [],
            isset($data['expires_at']) ? (int) $data['expires_at'] : null,
            (int) ($data['created_at'] ?? 0)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorageArray()
    {
        return [
            'id'         => $this->id,
            'label'      => $this->label,
            'hash'       => $this->hash,
            'last4'      => $this->last4,
            'scopes'     => $this->scopes,
            'expires_at' => $this->expiresAt,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray()
    {
        return [
            'id'         => $this->id,
            'label'      => $this->label,
            'last4'      => $this->last4,
            'scopes'     => $this->scopes,
            'expires_at' => $this->expiresAt,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiresAt !== null && $this->expiresAt < time();
    }
}
