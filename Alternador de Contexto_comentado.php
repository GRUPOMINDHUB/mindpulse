/* ============================================================================
 * SUMÁRIO: Script de Troca de Empresa Ativa no Sistema (147 linhas)
 * ----------------------------------------------------------------------------
 * DESCRIÇÃO: Este script permite que usuários alternem entre empresas associadas
 * à sua conta em sistemas multi-empresa. Processa requisições via GET (links),
 * POST (formulários) ou JSON (AJAX) para atualizar a empresa ativa na sessão.
 * ----------------------------------------------------------------------------
 * LINHAS 1-14:    Cabeçalho de documentação e sumário
 * LINHAS 15-19:   Inicialização da sessão e carregamento de dependências
 * LINHAS 20-33:   Definição da função respond_json() para respostas em formato JSON
 * LINHAS 34-112:  Bloco principal try: Processamento da troca de empresa
 *   LINHAS 36-53:  Captura e validação do ID da empresa de múltiplas fontes
 *   LINHAS 54-69:  Verificação de permissão do usuário na empresa solicitada
 *   LINHAS 70-77:  Atualização da sessão com nova empresa ativa
 *   LINHAS 78-86:  Atualização da lista completa de empresas na sessão
 *   LINHAS 87-112: Lógica de resposta baseada no método da requisição
 * LINHAS 113-146: Bloco catch: Tratamento de erros com respostas adequadas
 *   LINHAS 115-128: Tratamento de erros para requisições GET
 *   LINHAS 129-144: Tratamento de erros para requisições AJAX/POST
 * LINHA 144:      Fechamento da tag PHP
 * ----------------------------------------------------------------------------
 * FLUXO: Recebe company_id → Valida permissão → Atualiza sessão → Retorna resposta
 * ============================================================================
 */

// LINHAS 15-19: Inicialização da sessão e carregamento de dependências
<?php
// Inicia o bloco de código PHP.
if (session_status() === PHP_SESSION_NONE) session_start();
// Verifica se a sessão está inativa; se estiver, inicia uma nova sessão.
require_once __DIR__ . '/../includes/db.php';
// Inclui o arquivo de conexão ao banco de dados, subindo um nível de diretório.
require_once __DIR__ . '/../includes/auth.php';
// Inclui o arquivo de funções de autenticação, subindo um nível de diretório.
requireLogin();
// Executa a função que bloqueia o acesso de usuários que não estão logados.

function respond_json($arr, $code=200){
// Declara a função para enviar respostas JSON, definindo 200 como código padrão.
  while (ob_get_level()) ob_end_clean();
  // Enquanto houver buffers de saída ativos, limpa-os para não sujar o JSON.
  http_response_code($code);
  // Define o código de status HTTP da resposta (ex: 200, 400, 404).
  header('Content-Type: application/json; charset=utf-8');
  // Define o cabeçalho informando que o conteúdo retornado é um JSON em UTF-8.
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  // Converte o array PHP em uma string JSON, mantendo caracteres acentuados.
  exit;
  // Interrompe completamente a execução do script após enviar o JSON.
}
// Fecha a declaração da função respond_json.

try {
// Inicia um bloco de monitoramento de erros (try-catch).
  $raw = file_get_contents('php://input');
  // Lê os dados brutos enviados no corpo da requisição (útil para JSON via AJAX).
  $in  = json_decode($raw, true);
  // Decodifica a string JSON recebida para um array associativo PHP.
  $cid = 0;
  // Inicializa a variável $cid (Company ID) com o valor zero.
  if (is_array($in) && isset($in['company_id'])) {
  // Verifica se o JSON foi recebido e se contém a chave 'company_id'.
    $cid = (int)$in['company_id'];
    // Converte e atribui o valor de 'company_id' do JSON para a variável $cid.
  } elseif (isset($_POST['company_id'])) {
  // Caso não seja JSON, verifica se o valor veio via formulário comum (POST).
    $cid = (int)$_POST['company_id'];
    // Converte e atribui o valor do POST para a variável $cid.
  } elseif (isset($_GET['company_id'])) {
  // Caso não seja nenhum dos anteriores, verifica se o valor veio via URL (GET).
    $cid = (int)$_GET['company_id'];
    // Converte e atribui o valor do GET para a variável $cid.
  }
  // Fim da verificação das origens do ID da empresa.
  if ($cid <= 0) throw new Exception('company_id inválido');
  // Se o ID for menor ou igual a zero, gera um erro proposital para o catch.

  $userId = (int)$_SESSION['user']['id'];
  // Obtém o ID do usuário logado diretamente da sessão atual.

  $sql = "SELECT c.id, c.name, c.trade_name
          FROM companies c
          JOIN user_company uc ON uc.company_id=c.id
          WHERE uc.user_id=? AND c.id=? LIMIT 1";
  // Define a consulta SQL que valida se o usuário tem permissão na empresa escolhida.
  $st  = $pdo->prepare($sql);
  // Prepara a consulta no banco para evitar ataques de SQL Injection.
  $st->execute([$userId, $cid]);
  // Executa a consulta substituindo os "?" pelos valores de usuário e empresa.
  $row = $st->fetch(PDO::FETCH_ASSOC);
  // Tenta buscar a primeira linha de resultado como um array associativo.
  if (!$row) throw new Exception('Você não tem acesso a esta empresa.');
  // Se não encontrar nada, significa que o usuário não tem vínculo com essa empresa.

  $_SESSION['current_company'] = ['id' => (int)$row['id'], 'trade_name' => $row['trade_name']];
  // Atualiza os dados da "empresa ativa" na sessão do usuário.

  $st2 = $pdo->prepare("SELECT c.id, c.name, c.trade_name 
                        FROM companies c
                        JOIN user_company uc ON uc.company_id=c.id
                        WHERE uc.user_id=? ORDER BY c.trade_name");
  // Prepara uma nova consulta para listar todas as empresas às quais o usuário tem acesso.
  $st2->execute([$userId]);
  // Executa a listagem usando o ID do usuário logado.
  $_SESSION['companies'] = $st2->fetchAll(PDO::FETCH_ASSOC);
  // Salva a lista completa de empresas na sessão para atualizar menus/filtros.

  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
  // Verifica se o usuário acessou essa troca via um link direto (método GET).
    $back = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL.'/pages/home.php' : '/');
    // Descobre a URL da página anterior ou define a Home como destino padrão.
    while (ob_get_level()) ob_end_clean();
    // Limpa qualquer buffer de saída acumulado antes do redirecionamento.
    header('Location: '.$back); exit;
    // Envia o usuário de volta para a página onde ele estava e encerra.
  } else {
  // Caso a requisição tenha sido via POST ou AJAX (JSON).
    respond_json(['status'=>'ok']);
    // Chama a função para responder com uma mensagem de sucesso em JSON.
  }
  // Fim da lógica de decisão de resposta.
} catch(Throwable $e) {
// Captura qualquer erro ou exceção ocorrida dentro do bloco try.
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
  // Se o erro ocorreu em um acesso via link (GET).
    while (ob_get_level()) ob_end_clean();
    // Limpa a saída para garantir que apenas a mensagem de erro apareça.
    header('Content-Type: text/plain; charset=utf-8');
    // Define o tipo de conteúdo como texto simples.
    http_response_code(400);
    // Define o status de erro 400 (Bad Request).
    echo 'Erro ao trocar empresa: '.$e->getMessage();
    // Exibe a mensagem do erro no navegador.
    exit;
    // Interrompe a execução.
  } else {
  // Se o erro ocorreu em uma requisição AJAX/POST.
    respond_json(['status'=>'error','message'=>$e->getMessage()], 400);
    // Retorna o erro formatado em JSON para o sistema que fez a chamada.
  }
  // Fim do tratamento de erros.
}
// Fecha o bloco catch.
?>
