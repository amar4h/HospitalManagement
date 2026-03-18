<?php
/**
 * Authentication Class with JWT
 */
class Auth {
    /**
     * Generate JWT token
     */
    public static function generateToken($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'sub' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'name' => $user['name'],
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ]);

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * Validate JWT token
     */
    public static function validateToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        // Check expiration
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Login user
     */
    public static function login($username, $password) {
        $storage = new Storage();
        $user = $storage->getBy('users', 'username', $username);

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        if ($user['status'] !== 'active') {
            return null;
        }

        // Remove password from user data
        unset($user['password']);

        return [
            'token' => self::generateToken($user),
            'user' => $user
        ];
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
