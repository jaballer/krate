<?php
declare(strict_types=1);

namespace Krate\Core\Helpers;

use InvalidArgumentException;

class SessionHelper
{
    /**
     * Set a session value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Get and clear a session message
     *
     * @return string|null
     */
    public function getAndClearMessage(): ?string
    {
        if (isset($_SESSION['message']) && $_SESSION['message'] !== '') {
            $msg = $_SESSION['message'];
            unset($_SESSION['message']);
            return $msg;
        }
        return null;
    }

    /**
     * Set a session message
     *
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void
    {
        $_SESSION['message'] = $message;
    }

    /**
     * Get or create the CSRF token for the current session
     *
     * @return string
     */
    public function getCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION['csrf_token'];
    }

    /**
     * Validate an incoming CSRF token
     *
     * @param string|null $token
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateCsrfToken(?string $token): void
    {
        $expectedToken = $this->getCsrfToken();

        if (!is_string($token) || !hash_equals($expectedToken, $token)) {
            throw new InvalidArgumentException('Invalid CSRF token.');
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public function getCurrentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Clear all session data
     *
     * @return void
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Check if a session variable exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
