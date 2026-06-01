<?php

session_start();

class App {
    private ?array $config = null;

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array {
        if ($this->config)
            return $this->config;

        $this->config = parse_ini_file(__DIR__ . '/config.ini');
        if (!$this->config)
            die('Cannot load configuration');
        return $this->config;
    }

    /**
     * @return mysqli
     */
    public function getConnection(): mysqli {
        $cfg = $this->getConfig();
        $conn = new mysqli();
        $success = $conn->connect(
            $cfg['DB_HOST'],
            $cfg['DB_USER'],
            $cfg['DB_PASSWORD'],
            $cfg['DB_DATABASE'],
            $cfg['DB_PORT']);
        
        if (!$success)
            die('Cannot connect to db');
        return $conn;
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getUserInfo(): array|null {
        if (!isset($_SESSION['userId']) || !isset($_SESSION['isAdmin']))
            return null;

        return [
            'userId' => $_SESSION['userId'],
            'isAdmin' => $_SESSION['isAdmin']
        ];
    }
}

class View {
    /**
     * @return void
     */
    public function flash(?string $message = null): void {
        if ($message) {
            $_SESSION['flash'] = $message;
        } else {
            if (isset($_SESSION['flash'])) { ?>
                <div class="text-danger"><?= $_SESSION['flash'] ?></div>
            <?php }
            unset($_SESSION['flash']);
        }
    }

    /**
     * @return void
     */
    public function reloadWithFlash(string $message, string $location): void {
        $this->flash($message);
        header("Location: $location");
        exit();
    }
}
