<?php
declare(strict_types=1);

class AuthService
{
    private PDO $db;
    private ?array $currentUser = null;
    private ?string $currentSessionSelector = null;
    private string $sessionCookie = 'auth_session';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /* ==========================
       SESSION CORE (auth_sessions_v2)
    ========================== */

    public function bootstrapSession(): void
    {
        $this->currentUser = null;
        $this->currentSessionSelector = null;

        $cookie = $_COOKIE[$this->sessionCookie] ?? '';
        if ($cookie === '') {
            return;
        }

        $parts = explode(':', $cookie, 2);
        if (count($parts) !== 2) {
            $this->clearAuthCookie();
            return;
        }

        [$selector, $validator] = $parts;
        if ($selector === '' || $validator === '') {
            $this->clearAuthCookie();
            return;
        }

        $session = $this->db->prepare(
            "SELECT s.user_id, s.validator_hash, s.expires_at, s.revoked
             FROM auth_sessions_v2 s
             WHERE s.selector = :selector
             LIMIT 1"
        );
        $session->execute(['selector' => $selector]);
        $row = $session->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->clearAuthCookie();
            return;
        }

        if ((int)$row['revoked'] === 1 || strtotime($row['expires_at']) <= time()) {
            $this->clearAuthCookie();
            return;
        }

        $validatorHash = hash('sha256', $validator);
        if (!hash_equals($row['validator_hash'], $validatorHash)) {
            $this->clearAuthCookie();
            return;
        }

        $user = $this->fetchUserById((int)$row['user_id']);
        if (!$user || $user['status'] !== 'active') {
            $this->clearAuthCookie();
            return;
        }

        $this->currentUser = $user;
        $this->currentSessionSelector = $selector;
        $_SESSION['auth_selector'] = $selector;

        $this->db->prepare(
            "UPDATE auth_sessions_v2
             SET last_seen = NOW()
             WHERE selector = :selector"
        )->execute(['selector' => $selector]);
    }

    public function isLoggedIn(): bool
    {
        return $this->currentUser !== null;
    }

    public function getUserId(): ?int
    {
        return $this->currentUser ? (int)$this->currentUser['id'] : null;
    }

    public function getUserEmail(): ?string
    {
        return $this->currentUser['email'] ?? null;
    }

    public function getUsername(): ?string
    {
        return $this->currentUser['username'] ?? null;
    }

    public function isEmailVerified(): bool
    {
        if (!$this->currentUser) {
            return false;
        }
        return !empty($this->currentUser['email_verified_at']);
    }

    public function getVerificationDaysRemaining(): ?int
    {
        if (!$this->currentUser || $this->isEmailVerified()) {
            return null;
        }

        $createdAt = strtotime($this->currentUser['created_at']);
        $daysPassed = (int)floor((time() - $createdAt) / (60 * 60 * 24));
        $remaining = 7 - $daysPassed;
        return max(0, $remaining);
    }

    public function logout(): void
    {
        $selector = $this->currentSessionSelector ?? ($_SESSION['auth_selector'] ?? null);
        if ($selector) {
            $this->db->prepare(
                "UPDATE auth_sessions_v2
                 SET revoked = 1
                 WHERE selector = :selector"
            )->execute(['selector' => $selector]);
        }
        $this->clearAuthCookie();
        unset($_SESSION['auth_selector']);
        $this->currentUser = null;
        $this->currentSessionSelector = null;
    }

    /* ==========================
       LOGIN / REGISTER
    ========================== */

    public function login(string $email, string $password): bool
    {
        $q = $this->db->prepare(
            "SELECT id,email,username,password_hash,status,created_at,email_verified_at
             FROM users WHERE email=:email LIMIT 1"
        );
        $q->execute(['email'=>$email]);
        $u = $q->fetch(PDO::FETCH_ASSOC);

        if (!$u || $u['status'] !== 'active') return false;
        if (!password_verify($password, $u['password_hash'])) return false;

        $this->createAuthSession((int)$u['id']);
        $this->currentUser = $u;
        return true;
    }

    public function register(string $email, string $password, string $registerCode): bool
    {
        if (!$this->isRegisterCodeValid($registerCode)) {
            return false;
        }

        $username = strstr($email,'@',true);

        $check = $this->db->prepare(
            "SELECT id FROM users WHERE email=:e OR username=:u"
        );
        $check->execute(['e'=>$email,'u'=>$username]);
        if ($check->fetch()) return false;

        $created = $this->db->prepare(
            "INSERT INTO users (username,email,password_hash,status,created_at,email_verified_at)
             VALUES (:u,:e,:h,'active',NOW(),NULL)"
        )->execute([
            'u'=>$username,
            'e'=>$email,
            'h'=>password_hash($password,PASSWORD_DEFAULT)
        ]);

        if (!$created) {
            return false;
        }

        $userId = (int)$this->db->lastInsertId();
        $this->consumeRegisterCode($registerCode, $userId);
        $this->createEmailVerification($userId, $email);
        return true;
    }

    /* ==========================
       USERNAME
    ========================== */

    public function needsUsernameSetup(): bool
    {
        if (!$this->isLoggedIn()) return false;
        $email = $this->currentUser['email'] ?? '';
        return ($this->currentUser['username'] ?? '') === strstr($email,'@',true);
    }

    public function updateUsername(string $name): bool
    {
        if (!$this->isLoggedIn()) return false;

        $default = strstr($this->currentUser['email'] ?? '', '@', true);
        if ($name === $default) return false;
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/',$name)) return false;

        $exists = $this->db->prepare(
            "SELECT id FROM users WHERE username=:u"
        );
        $exists->execute(['u'=>$name]);
        if ($exists->fetch()) return false;

        $this->db->prepare(
            "UPDATE users SET username=:u WHERE id=:id"
        )->execute([
            'u'=>$name,
            'id'=>$this->currentUser['id']
        ]);

        $this->currentUser['username'] = $name;
        return true;
    }

    /* ==========================
       AUTH SESSION HANDLING
    ========================== */

    private function createAuthSession(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));

        $this->db->prepare(
            "INSERT INTO auth_sessions_v2
             (user_id, selector, validator_hash, user_agent, ip_address, created_at, last_seen, expires_at, revoked)
             VALUES (:uid, :selector, :hash, :agent, :ip, NOW(), NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 0)"
        )->execute([
            'uid'=>$userId,
            'selector'=>$selector,
            'hash'=>hash('sha256', $validator),
            'agent'=>$_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip'=>$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);

        $this->setAuthCookie($selector . ':' . $validator);
        $_SESSION['auth_selector'] = $selector;
        $this->currentSessionSelector = $selector;
    }

    private function setAuthCookie(string $value): void
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie($this->sessionCookie, $value, [
            'expires' => time() + (7 * 24 * 60 * 60),
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    private function clearAuthCookie(): void
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie($this->sessionCookie, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    private function fetchUserById(int $userId): ?array
    {
        $q = $this->db->prepare(
            "SELECT id, email, username, status, created_at, email_verified_at
             FROM users WHERE id = :id LIMIT 1"
        );
        $q->execute(['id' => $userId]);
        $user = $q->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function isRegisterCodeValid(string $code): bool
    {
        $q = $this->db->prepare(
            "SELECT code, is_used, expires_at
             FROM register_codes
             WHERE code = :code
             LIMIT 1"
        );
        $q->execute(['code' => $code]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['is_used'] === 1) {
            return false;
        }
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) <= time()) {
            return false;
        }
        return true;
    }

    private function consumeRegisterCode(string $code, int $userId): void
    {
        $this->db->prepare(
            "UPDATE register_codes
             SET is_used = 1, used_by = :uid, used_at = NOW()
             WHERE code = :code"
        )->execute([
            'uid' => $userId,
            'code' => $code
        ]);
    }

    public function requestEmailVerification(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        if ($this->isEmailVerified()) {
            return true;
        }

        $email = $this->currentUser['email'] ?? '';
        $this->createEmailVerification((int)$this->currentUser['id'], $email);
        return true;
    }

    public function verifyEmailToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $hash = hash('sha256', $token);
        $q = $this->db->prepare(
            "SELECT id, user_id
             FROM email_verifications
             WHERE token_hash = :hash
             AND used_at IS NULL
             AND expires_at > NOW()
             LIMIT 1"
        );
        $q->execute(['hash' => $hash]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        $this->db->prepare(
            "UPDATE users
             SET email_verified_at = NOW()
             WHERE id = :uid"
        )->execute(['uid' => $row['user_id']]);

        $this->db->prepare(
            "UPDATE email_verifications
             SET used_at = NOW()
             WHERE id = :id"
        )->execute(['id' => $row['id']]);

        if ($this->currentUser && (int)$this->currentUser['id'] === (int)$row['user_id']) {
            $this->currentUser['email_verified_at'] = date('Y-m-d H:i:s');
        }

        return true;
    }

    public function cleanupUnverifiedAccounts(): void
    {
        $q = $this->db->query(
            "SELECT id
             FROM users
             WHERE email_verified_at IS NULL
             AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $ids = $q->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $userId) {
            $this->deleteUserData((int)$userId);
        }
    }

    private function deleteUserData(int $userId): void
    {
        $imageStmt = $this->db->prepare(
            "SELECT file_path FROM profile_images WHERE user_id = :uid"
        );
        $imageStmt->execute(['uid' => $userId]);
        $paths = $imageStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($paths as $path) {
            $fullPath = dirname(__DIR__) . '/' . ltrim($path, '/');
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $tables = [
            ['auth_sessions_v2', 'user_id'],
            ['email_verifications', 'user_id'],
            ['mau_link_tokens', 'user_id'],
            ['profile_boxes', 'user_id'],
            ['profile_images', 'user_id'],
            ['rpgm_character', 'owner_user_id'],
            ['rpgm_post', 'user_id'],
            ['user_achievements', 'user_id'],
            ['user_remember_tokens', 'user_id'],
            ['user_roles', 'user_id'],
            ['user_strikes', 'user_id'],
            ['user_warnings', 'user_id'],
        ];

        foreach ($tables as [$table, $column]) {
            $this->db->prepare(
                "DELETE FROM {$table} WHERE {$column} = :uid"
            )->execute(['uid' => $userId]);
        }

        $this->db->prepare(
            "DELETE FROM users WHERE id = :uid"
        )->execute(['uid' => $userId]);
    }

    private function createEmailVerification(int $userId, string $email): void
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $this->db->prepare(
            "INSERT INTO email_verifications
             (user_id, token_hash, created_at, expires_at)
             VALUES (:uid, :hash, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))"
        )->execute([
            'uid' => $userId,
            'hash' => $hash
        ]);

        $subject = 'MAU · E-Mail bestätigen';
        $link = sprintf('%s://%s/verify-email.php?token=%s',
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
            $_SERVER['HTTP_HOST'] ?? 'localhost',
            $token
        );
        $message = "Bitte bestätige deine E-Mail über folgenden Link:\n\n{$link}\n\nDer Link ist 7 Tage gültig.";
        @mail($email, $subject, $message);
    }

    /* ==========================
       ACHIEVEMENTS
    ========================== */

    public function getUserAchievements(): array
    {
        if (!$this->isLoggedIn()) {
            return [];
        }
        $q = $this->db->prepare(
            "SELECT a.id,a.name,a.description,ua.unlocked_at
             FROM achievements a
             JOIN user_achievements ua ON ua.achievement_id=a.id
             WHERE ua.user_id=:uid
             ORDER BY ua.unlocked_at DESC"
        );
        $q->execute(['uid'=>$this->currentUser['id']]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
}
