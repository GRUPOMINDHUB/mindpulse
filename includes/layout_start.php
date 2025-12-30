<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║ LAYOUT_START.PHP — Início do Template Base de Todas as Páginas           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║                                                                           ║
 * ║ @objetivo      Fornecer estrutura HTML comum para todas as páginas       ║
 * ║                protegidas do sistema (após login)                        ║
 * ║                                                                           ║
 * ║ @acesso        Páginas protegidas (requer autenticação)                  ║
 * ║ @escopo        Global (estrutura comum)                                  ║
 * ║                                                                           ║
 * ║ @inclui        - Verificação de login (requireLogin)                     ║
 * ║                - Conexão com banco de dados                              ║
 * ║                - Estrutura HTML (head, body)                             ║
 * ║                - Header com navegação                                    ║
 * ║                - Sidebar com menu                                        ║
 * ║                - Container principal para conteúdo                       ║
 * ║                                                                           ║
 * ║ @uso           Incluir no início de cada página protegida:               ║
 * ║                require_once 'includes/layout_start.php';                 ║
 * ║                // Conteúdo da página aqui                                ║
 * ║                require_once 'includes/layout_end.php';                   ║
 * ║                                                                           ║
 * ║ @dependências  auth.php, db.php, header.php, sidebar.php                 ║
 * ║                                                                           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// ═══════════════════════════════════════════════════════════════════════════
// SEÇÃO: AUTENTICAÇÃO E DEPENDÊNCIAS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Inclui o arquivo de autenticação e EXIGE login
 * 
 * auth.php fornece: url_for(), isAdmin(), canAccessAdmin(), etc.
 * requireLogin() redireciona para /login.php se não autenticado
 * 
 * IMPORTANTE: Esta é a proteção principal das páginas!
 * Qualquer página que inclui layout_start.php está automaticamente protegida
 */
require_once __DIR__ . '/auth.php'; 
requireLogin();

/**
 * Inclui a conexão com o banco de dados
 * 
 * Após esta linha, a variável $pdo está disponível
 * para todas as queries no restante da página
 */
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<!-- ╔═══════════════════════════════════════════════════════════════════════╗
     ║ ESTRUTURA HTML BASE DO PAINEL                                         ║
     ║                                                                        ║
     ║ Layout: Header fixo + Sidebar + Área de conteúdo                      ║
     ║ Responsivo: Sidebar vira menu hambúrguer em mobile                    ║
     ╚═══════════════════════════════════════════════════════════════════════╝ -->
<head>
    <!-- ═══════════════════════════════════════════════════════════════════
         SEÇÃO: META TAGS ESSENCIAIS
         ═══════════════════════════════════════════════════════════════════ -->
    
    <!-- Codificação UTF-8 para suporte a acentos, emojis, etc. -->
    <meta charset="utf-8"/>
    
    <!-- Viewport para responsividade mobile
         width=device-width: largura igual à do dispositivo
         initial-scale=1: zoom inicial de 100% -->
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    
    <!-- Título da página (aparece na aba do navegador) -->
    <title>Mindhub — Painel</title>

    <!-- ═══════════════════════════════════════════════════════════════════
         SEÇÃO: FONTES EXTERNAS (Google Fonts)
         ═══════════════════════════════════════════════════════════════════ -->
    
    <!-- Preconnect: estabelece conexão antecipada (melhora performance) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonte Inter com múltiplos pesos para hierarquia tipográfica
         400: texto normal
         500: médio (subtítulos)
         600: semi-bold (labels)
         700: bold (títulos secundários)
         900: extra-bold (títulos principais) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <!-- ═══════════════════════════════════════════════════════════════════
         SEÇÃO: ESTILOS
         ═══════════════════════════════════════════════════════════════════ -->
    
    <!-- CSS global da aplicação (variáveis, componentes, utilitários) -->
    <link rel="stylesheet" href="<?= url_for('/assets/css/styles.css') ?>"/>

    <!-- ═══════════════════════════════════════════════════════════════════
         SEÇÃO: JAVASCRIPT GLOBAL
         ═══════════════════════════════════════════════════════════════════ -->
    
    <!-- Expõe BASE_URL para scripts JavaScript -->
    <script>window.BASE_URL="<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>";</script>
    
    <!-- Script principal da aplicação (carregado com defer para não bloquear) -->
    <script src="<?= url_for('/assets/js/app.js') ?>" defer></script>

    <!-- ═══════════════════════════════════════════════════════════════════
         SEÇÃO: ESTILOS ESPECÍFICOS DO LAYOUT
         
         Estes estilos são inline porque são essenciais para o layout
         e precisam estar disponíveis imediatamente (evita FOUC)
         ═══════════════════════════════════════════════════════════════════ -->
    <style>
        /* ═══════════════════════════════════════════════════════════════════
           VARIÁVEIS CSS DO LAYOUT
           ═══════════════════════════════════════════════════════════════════ */
        :root{ 
            /* Altura do header fixo - usada para calcular offsets */
            --mh-header-h:64px; 
        }

        /* ═══════════════════════════════════════════════════════════════════
           BODY: Configuração base
           ═══════════════════════════════════════════════════════════════════ */
        body{ 
            /* Fundo escuro (dark mode) */
            background:#0f1117; 
            
            /* Cor do texto padrão (branco suave) */
            color:#e8edf7; 
            
            /* Stack de fontes com fallbacks para cada sistema */
            font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif;
        }

        /* ═══════════════════════════════════════════════════════════════════
           APP-SHELL: Container principal da aplicação
           
           A sidebar é FIXED (fora do fluxo normal), então o shell
           não precisa de grid de 2 colunas - apenas display:block
           ═══════════════════════════════════════════════════════════════════ */
        .app-shell{ 
            min-height:100dvh;      /* Altura mínima = viewport dinâmica */
            display:block !important; /* Override de qualquer grid anterior */
        }

        /* ═══════════════════════════════════════════════════════════════════
           MH-CONTENT: Área principal de conteúdo
           
           Ocupa toda a largura disponível e considera:
           - Espaço do header fixo no topo
           - Espaço da sidebar fixa à esquerda (em desktop)
           ═══════════════════════════════════════════════════════════════════ */
        .mh-content{
            width:100%;
            padding:16px;
            /* Padding-top considera a altura do header + margem */
            padding-top:calc(var(--mh-header-h) + 12px);
        }
        
        /* Em desktop (>980px): adiciona espaço para a sidebar */
        @media (min-width:981px){
            .mh-content{ 
                /* 260px da sidebar + 16px de margem */
                padding-left:276px; 
            }
        }

        /* ═══════════════════════════════════════════════════════════════════
           .CONTENT: Container interno do conteúdo
           
           Reset de estilos que podem ter sido aplicados por CSS legado
           Garante que o conteúdo ocupe toda a largura disponível
           ═══════════════════════════════════════════════════════════════════ */
        .content{
            width:100% !important;
            max-width:none !important;
            margin:0 !important;
            padding:0 !important;
        }

        /* ═══════════════════════════════════════════════════════════════════
           .CARD: Componente de card padrão
           
           Cards ocupam toda a largura do container pai
           ═══════════════════════════════════════════════════════════════════ */
        .card{ 
            width:100%; 
        }

        /* ═══════════════════════════════════════════════════════════════════
           RESET DE CONTAINERS
           
           Remove limitações de largura que podem ter sido aplicadas
           por CSS legado ou frameworks externos
           ═══════════════════════════════════════════════════════════════════ */
        [class*="container"], [class*="wrapper"]{
            max-width:none !important;
        }
    </style>
</head>

<body>
    <!-- ╔═══════════════════════════════════════════════════════════════════╗
         ║ APP-SHELL: Container principal de toda a aplicação                ║
         ║                                                                    ║
         ║ Contém: Header + Sidebar + Área de conteúdo                       ║
         ╚═══════════════════════════════════════════════════════════════════╝ -->
    <div class="app-shell">

        <!-- ════════════════════════════════════════════════════════════════
             HEADER: Barra superior fixa
             
             Contém:
             - Logo/título da plataforma
             - Seletor de empresa (para usuários multi-empresa)
             - Avatar do usuário com menu dropdown
             - Botão hambúrguer (mobile)
             
             Incluído de arquivo separado para organização
             ════════════════════════════════════════════════════════════════ -->
        <?php include __DIR__ . '/header.php'; ?>
        
        <!-- ════════════════════════════════════════════════════════════════
             SIDEBAR: Menu lateral fixo
             
             Contém:
             - Logo da plataforma
             - Links de navegação organizados por seção
             - Seção "Colaborador": Treinamentos, Meus dados
             - Seção "Admin": Empresas, Colaboradores, Configurações
             - Seção "Sessão": Sair
             
             Em mobile: vira menu off-canvas (abre com hambúrguer)
             
             Incluído de arquivo separado para organização
             ════════════════════════════════════════════════════════════════ -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- ════════════════════════════════════════════════════════════════
             MH-CONTENT: Área principal de conteúdo
             
             Este é o container onde o conteúdo específico de cada página
             será inserido. O conteúdo fica entre layout_start e layout_end.
             
             Estrutura:
             - .mh-content: container externo (padding, responsividade)
             - .content: container interno (reset de estilos)
             ════════════════════════════════════════════════════════════════ -->
        <main class="mh-content">
            <div class="content">
                <!-- ════════════════════════════════════════════════════════
                     AQUI COMEÇA O CONTEÚDO ESPECÍFICO DE CADA PÁGINA
                     
                     O arquivo que inclui layout_start.php insere seu
                     conteúdo aqui, e depois inclui layout_end.php
                     para fechar as tags abertas.
                     
                     Exemplo de uso:
                     
                     <?php require_once 'includes/layout_start.php'; ?>
                     
                     <h1>Minha Página</h1>
                     <p>Conteúdo aqui...</p>
                     
                     <?php require_once 'includes/layout_end.php'; ?>
                     ════════════════════════════════════════════════════════ -->
