<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\AlertasService;

class ApiController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function clientes(): void
    {
        $this->json($this->query("SELECT id, nome, cpf_cnpj, whatsapp, cidade, estado FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 200"));
    }

    public function leads(): void
    {
        $this->json($this->query("SELECT id, nome, empresa, estagio, valor_estimado, prioridade, temperatura FROM leads ORDER BY created_at DESC LIMIT 200"));
    }

    public function dashboard(): void
    {
        $dados = [
            'clientes' => $this->count("SELECT COUNT(*) FROM clientes WHERE status = 'ativo'"),
            'leads' => $this->count("SELECT COUNT(*) FROM leads"),
            'usuarios' => $this->count("SELECT COUNT(*) FROM usuarios WHERE ativo = 1"),
        ];
        $this->json($dados);
    }

    public function notificacoesCount(): void
    {
        $resumo = (new AlertasService())->resumo();
        $this->json([
            'count' => $resumo['total'],
            'criticos' => $resumo['criticos'],
            'atencao' => $resumo['atencao'],
        ]);
    }

    private function query(string $sql): array
    {
        try {
            return db()->query($sql)->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function count(string $sql): int
    {
        try {
            return (int)db()->query($sql)->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
