<?php
/**
 * Configuração do banco de dados — KROMA PRINT ERP
 * Utiliza PDO com MySQL
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'kroma_print');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe de conexão singleton com PDO
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Retorna a instância única da conexão PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
                );

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                // Em produção, logar e exibir mensagem genérica
                if (APP_ENV === 'development') {
                    die('Erro de conexão com banco de dados: ' . $e->getMessage());
                } else {
                    die('Erro interno do sistema. Tente novamente mais tarde.');
                }
            }
        }

        return self::$instance;
    }

    /**
     * Evita clonagem da instância
     */
    private function __clone() {}
}

/**
 * Função auxiliar global para obter a conexão PDO
 */
function db(): PDO
{
    return Database::getInstance();
}
