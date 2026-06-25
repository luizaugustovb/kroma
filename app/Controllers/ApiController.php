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

    public function produtoMateriais(string $id): void
    {
        try {
            $stmt = db()->prepare(
                "SELECT pm.material_id, pm.quantidade, pm.unidade, pm.observacao,
                        m.nome, m.codigo, m.unidade AS material_unidade
                 FROM produto_materiais pm
                 JOIN materiais m ON m.id = pm.material_id
                 WHERE pm.produto_id = ?
                 ORDER BY pm.id"
            );
            $stmt->execute([(int)$id]);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                $unid = $row['unidade'] ?: $row['material_unidade'] ?: 'un';
                $qtd  = rtrim(rtrim(number_format((float)$row['quantidade'], 4, ',', '.'), '0'), ',');
                $row['quantidade_formatada'] = $qtd . ' ' . $unid;
                $row['label'] = ($row['codigo'] ? $row['codigo'] . ' - ' : '') . $row['nome'];
            }
            $this->json($rows);
        } catch (\Exception $e) {
            $this->json([]);
        }
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
