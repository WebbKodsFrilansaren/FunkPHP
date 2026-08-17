<?php
// src/funkphp/config/classes.php - FunkPHP | FunkCLI recreated it 2026-08-04 11:11:04

/**
 * ------------------------------------------------------
 * FUNKPHP AUTOMATICALLY GENERATED/RECREATED DEFAULT FILE
 * ------------------------------------------------------
 */
// FunkPHP - User-defined Classes Available either globally OR
// via `funkphp\classes` Namespace Scoping. Name them anything
// as long as they do not conflict with "Funk|" class names
// that begin with "Funk" or any other class(es) from
// the `/src/funkphp/vendor` (Composer Classes).
//
// Also, you may remove the `namespace funkphp\classes;` and your classes
// will then be put in the Global Namespace Scope during compiling/running.
//
// Besides all that above, name your class(es) anything you want!

namespace funkphp\classes;

/**
 * Data Transfer Object with constructor property promotion and default values.
 */
class UserDTO
{
    public function __construct(
        public readonly int $id,
        public string $username,
        public string $email,
        public array $roles = ['user'],
        public bool $isActive = true
    ) {}

    public function hasRole(string $role): bool
    {
        return in_array(strtolower($role), array_map('strtolower', $this->roles), true);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->roles,
            'is_active' => $this->isActive,
        ];
    }
}

/**
 * Utility Service featuring static methods, private properties, and conditional logic.
 */
class SecurityUtils
{
    private static string $algo = 'sha256';
    private const PEPPER = 'fphp_secret_key_2026';
    public static function hashPassword(string $password): string
    {
        $salted = $password . self::PEPPER;
        return password_hash($salted, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 1,
        ]);
    }
    public static function generateNonce(int $length = 32): string
    {
        if ($length < 16) {
            $length = 16;
        }
        return bin2hex(random_bytes((int) ($length / 2)));
    }
    public function verifyToken(?string $token, string $hash): bool
    {
        if (null === $token || '' === trim($token)) {
            return false;
        }
        return hash_equals(
            hash(self::$algo, $token . self::PEPPER),
            $hash
        );
    }
}
/**
 * Complex State Container testing method chaining support, variadic arguments, and array manipulation.
 */
class ResponsePipeline
{
    protected array $headers = [];
    protected array $payload = [];
    protected int $statusCode = 200;

    public function setStatus(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    public function withHeaders(array ...$headerPairs): self
    {
        foreach ($headerPairs as $pair) {
            if (isset($pair['key'], $pair['value'])) {
                $this->headers[strtolower($pair['key'])] = $pair['value'];
            }
        }
        return $this;
    }
    public function buildResponse(string $format = 'json'): array
    {
        $formatted = [
            'status' => $this->statusCode,
            'headers' => $this->headers,
            'timestamp' => time(),
        ];
        return match (strtolower($format)) {
            'json' => array_merge($formatted, ['data' => $this->payload]),
            'xml'  => array_merge($formatted, ['xml_data' => $this->payload]),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }
}
