<?php

/**
 * Controlador de Clientes — KROMA PRINT ERP
 */

namespace App\Controllers;

use App\Services\Auth;
use App\Middleware\AuthMiddleware;

class ClienteController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        try {
            $stmt = db()->query(
                "SELECT c.*, u.nome AS vendedor_nome FROM clientes c
                 LEFT JOIN usuarios u ON u.id = c.vendedor_id
                 ORDER BY c.nome ASC"
            );
            $clientes = $stmt->fetchAll();
        } catch (\Exception $e) {
            $clientes = [];
        }

        $titulo      = 'Clientes';
        $subtitulo   = 'Carteira de clientes ativa';
        $headerActions = '<a href="' . APP_URL . '/clientes/novo" class="btn btn-primary"><i class="bi bi-person-plus"></i> Novo Cliente</a>';

        ob_start();
        require APP_PATH . '/Views/clientes/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $cliente  = [];
        $vendedores = $this->getVendedores();
        $titulo   = 'Novo Cliente';
        $breadcrumbs = [['label' => 'Clientes', 'url' => '/clientes'], ['label' => 'Novo', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/clientes/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/clientes/novo');
            exit;
        }

        $campos = $this->extrairCampos();

        if (empty($campos['nome'])) {
            $_SESSION['flash_error'] = 'Nome é obrigatório.';
            header('Location: ' . APP_URL . '/clientes/novo');
            exit;
        }

        try {
            $colunas = implode(', ', array_keys($campos));
            $placeholders = ':' . implode(', :', array_keys($campos));
            $stmt = db()->prepare("INSERT INTO clientes ($colunas, created_at) VALUES ($placeholders, NOW())");
            $stmt->execute($campos);
            $id = db()->lastInsertId();

            Auth::registrarAuditoria('clientes', 'criar', $id, null, $campos);

            // Todo cliente com e-mail recebe acesso ao portal automaticamente.
            if (!empty($campos['email'])) {
                $this->criarAcessoENotificar((int)$id, $campos);
            } elseif (!empty($campos['whatsapp'])) {
                $this->enviarBoasVindas((int)$id, $campos, null);
            }

            $_SESSION['flash_success'] = 'Cliente cadastrado com sucesso!';
            header('Location: ' . APP_URL . '/clientes/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/clientes/novo');
        }
        exit;
    }

    private function criarAcessoENotificar(int $clienteId, array $campos): void
    {
        try {
            // Buscar perfil de portal/cliente
            $perfil = db()->query("SELECT id FROM perfis WHERE nome = 'cliente' LIMIT 1")->fetch();
            if (!$perfil) {
                $perfil = db()->query("SELECT id FROM perfis ORDER BY id LIMIT 1")->fetch();
            }
            if (!$perfil) return;

            $usuarioExistente = db()->prepare(
                "SELECT u.id, p.nome AS perfil
                 FROM usuarios u
                 JOIN perfis p ON p.id = u.perfil_id
                 WHERE u.email = ?
                 LIMIT 1"
            );
            $usuarioExistente->execute([$campos['email']]);
            $usuario = $usuarioExistente->fetch();
            if ($usuario && ($usuario['perfil'] ?? '') === 'cliente') {
                db()->prepare("UPDATE usuarios SET cliente_id = ?, perfil_id = ?, ativo = 1, updated_at = NOW() WHERE id = ?")
                    ->execute([$clienteId, $perfil['id'], $usuario['id']]);
                $this->enviarBoasVindas($clienteId, $campos, null);
                return;
            }
            if ($usuario) {
                $_SESSION['flash_warning'] = 'Cliente cadastrado, mas o e-mail já pertence a um usuário interno. Crie um acesso de cliente com outro e-mail.';
                $this->enviarBoasVindas($clienteId, $campos, null);
                return;
            }

            $senha = substr(bin2hex(random_bytes(5)), 0, 8);
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

            db()->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil_id, cliente_id, ativo, created_at)
                 VALUES (?, ?, ?, ?, ?, 1, NOW())"
            )->execute([$campos['nome'], $campos['email'], $senhaHash, $perfil['id'], $clienteId]);

            $this->enviarBoasVindas($clienteId, $campos, ['email' => $campos['email'], 'senha' => $senha]);
        } catch (\Exception $e) {
            // Não bloquear o cadastro do cliente por falha no acesso
        }
    }

    private function enviarBoasVindas(int $clienteId, array $campos, ?array $credenciais): void
    {
        if (empty($campos['whatsapp'])) return;
        try {
            $empresa = db()->query("SELECT nome_fantasia, razao_social FROM empresas LIMIT 1")->fetch();
            $nomeEmpresa = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? APP_NAME;
            $portalUrl = APP_URL . '/portal';
            $loginUrl = APP_URL . '/login';

            if ($credenciais) {
                $msg = "Olá, *{$campos['nome']}*! Bem-vindo(a) à *{$nomeEmpresa}*!\n\n"
                    . "Criamos seu acesso ao portal do cliente.\n\n"
                    . "🔗 *Acesso:* {$loginUrl}\n"
                    . "📧 *Usuário:* {$credenciais['email']}\n"
                    . "🔑 *Senha:* {$credenciais['senha']}\n\n"
                    . "Recomendamos alterar sua senha no primeiro acesso.\n\n"
                    . "Qualquer dúvida, estamos à disposição!";
            } else {
                $msg = "Olá, *{$campos['nome']}*! Bem-vindo(a) à *{$nomeEmpresa}*!\n\n"
                    . "Seu cadastro foi realizado com sucesso. Em breve entraremos em contato.\n\n"
                    . "Acompanhe seus pedidos em: {$portalUrl}";
            }

            $wpp = new \App\Services\WhatsAppService();
            $wpp->enviar([
                'telefone' => $campos['whatsapp'],
                'mensagem' => $msg,
                'tipo' => 'sistema',
                'origem' => 'Boas-vindas cliente',
                'cliente_id' => $clienteId,
            ]);
        } catch (\Exception $e) {
            // Não bloquear
        }
    }

    public function ver(string $id): void
    {
        $cliente = $this->buscarPorId($id);
        if (!$cliente) {
            $_SESSION['flash_error'] = 'Cliente não encontrado.';
            header('Location: ' . APP_URL . '/clientes');
            exit;
        }

        // Leads do cliente
        try {
            $stmt = db()->prepare("SELECT * FROM leads WHERE cliente_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$id]);
            $leads = $stmt->fetchAll();
        } catch (\Exception $e) {
            $leads = [];
        }

        $titulo      = htmlspecialchars($cliente['nome']);
        $subtitulo   = 'Ficha do Cliente';
        $breadcrumbs = [['label' => 'Clientes', 'url' => '/clientes'], ['label' => $cliente['nome'], 'url' => '']];
        $headerActions = '
            <a href="' . APP_URL . '/clientes/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
            <a href="' . APP_URL . '/crm/leads/novo?cliente_id=' . $id . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Novo Lead</a>
        ';

        ob_start();
        require APP_PATH . '/Views/clientes/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $cliente = $this->buscarPorId($id);
        if (!$cliente) {
            $_SESSION['flash_error'] = 'Cliente não encontrado.';
            header('Location: ' . APP_URL . '/clientes');
            exit;
        }

        $vendedores  = $this->getVendedores();
        $titulo      = 'Editar Cliente';
        $breadcrumbs = [
            ['label' => 'Clientes', 'url' => '/clientes'],
            ['label' => $cliente['nome'], 'url' => '/clientes/' . $id],
            ['label' => 'Editar', 'url' => ''],
        ];

        ob_start();
        require APP_PATH . '/Views/clientes/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/clientes/' . $id . '/editar');
            exit;
        }

        $campos = $this->extrairCampos();
        $antigo = $this->buscarPorId($id);

        try {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
            $campos['id_registro'] = $id;
            $stmt = db()->prepare("UPDATE clientes SET $sets, updated_at = NOW() WHERE id = :id_registro");
            $stmt->execute($campos);

            Auth::registrarAuditoria('clientes', 'editar', $id, $antigo, $campos);
            $_SESSION['flash_success'] = 'Cliente atualizado com sucesso!';
            header('Location: ' . APP_URL . '/clientes/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/clientes/' . $id . '/editar');
        }
        exit;
    }

    public function excluir(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Acesso negado.';
            header('Location: ' . APP_URL . '/clientes');
            exit;
        }

        try {
            $stmt = db()->prepare("UPDATE clientes SET status = 'inativo' WHERE id = ?");
            $stmt->execute([$id]);
            Auth::registrarAuditoria('clientes', 'excluir', $id);
            $_SESSION['flash_success'] = 'Cliente inativado com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao excluir cliente.';
        }

        header('Location: ' . APP_URL . '/clientes');
        exit;
    }

    public function busca(): void
    {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');

        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }

        try {
            $stmt = db()->prepare(
                "SELECT id, nome, cpf_cnpj, cidade FROM clientes
                 WHERE (nome LIKE ? OR cpf_cnpj LIKE ?) AND status = 'ativo'
                 LIMIT 10"
            );
            $stmt->execute(["%$q%", "%$q%"]);
            echo json_encode($stmt->fetchAll());
        } catch (\Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    // Helpers
    private function buscarPorId(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT c.*, u.nome AS vendedor_nome FROM clientes c
                 LEFT JOIN usuarios u ON u.id = c.vendedor_id
                 WHERE c.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extrairCampos(): array
    {
        return [
            'tipo_pessoa'       => $_POST['tipo_pessoa'] ?? 'juridica',
            'tipo_cliente'      => $_POST['tipo_cliente'] ?? 'cliente_final',
            'nome'              => trim($_POST['nome'] ?? ''),
            'nome_fantasia'     => trim($_POST['nome_fantasia'] ?? ''),
            'cpf_cnpj'          => $_POST['cpf_cnpj'] ?? '',
            'rg_ie'             => trim($_POST['rg_ie'] ?? ''),
            'email'             => trim($_POST['email'] ?? ''),
            'telefone'          => $_POST['telefone'] ?? '',
            'whatsapp'          => $_POST['whatsapp'] ?? '',
            'celular'           => $_POST['celular'] ?? '',
            'endereco'          => trim($_POST['endereco'] ?? ''),
            'numero'            => trim($_POST['numero'] ?? ''),
            'complemento'       => trim($_POST['complemento'] ?? ''),
            'bairro'            => trim($_POST['bairro'] ?? ''),
            'cidade'            => trim($_POST['cidade'] ?? ''),
            'estado'            => trim($_POST['estado'] ?? ''),
            'cep'               => $_POST['cep'] ?? '',
            'origem_lead'       => $_POST['origem_lead'] ?? '',
            'vendedor_id'       => !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : null,
            'classificacao'     => $_POST['classificacao'] ?? 'bronze',
            'status'            => $_POST['status'] ?? 'ativo',
            'recebe_whatsapp'   => isset($_POST['recebe_whatsapp']) ? 1 : 0,
            'recebe_campanha'   => isset($_POST['recebe_campanha']) ? 1 : 0,
            'recebe_producao'   => isset($_POST['recebe_producao']) ? 1 : 0,
            'recebe_financeiro' => isset($_POST['recebe_financeiro']) ? 1 : 0,
            'limite_credito'    => !empty($_POST['limite_credito']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['limite_credito'])) : 0,
            'observacoes'       => trim($_POST['observacoes'] ?? ''),
            'observacoes_internas' => trim($_POST['observacoes_internas'] ?? ''),
        ];
    }

    private function getVendedores(): array
    {
        try {
            $stmt = db()->query(
                "SELECT u.id, u.nome FROM usuarios u
                 JOIN perfis p ON p.id = u.perfil_id
                 WHERE u.ativo = 1
                 ORDER BY u.nome"
            );
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
