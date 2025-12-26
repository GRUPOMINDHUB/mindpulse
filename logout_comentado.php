<?php
/* ============================================================================
 * SUMÁRIO: Script de Encerramento de Sessão (Logout)
 * ----------------------------------------------------------------------------
 * DESTINO: Finalizar a conexão segura do usuário com o sistema, limpando todos
 * os rastros de autenticação no servidor e redirecionando para a tela inicial.
 * ----------------------------------------------------------------------------
 * LINHAS 14-19:  Inicialização/Recuperação da sessão ativa.
 * LINHAS 21-25:  Carregamento de funções auxiliares de URL.
 * LINHAS 27-31:  Limpeza dos dados do usuário na memória ($_SESSION).
 * LINHAS 33-37:  Exclusão física dos registros de sessão no servidor.
 * LINHAS 39-43:  Redirecionamento de segurança para a página de Login.
 * ============================================================================
 */

// O QUE É: Verificação de Sessão.
// POR QUE ESTÁ ALI: Para destruir uma sessão ou limpar seus dados, o PHP precisa
// primeiro saber qual sessão está ativa. Se não houver uma iniciada, ele inicia
// para ter algo que possa manipular e, em seguida, fechar.
if (session_status() === PHP_SESSION_NONE) session_start();

// O QUE É: Importação de dependências.
// POR QUE ESTÁ ALI: O comando de redirecionamento final usa a função 'url_for()'.
// Este arquivo 'auth.php' é importado para que o sistema saiba como gerar o endereço
// correto da página de login.
require_once __DIR__ . '/../includes/auth.php';

// O QUE É: Esvaziamento da Superglobal $_SESSION.
// POR QUE ESTÁ ALI: Imagine que a sessão é uma caixa com os dados do usuário.
// Atribuir um array vazio '[]' garante que, mesmo que a sessão ainda exista por
// alguns milissegundos, todos os dados (nome, ID, cargo) foram apagados da memória.
$_SESSION = [];



// O QUE É: Destruição física da sessão.
// POR QUE ESTÁ ALI: Este comando remove o arquivo de sessão temporário armazenado 
// no servidor. É o "golpe final" que invalida o cookie de sessão que está no 
// navegador do usuário.
session_destroy();

// O QUE É: Redirecionamento de saída.
// POR QUE ESTÁ ALI: Após limpar tudo, não faz sentido o usuário ficar em uma página 
// em branco. O sistema o envia de volta para a tela de login. O 'exit' interrompe 
// o script para garantir que nada mais seja executado.
header('Location: '.url_for('/login.php')); exit;
?>