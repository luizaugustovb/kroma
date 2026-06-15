<?php
/**
 * Controlador de Instalação — KROMA PRINT ERP
 * Cria o banco de dados e popula com dados iniciais
 */

namespace App\Controllers;

class InstallController
{
    private string $lockFile;

    public function __construct()
    {
        $this->lockFile = ROOT_PATH . '/storage/installed.lock';
    }

    public function index(): void
    {
        if (file_exists($this->lockFile)) {
            $_SESSION['flash_info'] = 'Sistema já instalado. Faça login para continuar.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $this->renderizar();
    }

    public function instalar(): void
    {
        if (file_exists($this->lockFile)) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $erros   = [];
        $sucesso = false;

        try {
            // Cria banco se não existir
            $pdo = new \PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");

            // Executa o SQL de instalação
            $sql = file_get_contents(ROOT_PATH . '/database/install.sql');
            // Remove comentários de linha e divide por ponto-e-vírgula
            $sql = preg_replace('/^--.*$/m', '', $sql);
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            // Cria pastas necessárias
            $pastas = [
                ROOT_PATH . '/storage',
                ROOT_PATH . '/storage/uploads',
                ROOT_PATH . '/storage/arquivos',
                ROOT_PATH . '/logs',
                PUBLIC_PATH . '/uploads',
                PUBLIC_PATH . '/uploads/landing',
            ];
            foreach ($pastas as $pasta) {
                if (!is_dir($pasta)) {
                    mkdir($pasta, 0755, true);
                }
            }

            // Cria arquivo de bloqueio de reinstalação
            file_put_contents($this->lockFile, 'installed=' . date('Y-m-d H:i:s'));

            $sucesso = true;
        } catch (\Exception $e) {
            $erros[] = 'Erro na instalação: ' . $e->getMessage();
        }

        $this->renderizar($erros, $sucesso);
    }

    private function renderizar(array $erros = [], bool $sucesso = false): void
    {
        require APP_PATH . '/Views/install/index.php';
    }
}
