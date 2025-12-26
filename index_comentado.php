<?php
/* ============================================================================
 * SUMÁRIO: Script de Redirecionamento Inicial (Porteiro)
 * ----------------------------------------------------------------------------
 * DESTINO: Este ficheiro serve como o ponto de entrada principal (index) do
 * sistema, garantindo que qualquer acesso à raiz seja encaminhado para a
 * página de autenticação.
 * ----------------------------------------------------------------------------
 * LINHAS 14-18: Carregamento de dependências e lógica de caminhos base.
 * LINHAS 20-23: Comando de redirecionamento HTTP para a tela de login.
 * LINHAS 25-27: Interrupção de segurança do script.
 * ============================================================================
 */

// O QUE É: Tag de abertura do PHP.
// POR QUE ESTÁ ALI: Indica ao servidor que o código a seguir deve ser processado antes de chegar ao utilizador.

require_once __DIR__ . '/includes/auth.php';
// O QUE É: Inclusão obrigatória de ficheiro externo.
// POR QUE ESTÁ ALI: Carrega as funções globais do sistema, como a 'url_for()'. O '__DIR__' garante que o PHP encontre a pasta 'includes' independentemente de como o servidor está configurado.

header('Location: '.url_for('/login.php'));
// O QUE É: Cabeçalho de redirecionamento HTTP.
// POR QUE ESTÁ ALI: É a instrução que diz ao navegador: "Não fiques aqui, vai para a página de login". A função 'url_for' ajusta o caminho para que o link nunca quebre.



exit;
// O QUE É: Comando de terminação forçada.
// POR QUE ESTÁ ALI: É uma medida de segurança. Garante que, após a ordem de redirecionamento, mais nenhum código (que poderia ser sensível) seja lido ou executado pelo servidor.

?>
// O QUE É: Tag de fecho do PHP.
// POR QUE ESTÁ ALI: Finaliza o bloco de processamento, embora seja opcional em ficheiros puramente de código, ajuda a delimitar o fim da instrução.