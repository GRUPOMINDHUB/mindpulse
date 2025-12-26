<?php
/* ============================================================================
 * SUMÁRIO: Script de Troca de Empresa Ativa no Sistema
 * ----------------------------------------------------------------------------
 * DESTINO: Gerenciar a alternância de contexto em sistemas multi-empresa (SaaS),
 * garantindo que o usuário só visualize dados de empresas às quais possui acesso.
 * ----------------------------------------------------------------------------
 * LINHAS 19-27:  Inicialização de sessão, conexão com DB e barreira de login.
 * LINHAS 29-41:  Definição da função de resposta técnica para requisições AJAX (JSON).
 * LINHAS 43-65:  Captura do ID da empresa de 3 fontes diferentes e validação inicial.
 * LINHAS 67-80:  Regra de Negócio: Consulta ao banco para validar permissão de acesso.
 * LINHAS 82-93:  Persistência: Atualização das variáveis de sessão com o novo contexto.
 * LINHAS 95-107: Finalização: Redirecionamento (navegador) ou resposta JSON (API).
 * LINHAS 108-124: Tratamento de Exceções: Gestão de erros amigável ao usuário.
 * ============================================================================
 */

// O QUE É: Início do bloco PHP.
// POR QUE ESTÁ ALI: Permite que o servidor processe a lógica antes de enviar dados ao navegador.

if (session_status() === PHP_SESSION_NONE) session_start();
// O QUE É: Verificação e inicialização de sessão.
// POR QUE ESTÁ ALI: Necessário para acessar e modificar os dados do usuário logado e a empresa atual.

require_once __DIR__ . '/../includes/db.php';
// O QUE É: Importação de arquivo de conexão.
// POR QUE ESTÁ ALI: Fornece o objeto $pdo para realizar consultas ao banco de dados.

require_once __DIR__ . '/../includes/auth.php';
// O QUE É: Importação de lógica de autenticação.
// POR QUE ESTÁ ALI: Traz funções essenciais como requireLogin() e url_for().

requireLogin();
// O QUE É: Função de barreira de segurança.
// POR QUE ESTÁ ALI: Impede que usuários não autenticados acessem este script de troca.

function respond_json($arr, $code=200){
// O QUE É: Declaração de função auxiliar de resposta.
// POR QUE ESTÁ ALI: Padroniza o envio de dados para o Front-End quando a troca ocorre via JavaScript.

  while (ob_get_level()) ob_end_clean();
  // O QUE É: Limpeza do buffer de saída.
  // POR QUE ESTÁ ALI: Garante que nenhum texto acidental "suje" o formato JSON final.

  http_response_code($code);
  // O QUE É: Configuração do status HTTP.
  // POR QUE ESTÁ ALI: Indica tecnicamente se a operação foi um sucesso (200) ou erro (400).

  header('Content-Type: application/json; charset=utf-8');
  // O QUE É: Cabeçalho de tipo de conteúdo.
  // POR QUE ESTÁ ALI: Informa ao navegador que o corpo da resposta deve ser lido como JSON.

  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  // O QUE É: Conversão de array para JSON.
  // POR QUE ESTÁ ALI: Transforma dados do PHP em um formato que o JavaScript entende nativamente.

  exit;
  // O QUE É: Interrupção de script.
  // POR QUE ESTÁ ALI: Garante que nada mais seja processado após o envio da resposta.
}
// O QUE É: Fechamento da função.

try {
// O QUE É: Início do bloco de tratamento de erros.
// POR QUE ESTÁ ALI: Captura qualquer falha no processo (como falta de acesso) sem derrubar o sistema.

  $raw = file_get_contents('php://input');
  // O QUE É: Leitura de dados brutos da requisição.
  // POR QUE ESTÁ ALI: Permite ler dados enviados no formato JSON por bibliotecas como Axios ou Fetch.

  $in  = json_decode($raw, true);
  // O QUE É: Decodificador JSON.
  // POR QUE ESTÁ ALI: Converte a string JSON recebida em um array associativo PHP.

  $cid = 0;
  // O QUE É: Inicialização de variável.
  // POR QUE ESTÁ ALI: Define um valor padrão seguro para o ID da empresa (Company ID).

  if (is_array($in) && isset($in['company_id'])) {
  // O QUE É: Verificação de dados JSON.
  // POR QUE ESTÁ ALI: Checa se o ID da empresa foi enviado via JSON (AJAX).

    $cid = (int)$in['company_id'];
    // O QUE É: Conversão e atribuição.
    // POR QUE ESTÁ ALI: Garante que o ID seja tratado estritamente como um número inteiro.

  } elseif (isset($_POST['company_id'])) {
  // O QUE É: Verificação de dados via POST.
  // POR QUE ESTÁ ALI: Checa se o ID veio de um formulário HTML convencional.

    $cid = (int)$_POST['company_id'];
    // O QUE É: Atribuição de dado do formulário.
    // POR QUE ESTÁ ALI: Captura o ID vindo de submissão de formulário.

  } elseif (isset($_GET['company_id'])) {
  // O QUE É: Verificação de dados via GET.
  // POR QUE ESTÁ ALI: Checa se o ID veio diretamente na URL (link).

    $cid = (int)$_GET['company_id'];
    // O QUE É: Atribuição de dado da URL.
    // POR QUE ESTÁ ALI: Captura o ID para trocas rápidas via links de navegação.
  }
  // O QUE É: Fim do bloco de captura de ID.

  if ($cid <= 0) throw new Exception('company_id inválido');
  // O QUE É: Validação de valor.
  // POR QUE ESTÁ ALI: Impede o processamento se nenhum ID válido foi fornecido.

  $userId = (int)$_SESSION['user']['id'];
  // O QUE É: Recuperação do ID do usuário.
  // POR QUE ESTÁ ALI: Usado para validar se o usuário logado tem vínculo com a empresa solicitada.

  $sql = "SELECT c.id, c.name, c.trade_name
          FROM companies c
          JOIN user_company uc ON uc.company_id=c.id
          WHERE uc.user_id=? AND c.id=? LIMIT 1";
  // O QUE É: Consulta SQL de validação.
  // POR QUE ESTÁ ALI: Verifica a existência da empresa e a permissão de acesso do usuário em uma única operação.

  $st  = $pdo->prepare($sql);
  // O QUE É: Preparação da Query.
  // POR QUE ESTÁ ALI: Protege o sistema contra ataques de SQL Injection.

  $st->execute([$userId, $cid]);
  // O QUE É: Execução da Query.
  // POR QUE ESTÁ ALI: Substitui os placeholders (?) pelos valores reais de forma segura.

  $row = $st->fetch(PDO::FETCH_ASSOC);
  // O QUE É: Coleta de resultado.
  // POR QUE ESTÁ ALI: Recupera os dados da empresa se a permissão for válida.

  if (!$row) throw new Exception('Você não tem acesso a esta empresa.');
  // O QUE É: Barreira de segurança lógica.
  // POR QUE ESTÁ ALI: Impede que um usuário troque para uma empresa que não pertence a ele.

  $_SESSION['current_company'] = ['id' => (int)$row['id'], 'trade_name' => $row['trade_name']];
  // O QUE É: Atualização do contexto da sessão.
  // POR QUE ESTÁ ALI: Define qual empresa será usada para filtrar dados em todo o restante do sistema.

  $st2 = $pdo->prepare("SELECT c.id, c.name, c.trade_name
                        FROM companies c
                        JOIN user_company uc ON uc.company_id=c.id
                        WHERE uc.user_id=? ORDER BY c.trade_name");
  // O QUE É: Query de atualização de lista.
  // POR QUE ESTÁ ALI: Atualiza a lista de empresas disponíveis para o usuário.

  $st2->execute([$userId]);
  // O QUE É: Execução da listagem.
  // POR QUE ESTÁ ALI: Busca as empresas vinculadas ao ID do usuário.

  $_SESSION['companies'] = $st2->fetchAll(PDO::FETCH_ASSOC);
  // O QUE É: Atualização do menu de empresas.
  // POR QUE ESTÁ ALI: Garante que o seletor no topo do site esteja sempre sincronizado.

  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
  // O QUE É: Verificação de método de navegação.
  // POR QUE ESTÁ ALI: Define se o usuário deve ser redirecionado ou se recebe uma resposta JSON.

    $back = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL.'/pages/home.php' : '/');
    // O QUE É: Lógica de retorno de página.
    // POR QUE ESTÁ ALI: Tenta devolver o usuário para a página de onde ele veio após a troca.

    while (ob_get_level()) ob_end_clean();
    // O QUE É: Limpeza de buffer.
    // POR QUE ESTÁ ALI: Previne erros de "headers already sent" ao redirecionar.

    header('Location: '.$back); exit;
    // O QUE É: Redirecionamento HTTP.
    // POR QUE ESTÁ ALI: Move o navegador do usuário para a URL de destino.

  } else {
  // O QUE É: Alternativa para requisições não-GET.

    respond_json(['status'=>'ok']);
    // O QUE É: Resposta de sucesso em JSON.
    // POR QUE ESTÁ ALI: Confirma para o JavaScript que a troca foi processada com sucesso.
  }

} catch(Throwable $e) {
// O QUE É: Captura de exceções globais.
// POR QUE ESTÁ ALI: Trata erros de banco, falta de permissão ou falhas de lógica.

  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
  // O QUE É: Tratamento de erro para navegação direta.

    while (ob_get_level()) ob_end_clean();
    // O QUE É: Limpeza de buffer no erro.

    header('Content-Type: text/plain; charset=utf-8');
    // O QUE É: Cabeçalho de texto simples.
    // POR QUE ESTÁ ALI: Exibe a mensagem de erro de forma legível no navegador.

    http_response_code(400);
    // O QUE É: Código de erro do cliente.

    echo 'Erro ao trocar empresa: '.$e->getMessage();
    // O QUE É: Exibição da mensagem.
    // POR QUE ESTÁ ALI: Informa o usuário sobre o que deu errado (ex: "ID inválido").

    exit;
    // O QUE É: Encerramento de segurança.

  } else {
  // O QUE É: Tratamento de erro para AJAX.

    respond_json(['status'=>'error','message'=>$e->getMessage()], 400);
    // O QUE É: Resposta de erro estruturada.
    // POR QUE ESTÁ ALI: Permite que o Front-End trate a falha e mostre um alerta ao usuário.
  }
}
// O QUE É: Fim do bloco catch e do script.
?>