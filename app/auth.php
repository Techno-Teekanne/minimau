<?php
declare(strict_types=1);

class AuthService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /* ==========================
       SESSION CORE
    ========================== */

    public function startSession(int $userId, string $email, string $username): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        $_SESSION['session_expires'] = time() + (7 * 24 * 60 * 60);
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']);
    }

    public function logout(): void
    {
        setcookie('remember_me', '', time() - 3600, '/');
        session_destroy();
    }

    /* ==========================
       LOGIN / REGISTER
    ========================== */

    public function login(string $email, string $password): bool
    {
        $q = $this->db->prepare(
            "SELECT id,email,username,password_hash,status
             FROM users WHERE email=:email LIMIT 1"
        );
        $q->execute(['email'=>$email]);
        $u = $q->fetch(PDO::FETCH_ASSOC);

        if (!$u || $u['status'] !== 'active') return false;
        if (!password_verify($password, $u['password_hash'])) return false;

        $this->startSession((int)$u['id'], $u['email'], $u['username']);
        $this->createRememberToken((int)$u['id']);
        return true;
    }

    public function register(string $email, string $password): bool
    {
        $username = strstr($email,'@',true);

        $check = $this->db->prepare(
            "SELECT id FROM users WHERE email=:e OR username=:u"
        );
        $check->execute(['e'=>$email,'u'=>$username]);
        if ($check->fetch()) return false;

        return $this->db->prepare(
            "INSERT INTO users (username,email,password_hash,status,created_at)
             VALUES (:u,:e,:h,'active',NOW())"
        )->execute([
            'u'=>$username,
            'e'=>$email,
            'h'=>password_hash($password,PASSWORD_DEFAULT)
        ]);
    }

    /* ==========================
       USERNAME
    ========================== */

    public function needsUsernameSetup(): bool
    {
        if (!$this->isLoggedIn()) return false;
        return $_SESSION['username'] === strstr($_SESSION['user_email'],'@',true);
    }

    public function updateUsername(string $name): bool
    {
        if (!$this->isLoggedIn()) return false;

        $default = strstr($_SESSION['user_email'],'@',true);
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
            'id'=>$_SESSION['user_id']
        ]);

        $_SESSION['username'] = $name;
        $_SESSION['toast'] = true;
        return true;
    }

    /* ==========================
       REMEMBER ME
    ========================== */

    public function createRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));

        $this->db->prepare(
            "INSERT INTO user_remember_tokens (user_id, token, expires_at)
             VALUES (:uid,:t,DATE_ADD(NOW(), INTERVAL 30 DAY))"
        )->execute([
            'uid'=>$userId,
            't'=>hash('sha256',$token)
        ]);

        setcookie(
            'remember_me',
            $token,
            time() + (30*24*60*60),
            '/',
            '',
            true,
            true
        );
    }

    public function autoLoginFromCookie(): void
    {
        if (!empty($_SESSION['logged_in']) || empty($_COOKIE['remember_me'])) return;

        $hash = hash('sha256', $_COOKIE['remember_me']);

        $q = $this->db->prepare(
            "SELECT u.id,u.email,u.username
             FROM user_remember_tokens t
             JOIN users u ON u.id=t.user_id
             WHERE t.token=:t AND t.expires_at>NOW()
             LIMIT 1"
        );
        $q->execute(['t'=>$hash]);

        if ($u = $q->fetch(PDO::FETCH_ASSOC)) {
            $this->startSession((int)$u['id'], $u['email'], $u['username']);
        }
    }

    /* ==========================
       ACHIEVEMENTS
    ========================== */

    public function getUserAchievements(): array
    {
        $q = $this->db->prepare(
            "SELECT a.id,a.name,a.description,ua.unlocked_at
             FROM achievements a
             JOIN user_achievements ua ON ua.achievement_id=a.id
             WHERE ua.user_id=:uid
             ORDER BY ua.unlocked_at DESC"
        );
        $q->execute(['uid'=>$_SESSION['user_id']]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
}
