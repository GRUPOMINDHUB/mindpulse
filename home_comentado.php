<?php
/* ============================================================================
 * SUMÁRIO: Página de Dashboard / Home pós-login do sistema Mindhub
 * ----------------------------------------------------------------------------
 * DESCRIÇÃO: Dashboard principal exibido após login, com boas-vindas personalizadas,
 * informações do perfil do usuário e atalhos para funcionalidades do sistema.
 * ----------------------------------------------------------------------------
 * LINHAS 1-17:    Cabeçalho de documentação e sumário
 * LINHAS 18-23:   Inicialização PHP e carregamento do layout inicial
 * LINHAS 24-54:   Card de boas-vindas com saudação personalizada e status da sessão
 * LINHAS 55-196:  Layout em grid com duas colunas principais
 *   LINHAS 57-130:  Coluna esquerda (4/12) - Perfil do usuário
 *   LINHAS 131-195: Coluna direita (8/12) - Acesso rápido com links condicionais
 * LINHAS 197-202: Fechamento do layout com rodapé e scripts
 * ----------------------------------------------------------------------------
<?php
// O QUE É: Tag de abertura do PHP
// POR QUE ESTÁ ALI: Marca o início do código PHP no arquivo

require_once __DIR__ . '/includes/layout_start.php';
// O QUE É: Inclusão de arquivo de layout inicial
// POR QUE ESTÁ ALI: Para carregar o cabeçalho, CSS, JavaScript e estrutura inicial da página

?>
<!-- O QUE É: Fechamento do PHP e início do HTML
<!-- POR QUE ESTÁ ALI: Permite alternar entre código PHP e HTML na mesma página -->

<div class="card" style="padding:20px; display:flex; align-items:center; justify-content:space-between">
<!-- O QUE É: Div com classe CSS e estilo inline
<!-- POR QUE ESTÁ ALI: Cria um cartão visual que organiza conteúdo com flexbox -->

  <div>
  <!-- O QUE É: Div container
  <!-- POR QUE ESTÁ ALI: Agrupa elementos de texto relacionados -->

    <div style="font-weight:900; font-size:1.2rem">Olá, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>!</div>
    <!-- O QUE É: Saudação personalizada com nome do usuário
    <!-- POR QUE ESTÁ ALI: Dá boas-vindas ao usuário logado de forma personalizada
    <!-- htmlspecialchars: Previne ataques XSS (Cross-Site Scripting)
    <!-- $_SESSION['user']['name']: Pega o nome do usuário da sessão
    <!-- ?? '': Operador null coalescing, usa string vazia se nome não existir -->

    <div style="color:#cbd5e1; margin-top:4px">Bem-vindo(a) à Mindhub. Selecione uma empresa no topo para trabalhar.</div>
    <!-- O QUE É: Mensagem de instrução
    <!-- POR QUE ESTÁ ALI: Orienta o usuário sobre o próximo passo (escolher empresa)
    <!-- color:#cbd5e1: Define cor cinza clara para texto secundário
    <!-- margin-top:4px: Adiciona espaçamento acima do elemento -->

  </div>
  <!-- O QUE É: Fechamento da div container
  <!-- POR QUE ESTÁ ALI: Fecha o agrupamento de textos -->

  <div class="badge"><span class="brand-dot"></span> Sessão ativa</div>
  <!-- O QUE É: Badge/etiqueta visual
  <!-- POR QUE ESTÁ ALI: Indica visualmente que a sessão está ativa
  <!-- class="badge": Aplica estilos CSS pré-definidos
  <!-- span class="brand-dot": Cria um ponto decorativo com cores da marca -->

</div>
<!-- O QUE É: Fechamento do cartão principal
<!-- POR QUE ESTÁ ALI: Finaliza a seção de boas-vindas -->

<div style="display:grid; gap:16px; grid-template-columns: repeat(12,1fr); margin-top:16px">
<!-- O QUE É: Container com layout grid CSS
<!-- POR QUE ESTÁ ALI: Cria uma grade responsiva para organizar cards lado a lado
<!-- display:grid: Ativa o sistema de grid do CSS
<!-- grid-template-columns: repeat(12,1fr): Divide em 12 colunas de tamanho igual
<!-- gap:16px: Espaçamento entre os itens do grid
<!-- margin-top:16px: Espaço acima do grid -->

  <div class="card" style="grid-column: span 4; padding:18px">
  <!-- O QUE É: Cartão que ocupa 4 colunas do grid (1/3 da largura)
  <!-- POR QUE ESTÁ ALI: Container para informações do perfil do usuário
  <!-- grid-column: span 4: Faz o elemento ocupar 4 das 12 colunas
  <!-- padding:18px: Espaçamento interno -->

    <div style="font-weight:800; margin-bottom:6px">Seu perfil</div>
    <!-- O QUE É: Título da seção
    <!-- POR QUE ESTÁ ALI: Identifica visualmente a seção de perfil
    <!-- font-weight:800: Texto em negrito
    <!-- margin-bottom:6px: Espaço abaixo do título -->

    <div style="color:#cbd5e1; font-size:.95rem">
    <!-- O QUE É: Container para detalhes do perfil
    <!-- POR QUE ESTÁ ALI: Agrupa informações secundárias do usuário
    <!-- font-size:.95rem: Texto ligeiramente menor que o padrão -->

      Tipo: <strong><?= htmlspecialchars($_SESSION['user']['type']) ?></strong><br/>
      <!-- O QUE É: Exibe o tipo/tipo do usuário
      <!-- POR QUE ESTÁ ALI: Mostra o nível de acesso (admin, usuário, etc.)
      <!-- <strong>: Tag HTML para texto em negrito
      <!-- <br/>: Quebra de linha -->

      Cargos:
      <!-- O QUE É: Rótulo para lista de cargos
      <!-- POR QUE ESTÁ ALI: Indica que os itens seguintes são cargos do usuário -->

      <?php if (!empty($_SESSION['roles'])): ?>
      <!-- O QUE É: Condicional PHP que verifica se há cargos
      <!-- POR QUE ESTÁ ALI: Evita erros se a variável de sessão estiver vazia
      <!-- !empty(): Verifica se o array não está vazio -->

        <?= htmlspecialchars(implode(', ', array_map(fn($r)=>$r['name'], $_SESSION['roles']))) ?>
        <!-- O QUE É: Converte array de cargos em string separada por vírgulas
        <!-- POR QUE ESTÁ ALI: Exibe todos os cargos do usuário em formato legível
        <!-- array_map(): Aplica função a cada item do array
        <!-- fn($r)=>$r['name']: Função arrow que extrai o nome de cada cargo
        <!-- implode(): Junta elementos do array com separador ', ' -->

      <?php else: ?>Nenhum cargo atribuído<?php endif; ?>
      <!-- O QUE É: Mensagem alternativa se não houver cargos
      <!-- POR QUE ESTÁ ALI: Fornece feedback claro quando o usuário não tem cargos
      <!-- else: Parte alternativa do condicional
      <!-- endif;: Fecha o bloco condicional -->

    </div>
    <!-- O QUE É: Fechamento do container de detalhes
    <!-- POR QUE ESTÁ ALI: Finaliza a seção de informações do perfil -->

  </div>
  <!-- O QUE É: Fechamento do cartão de perfil
  <!-- POR QUE ESTÁ ALI: Termina o primeiro cartão do grid -->

  <div class="card" style="grid-column: span 8; padding:18px">
  <!-- O QUE É: Cartão que ocupa 8 colunas do grid (2/3 da largura)
  <!-- POR QUE ESTÁ ALI: Container para links de acesso rápido
  <!-- grid-column: span 8: Ocupa 8 das 12 colunas -->

    <div style="font-weight:800; margin-bottom:6px">Acesso rápido</div>
    <!-- O QUE É: Título da seção
    <!-- POR QUE ESTÁ ALI: Identifica a seção de atalhos/links rápidos -->

    <div style="display:flex; gap:10px; flex-wrap:wrap">
    <!-- O QUE É: Container flexbox para botões
    <!-- POR QUE ESTÁ ALI: Organiza botões horizontalmente com quebra de linha
    <!-- display:flex: Layout flexível
    <!-- gap:10px: Espaçamento entre botões
    <!-- flex-wrap:wrap: Permite quebra para linha seguinte -->

      <a class="button" href="<?= url_for('/pages/treinamentos.php') ?>">Treinamentos</a>
      <!-- O QUE É: Link/Botão para página de treinamentos
      <!-- POR QUE ESTÁ ALI: Atalho rápido para funcionalidade comum
      <!-- class="button": Aplica estilos CSS de botão
      <!-- url_for(): Função que gera URL completa (provavelmente definida em layout_start.php) -->

      <a class="button" href="<?= url_for('/pages/meus_dados.php') ?>">Meus dados</a>
      <!-- O QUE É: Link/Botão para página de dados pessoais
      <!-- POR QUE ESTÁ ALI: Atalho para edição de perfil do usuário -->

      <?php if (canAccessAdmin()): ?>
      <!-- O QUE É: Condicional de verificação de permissão
      <!-- POR QUE ESTÁ ALI: Controla visibilidade de links administrativos
      <!-- canAccessAdmin(): Função que verifica se usuário tem acesso administrativo -->

      <a class="button" href="<?= url_for('/pages/empresas.php') ?>">Empresas</a>
      <!-- O QUE É: Link/Botão para gerenciamento de empresas
      <!-- POR QUE ESTÁ ALI: Acesso rápido a módulo administrativo
      <!-- Só visível para usuários com permissão admin -->

      <a class="button" href="<?= url_for('/pages/usuarios.php') ?>">Usuários</a>
      <!-- O QUE É: Link/Botão para gerenciamento de usuários
      <!-- POR QUE ESTÁ ALI: Acesso rápido a módulo de gestão de usuários -->

      <a class="button" href="<?= url_for('/pages/config.php') ?>">Configurações</a>
      <!-- O QUE É: Link/Botão para configurações do sistema
      <!-- POR QUE ESTÁ ALI: Acesso rápido a configurações globais -->

      <?php endif; ?>
      <!-- O QUE É: Fim do condicional de permissão
      <!-- POR QUE ESTÁ ALI: Fecha o bloco de links que só admins veem -->

    </div>
    <!-- O QUE É: Fechamento do container flexbox
    <!-- POR QUE ESTÁ ALI: Finaliza a área de botões -->

  </div>
  <!-- O QUE É: Fechamento do cartão de acesso rápido
  <!-- POR QUE ESTÁ ALI: Termina o segundo cartão do grid -->

</div>
<!-- O QUE É: Fechamento do container grid
<!-- POR QUE ESTÁ ALI: Finaliza o layout de duas colunas -->

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<!-- O QUE É: Inclusão de arquivo de layout final
<!-- POR QUE ESTÁ ALI: Para carregar rodapé, scripts JavaScript finais e fechar tags HTML abertas no layout_start.php
<!-- __DIR__: Constante que retorna o diretório atual do arquivo -->
