<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class RhController
{
    private array $colaboradorStatus = [
        'ativo' => 'Ativo',
        'ferias' => 'Férias',
        'afastado' => 'Afastado',
        'demitido' => 'Demitido',
    ];

    private array $contratoLabels = [
        'clt' => 'CLT',
        'pj' => 'PJ',
        'autonomo' => 'Autônomo',
        'estagio' => 'Estágio',
    ];

    private array $equipamentoStatus = [
        'ativo' => 'Ativo',
        'manutencao' => 'Manutenção',
        'inativo' => 'Inativo',
        'baixado' => 'Baixado',
    ];

    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $this->requerRh();
        $colaboradores = $this->colaboradores();
        $setores = $this->query("SELECT s.*, c.nome AS responsavel_nome FROM rh_setores s LEFT JOIN colaboradores c ON c.id = s.responsavel_id ORDER BY s.status, s.nome");
        $cargos = $this->query("SELECT c.*, s.nome AS setor_nome FROM rh_cargos c LEFT JOIN rh_setores s ON s.id = c.setor_id ORDER BY c.status, c.nome");
        $equipamentos = $this->query("SELECT e.*, s.nome AS setor_nome, c.nome AS responsavel_nome FROM equipamentos e LEFT JOIN rh_setores s ON s.id = e.setor_id LEFT JOIN colaboradores c ON c.id = e.responsavel_id ORDER BY FIELD(e.status,'ativo','manutencao','inativo','baixado'), e.nome");
        $veiculos = $this->query("SELECT v.*, c.nome AS responsavel_nome FROM veiculos v LEFT JOIN colaboradores c ON c.id = v.responsavel_id ORDER BY FIELD(v.status,'ativo','manutencao','inativo','baixado'), v.nome");
        $statusLabels = $this->colaboradorStatus;
        $contratoLabels = $this->contratoLabels;
        $equipamentoStatus = $this->equipamentoStatus;

        $titulo = 'RH Operacional';
        $subtitulo = 'Colaboradores, cargos, setores, equipamentos, veículos e custos operacionais';
        $headerActions = '<a href="' . APP_URL . '/rh/colaboradores/novo" class="btn btn-primary"><i class="bi bi-person-plus"></i> Colaborador</a> <a href="' . APP_URL . '/rh/equipamentos/novo" class="btn btn-secondary"><i class="bi bi-tools"></i> Equipamento</a>';

        ob_start();
        require APP_PATH . '/Views/rh/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novoColaborador(): void
    {
        $this->requerRh();
        $colaborador = ['tipo_contrato' => 'clt', 'status' => 'ativo', 'jornada_mensal' => 220, 'custo_hora' => 0, 'salario' => 0];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Colaborador';
        $subtitulo = 'Cadastro pessoal, contrato e custo/hora';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => 'Novo Colaborador', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/rh/colaborador_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarColaborador(): void
    {
        $this->salvarColaborador();
    }

    public function editarColaborador(string $id): void
    {
        $this->requerRh();
        $colaborador = $this->colaborador($id);
        if (!$colaborador) {
            $_SESSION['flash_error'] = 'Colaborador não encontrado.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Colaborador';
        $subtitulo = $colaborador['nome'];
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => $colaborador['nome'], 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/rh/colaborador_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarColaborador(string $id): void
    {
        $this->salvarColaborador($id);
    }

    public function novoSetor(): void
    {
        $this->requerRh();
        $tipo = 'setor';
        $registro = ['status' => 'ativo'];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Setor';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => 'Novo Setor', 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/cadastro_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarSetor(): void
    {
        $this->salvarSetor();
    }

    public function editarSetor(string $id): void
    {
        $this->requerRh();
        $registro = $this->buscar('rh_setores', $id);
        if (!$registro) {
            $_SESSION['flash_error'] = 'Setor não encontrado.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $tipo = 'setor';
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Setor';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => $registro['nome'], 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/cadastro_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarSetor(string $id): void
    {
        $this->salvarSetor($id);
    }

    public function novoCargo(): void
    {
        $this->requerRh();
        $tipo = 'cargo';
        $registro = ['status' => 'ativo', 'salario_base' => 0, 'custo_hora_padrao' => 0];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Cargo';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => 'Novo Cargo', 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/cadastro_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarCargo(): void
    {
        $this->salvarCargo();
    }

    public function editarCargo(string $id): void
    {
        $this->requerRh();
        $registro = $this->buscar('rh_cargos', $id);
        if (!$registro) {
            $_SESSION['flash_error'] = 'Cargo não encontrado.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $tipo = 'cargo';
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Cargo';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => $registro['nome'], 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/cadastro_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarCargo(string $id): void
    {
        $this->salvarCargo($id);
    }

    public function novoEquipamento(): void
    {
        $this->requerEquipamentos();
        $tipo = 'equipamento';
        $registro = ['tipo' => 'maquina', 'status' => 'ativo', 'custo_hora' => 0, 'valor_aquisicao' => 0];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Equipamento';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => 'Novo Equipamento', 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/recurso_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarEquipamento(): void
    {
        $this->salvarEquipamento();
    }

    public function editarEquipamento(string $id): void
    {
        $this->requerEquipamentos();
        $registro = $this->buscar('equipamentos', $id);
        if (!$registro) {
            $_SESSION['flash_error'] = 'Equipamento não encontrado.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $tipo = 'equipamento';
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Equipamento';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => $registro['nome'], 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/recurso_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarEquipamento(string $id): void
    {
        $this->salvarEquipamento($id);
    }

    public function novoVeiculo(): void
    {
        $this->requerEquipamentos();
        $tipo = 'veiculo';
        $registro = ['tipo' => 'carro', 'status' => 'ativo', 'custo_km' => 0, 'custo_hora' => 0, 'km_atual' => 0];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Veículo';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => 'Novo Veículo', 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/recurso_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarVeiculo(): void
    {
        $this->salvarVeiculo();
    }

    public function editarVeiculo(string $id): void
    {
        $this->requerEquipamentos();
        $registro = $this->buscar('veiculos', $id);
        if (!$registro) {
            $_SESSION['flash_error'] = 'Veículo não encontrado.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $tipo = 'veiculo';
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Veículo';
        $breadcrumbs = [['label' => 'RH', 'url' => '/rh'], ['label' => $registro['nome'], 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/rh/recurso_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarVeiculo(string $id): void
    {
        $this->salvarVeiculo($id);
    }

    private function salvarColaborador(?string $id = null): void
    {
        $this->requerRh();
        $this->validarCsrf('/rh');
        $dados = [
            'usuario_id' => !empty($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : null,
            'nome' => trim($_POST['nome'] ?? ''),
            'cpf' => trim($_POST['cpf'] ?? ''),
            'rg' => trim($_POST['rg'] ?? ''),
            'data_nascimento' => $_POST['data_nascimento'] ?: null,
            'sexo' => $_POST['sexo'] ?: null,
            'email' => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'cargo' => trim($_POST['cargo'] ?? ''),
            'setor' => trim($_POST['setor'] ?? ''),
            'salario' => $this->numero($_POST['salario'] ?? 0),
            'custo_hora' => $this->numero($_POST['custo_hora'] ?? 0),
            'jornada_mensal' => max(1, (int)($_POST['jornada_mensal'] ?? 220)),
            'habilidades' => trim($_POST['habilidades'] ?? ''),
            'data_admissao' => $_POST['data_admissao'] ?: null,
            'data_demissao' => $_POST['data_demissao'] ?: null,
            'tipo_contrato' => $_POST['tipo_contrato'] ?? 'clt',
            'banco' => trim($_POST['banco'] ?? ''),
            'agencia' => trim($_POST['agencia'] ?? ''),
            'conta' => trim($_POST['conta'] ?? ''),
            'tipo_conta' => $_POST['tipo_conta'] ?? 'corrente',
            'pix' => trim($_POST['pix'] ?? ''),
            'status' => $_POST['status'] ?? 'ativo',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do colaborador é obrigatório.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        if ($dados['custo_hora'] <= 0 && $dados['salario'] > 0) {
            $dados['custo_hora'] = round($dados['salario'] / $dados['jornada_mensal'], 2);
        }
        $this->salvar('colaboradores', $dados, $id, 'colaborador');
    }

    private function salvarSetor(?string $id = null): void
    {
        $this->requerRh();
        $this->validarCsrf('/rh');
        $nome = trim($_POST['nome'] ?? '');
        $dados = [
            'nome' => $nome,
            'slug' => $this->slug($nome),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'status' => $_POST['status'] ?? 'ativo',
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do setor é obrigatório.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $this->salvar('rh_setores', $dados, $id, 'setor');
    }

    private function salvarCargo(?string $id = null): void
    {
        $this->requerRh();
        $this->validarCsrf('/rh');
        $dados = [
            'setor_id' => !empty($_POST['setor_id']) ? (int)$_POST['setor_id'] : null,
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'salario_base' => $this->numero($_POST['salario_base'] ?? 0),
            'custo_hora_padrao' => $this->numero($_POST['custo_hora_padrao'] ?? 0),
            'status' => $_POST['status'] ?? 'ativo',
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do cargo é obrigatório.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $this->salvar('rh_cargos', $dados, $id, 'cargo');
    }

    private function salvarEquipamento(?string $id = null): void
    {
        $this->requerEquipamentos();
        $this->validarCsrf('/rh');
        $dados = [
            'codigo' => trim($_POST['codigo'] ?? '') ?: $this->gerarCodigo('EQP', 'equipamentos'),
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'maquina',
            'setor_id' => !empty($_POST['setor_id']) ? (int)$_POST['setor_id'] : null,
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'marca' => trim($_POST['marca'] ?? ''),
            'modelo' => trim($_POST['modelo'] ?? ''),
            'patrimonio' => trim($_POST['patrimonio'] ?? ''),
            'status' => $_POST['status'] ?? 'ativo',
            'custo_hora' => $this->numero($_POST['custo_hora'] ?? 0),
            'data_aquisicao' => $_POST['data_aquisicao'] ?: null,
            'valor_aquisicao' => $this->numero($_POST['valor_aquisicao'] ?? 0),
            'manutencao_prevista' => $_POST['manutencao_prevista'] ?: null,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do equipamento é obrigatório.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $this->salvar('equipamentos', $dados, $id, 'equipamento');
    }

    private function salvarVeiculo(?string $id = null): void
    {
        $this->requerEquipamentos();
        $this->validarCsrf('/rh');
        $dados = [
            'codigo' => trim($_POST['codigo'] ?? '') ?: $this->gerarCodigo('VEI', 'veiculos'),
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'carro',
            'placa' => strtoupper(trim($_POST['placa'] ?? '')),
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'status' => $_POST['status'] ?? 'ativo',
            'custo_km' => $this->numero($_POST['custo_km'] ?? 0),
            'custo_hora' => $this->numero($_POST['custo_hora'] ?? 0),
            'km_atual' => $this->numero($_POST['km_atual'] ?? 0),
            'manutencao_prevista' => $_POST['manutencao_prevista'] ?: null,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do veículo é obrigatório.';
            header('Location: ' . APP_URL . '/rh');
            exit;
        }
        $this->salvar('veiculos', $dados, $id, 'veículo');
    }

    private function salvar(string $tabela, array $dados, ?string $id, string $label): void
    {
        try {
            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                db()->prepare("UPDATE $tabela SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                Auth::registrarAuditoria($tabela, 'editar', (int)$id);
                $_SESSION['flash_success'] = ucfirst($label) . ' atualizado.';
            } else {
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                db()->prepare("INSERT INTO $tabela ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
                Auth::registrarAuditoria($tabela, 'criar', (int)db()->lastInsertId());
                $_SESSION['flash_success'] = ucfirst($label) . ' cadastrado.';
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar ' . $label . ': ' . $e->getMessage();
        }
        header('Location: ' . APP_URL . '/rh');
        exit;
    }

    private function requerRh(): void
    {
        if (!Auth::pode('colaboradores')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para acessar RH.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }

    private function requerEquipamentos(): void
    {
        if (!Auth::pode('equipamentos') && !Auth::pode('colaboradores')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para acessar equipamentos.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }

    private function validarCsrf(string $redirect): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . $redirect);
            exit;
        }
    }

    private function colaboradores(): array
    {
        return $this->query(
            "SELECT c.*, u.email AS usuario_email, u.ativo AS usuario_ativo
             FROM colaboradores c
             LEFT JOIN usuarios u ON u.id = c.usuario_id
             ORDER BY FIELD(c.status,'ativo','ferias','afastado','demitido'), c.nome"
        );
    }

    private function colaborador(string $id): ?array
    {
        return $this->buscar('colaboradores', $id);
    }

    private function contextoFormulario(): array
    {
        return [
            'usuarios' => $this->query("SELECT id, nome, email FROM usuarios WHERE ativo = 1 ORDER BY nome"),
            'colaboradores' => $this->query("SELECT id, nome, cargo, setor FROM colaboradores WHERE status IN ('ativo','ferias') ORDER BY nome"),
            'setores' => $this->query("SELECT * FROM rh_setores WHERE status = 'ativo' ORDER BY nome"),
            'cargos' => $this->query("SELECT * FROM rh_cargos WHERE status = 'ativo' ORDER BY nome"),
            'colaboradorStatus' => $this->colaboradorStatus,
            'contratoLabels' => $this->contratoLabels,
            'equipamentoStatus' => $this->equipamentoStatus,
        ];
    }

    private function buscar(string $tabela, string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM $tabela WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function gerarCodigo(string $prefixoBase, string $tabela): string
    {
        $prefixo = $prefixoBase . '-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM $tabela WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function query(string $sql): array
    {
        try {
            return db()->query($sql)->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function numero($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }
        if (is_numeric($valor)) {
            return (float)$valor;
        }
        return (float)str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9,.-]/', '', (string)$valor));
    }

    private function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
        return trim($value, '-') ?: 'setor';
    }
}
