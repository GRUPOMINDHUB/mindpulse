# 🧠 Mindpulse (Mindhub)

> **Plataforma SaaS de Treinamento, Checklists e Feedback para Gestão de Equipes**

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)

---

## 📋 Índice

- [Visão Geral](#-visão-geral)
- [Funcionalidades](#-funcionalidades)
- [Arquitetura](#-arquitetura)
- [Instalação](#-instalação)
- [Estrutura de Pastas](#-estrutura-de-pastas)
- [Banco de Dados](#-banco-de-dados)
- [Autenticação e Permissões](#-autenticação-e-permissões)
- [Módulos do Sistema](#-módulos-do-sistema)
- [API Endpoints](#-api-endpoints)
- [Guia de Desenvolvimento](#-guia-de-desenvolvimento)
- [Convenções de Código](#-convenções-de-código)

---

## 🎯 Visão Geral

**Mindpulse** (também conhecido como **Mindhub**) é uma plataforma web multi-tenant para gestão de equipes, focada em:

- 📚 **Treinamentos corporativos** com vídeos e gamificação
- ✅ **Checklists operacionais** com frequências variadas
- 💬 **Canal de feedback** entre colaboradores e gestão
- 📊 **Dashboard administrativo** com KPIs e rankings

### Características Principais

| Característica | Descrição |
|----------------|-----------|
| **Multi-tenant** | Suporte a múltiplas empresas com isolamento de dados |
| **Role-based Access** | Controle de acesso baseado em cargos |
| **Gamificação** | Recompensas, badges e progresso visual |
| **Mobile-first** | Interface responsiva otimizada para dispositivos móveis |
| **Dark Mode** | Interface escura moderna e confortável |

---

## ✨ Funcionalidades

### 👤 Para Colaboradores

- ✅ Assistir treinamentos em vídeo (YouTube, Vimeo, Cloudflare, Mux)
- ✅ Acompanhar progresso e conquistar recompensas
- ✅ Executar checklists diários, semanais e mensais
- ✅ Enviar feedback para a gestão
- ✅ Visualizar histórico de atividades

### 👔 Para Gestores

- ✅ Criar e gerenciar treinamentos
- ✅ Configurar checklists com tarefas e prioridades
- ✅ Monitorar progresso da equipe
- ✅ Responder feedbacks dos colaboradores
- ✅ Visualizar rankings e métricas

### 🔧 Para Administradores

- ✅ Gerenciar múltiplas empresas
- ✅ Criar e editar colaboradores
- ✅ Configurar cargos e permissões
- ✅ Dashboard com KPIs globais
- ✅ Gráficos de crescimento mensal

---

## 🏗️ Arquitetura

### Stack Tecnológico

```
┌─────────────────────────────────────────────────────────┐
│                      FRONTEND                           │
├─────────────────────────────────────────────────────────┤
│  HTML5 + CSS3 (Custom Properties) + JavaScript (ES6+)  │
│  Responsivo (Mobile-first) • Dark Mode • Sem frameworks │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                      BACKEND                            │
├─────────────────────────────────────────────────────────┤
│  PHP 8.0+ (Puro, sem framework)                        │
│  PDO com Prepared Statements • Sessions • JSON API     │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                     DATABASE                            │
├─────────────────────────────────────────────────────────┤
│  MySQL 8.0+ / MariaDB 10.5+                            │
│  InnoDB • utf8mb4 • Foreign Keys                       │
└─────────────────────────────────────────────────────────┘
```

### Padrão de Arquitetura

O projeto segue uma arquitetura **monolítica modular**:

- **Páginas PHP**: Renderizam HTML diretamente
- **Includes**: Funções reutilizáveis e componentes
- **Endpoints AJAX**: Retornam JSON para operações assíncronas

---

## 🚀 Instalação

### Pré-requisitos

- PHP 8.0 ou superior
- MySQL 8.0 / MariaDB 10.5 ou superior
- Servidor web (Apache/Nginx) ou XAMPP/WAMP
- Extensões PHP: `pdo_mysql`, `json`, `session`

### Passo a Passo

1. **Clone ou copie o projeto**
   ```bash
   git clone <repo-url> /var/www/html/MINDPULSE
   # ou para XAMPP:
   # Copie para C:\xampp\htdocs\MINDPULSE
   ```

2. **Crie o banco de dados**
   ```sql
   CREATE DATABASE mindpulse 
   CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;
   ```

3. **Execute o schema inicial**
   ```bash
   mysql -u root -p mindpulse < schema.sql
   ```

4. **Configure as credenciais**
   
   Edite `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'mindpulse');
   define('DB_USER', 'root');
   define('DB_PASS', 'sua_senha');
   ```

5. **Crie o usuário admin**
   ```bash
   php create_admin.php
   ```
   
   Ou acesse diretamente no navegador:
   ```
   http://localhost/MINDPULSE/create_admin.php
   ```

6. **Acesse a aplicação**
   ```
   http://localhost/MINDPULSE
   ```
   
   **Credenciais padrão:**
   - Email: `admin@mindhub.com`
   - Senha: `admin123`

---

## 📁 Estrutura de Pastas

```
MINDPULSE/
│
├── 📄 index.php              # Ponto de entrada (redireciona para login)
├── 📄 login.php              # Página de login
├── 📄 home.php               # Home alternativa (legado)
├── 📄 create_admin.php       # Script para criar usuário admin
├── 📄 schema.sql             # Schema inicial do banco de dados
├── 📄 README.md              # Este arquivo
│
├── 📂 includes/              # Arquivos incluídos (core do sistema)
│   ├── config.php            # Configurações globais (DB, URLs)
│   ├── db.php                # Conexão PDO e funções de dados
│   ├── auth.php              # Autenticação e autorização
│   ├── training.php          # Funções de treinamentos
│   ├── checklist.php         # Funções de checklists
│   ├── feedback.php          # Funções de feedback
│   ├── layout_start.php      # Template base (início)
│   ├── layout_end.php        # Template base (fim)
│   ├── header.php            # Cabeçalho fixo
│   ├── sidebar.php           # Menu lateral (desktop)
│   ├── menu.php              # Menu alternativo (legado)
│   └── menu_items.php        # Itens de navegação
│
├── 📂 auth/                  # Endpoints de autenticação
│   ├── do_login.php          # Processa login
│   ├── logout.php            # Encerra sessão
│   └── switch_company.php    # Troca empresa ativa
│
├── 📂 pages/                 # Páginas da aplicação
│   │
│   │── 📄 home.php           # Dashboard do colaborador
│   │
│   │── # TREINAMENTOS
│   ├── treinamentos.php      # Lista de treinamentos
│   ├── treinamento.php       # Player de treinamento
│   ├── training_new.php      # Criar treinamento (admin)
│   ├── training_save.php     # Salvar treinamento
│   ├── training_complete_video.php  # API: marcar vídeo
│   ├── training_finalize.php # API: finalizar treinamento
│   │
│   │── # CHECKLISTS
│   ├── checklists.php        # Lista de checklists
│   ├── checklist_run.php     # Executar checklist
│   ├── checklist_new.php     # Criar checklist (admin)
│   ├── checklist_save.php    # Salvar checklist
│   ├── checklist_toggle.php  # API: marcar/desmarcar tarefa
│   │
│   │── # FEEDBACK
│   ├── feedback.php          # Enviar feedback
│   ├── feedback_submit.php   # API: salvar feedback
│   ├── chamados.php          # Lista de chamados (admin)
│   ├── chamados_update.php   # API: atualizar status
│   │
│   │── # ADMINISTRAÇÃO
│   ├── admin_dashboard.php   # Dashboard administrativo
│   ├── empresas.php          # Gerenciar empresas
│   ├── company_save.php      # API: salvar empresa
│   ├── colaboradores.php     # Lista de colaboradores
│   ├── collaborator_new.php  # Criar colaborador
│   ├── collaborator_save.php # Salvar colaborador
│   ├── usuarios.php          # Gerenciar usuários
│   ├── config.php            # Configurações
│   │
│   │── # PERFIL
│   ├── meus_dados.php        # Dados do usuário
│   └── recompensas.php       # Recompensas conquistadas
│
└── 📂 assets/                # Arquivos estáticos
    ├── 📂 css/
    │   └── styles.css        # Estilos globais
    ├── 📂 js/
    │   └── app.js            # JavaScript global
    └── 📂 img/
        ├── logo.png          # Logo da plataforma
        ├── avatar.svg        # Avatar padrão
        ├── login_hero.svg    # Ilustração do login
        └── login_story.jpg   # Background do login
```

---

## 🗄️ Banco de Dados

### Diagrama de Relacionamentos

```
┌─────────────┐       ┌─────────────────┐       ┌─────────────┐
│   users     │───────│  user_company   │───────│  companies  │
└─────────────┘       └─────────────────┘       └─────────────┘
      │                                               │
      │               ┌─────────────────┐             │
      └───────────────│   user_role     │             │
                      └─────────────────┘             │
                             │                        │
                      ┌──────┴──────┐                 │
                      │    roles    │                 │
                      └──────┬──────┘                 │
                             │                        │
              ┌──────────────┴──────────────┐        │
              │                             │        │
       ┌──────┴──────┐              ┌───────┴────────┴───────┐
       │role_training│              │      checklists        │
       └──────┬──────┘              └───────────┬────────────┘
              │                                 │
       ┌──────┴──────┐              ┌───────────┴────────────┐
       │  trainings  │              │    checklist_tasks     │
       └──────┬──────┘              └───────────┬────────────┘
              │                                 │
       ┌──────┴──────────┐          ┌───────────┴────────────┐
       │training_videos  │          │  checklist_task_done   │
       └──────┬──────────┘          └────────────────────────┘
              │
       ┌──────┴──────────┐
       │user_video_progress│
       └─────────────────┘
```

### Tabelas Principais

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários do sistema (Admin/Colaborador) |
| `companies` | Empresas cadastradas |
| `roles` | Cargos/funções disponíveis |
| `user_company` | Vínculo usuário ↔ empresa |
| `user_role` | Vínculo usuário ↔ cargo |
| `trainings` | Treinamentos cadastrados |
| `training_videos` | Vídeos/aulas dos treinamentos |
| `user_video_progress` | Progresso do usuário em vídeos |
| `user_training_reward` | Recompensas conquistadas |
| `checklists` | Checklists cadastrados |
| `checklist_tasks` | Tarefas dos checklists |
| `checklist_task_done` | Marcações de tarefas |
| `feedback_tickets` | Tickets de feedback |

### Frequências de Checklists

| Código | Descrição | Period Key |
|--------|-----------|------------|
| `daily` | Diário | `2025-01-15` |
| `weekly` | Semanal | `2025-W03` |
| `biweekly` | Quinzenal | `2025-B02` |
| `monthly` | Mensal | `2025-01` |

---

## 🔐 Autenticação e Permissões

### Níveis de Usuário

```
┌─────────────────────────────────────────────────────────────┐
│                     ADMIN GERAL                             │
│  • Acesso total a todas as empresas                        │
│  • Pode criar/editar empresas, usuários, treinamentos      │
│  • Vê dashboard global com KPIs de todas as empresas       │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                       GESTOR                                │
│  • Acesso administrativo à SUA empresa                     │
│  • Pode criar treinamentos, checklists, colaboradores      │
│  • Vê métricas e feedbacks da sua equipe                   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    COLABORADOR                              │
│  • Acesso de execução apenas                               │
│  • Assiste treinamentos, executa checklists                │
│  • Envia feedbacks para a gestão                           │
└─────────────────────────────────────────────────────────────┘
```

### Funções de Autorização

```php
// Verifica se está logado (redireciona se não)
requireLogin();

// Verifica se é Admin
if (isAdmin()) { ... }

// Verifica se pode acessar área administrativa
if (canAccessAdmin()) { ... }

// Obtém ID da empresa atual
$companyId = currentCompanyId();
```

### Fluxo de Autenticação

```
┌──────────┐     ┌──────────────┐     ┌─────────────┐
│  login   │────▶│  do_login    │────▶│  Sessão     │
│  .php    │     │  .php        │     │  criada     │
└──────────┘     └──────────────┘     └─────────────┘
                        │
                        ▼
              ┌─────────────────┐
              │ $_SESSION =     │
              │ • user          │
              │ • companies     │
              │ • roles         │
              │ • current_company│
              └─────────────────┘
```

---

## 📦 Módulos do Sistema

### 1. Módulo de Treinamentos

**Arquivos:**
- `includes/training.php` — Funções core
- `pages/treinamentos.php` — Lista de treinamentos
- `pages/treinamento.php` — Player de vídeo
- `pages/training_new.php` — Formulário de criação
- `pages/training_save.php` — Processamento do formulário
- `pages/training_complete_video.php` — API de progresso
- `pages/training_finalize.php` — API de finalização

**Providers de Vídeo Suportados:**
- YouTube (youtube.com, youtu.be)
- Vimeo
- Cloudflare Stream
- Mux Video (HLS)
- MP4 direto

**Gamificação:**
- Barra de progresso por treinamento
- Contador de aulas concluídas
- Recompensa (badge) ao finalizar
- Galeria de recompensas conquistadas

### 2. Módulo de Checklists

**Arquivos:**
- `includes/checklist.php` — Funções core
- `pages/checklists.php` — Lista de checklists
- `pages/checklist_run.php` — Execução de checklist
- `pages/checklist_new.php` — Formulário de criação
- `pages/checklist_save.php` — Processamento
- `pages/checklist_toggle.php` — API de marcação

**Frequências:**
- Diário (`daily`)
- Semanal (`weekly`)
- Quinzenal (`biweekly`)
- Mensal (`monthly`)

**Conceito de Período:**
```php
// Chave única para cada período
$periodKey = period_key_for('daily');  // "2025-01-15"
$periodKey = period_key_for('weekly'); // "2025-W03"
```

### 3. Módulo de Feedback

**Arquivos:**
- `includes/feedback.php` — Funções core
- `pages/feedback.php` — Formulário de envio
- `pages/feedback_submit.php` — API de envio
- `pages/chamados.php` — Lista para admin
- `pages/chamados_update.php` — API de atualização

**Sentimentos Disponíveis:**
| Emoji | Chave | Score |
|-------|-------|-------|
| 😊 | `happy` | 5 |
| 🙂 | `good` | 4 |
| 😐 | `neutral` | 3 |
| 😟 | `worried` | 2 |
| 😢 | `sad` | 1 |

**Status de Tickets:**
- `aberto` — Aguardando resposta
- `em_andamento` — Em análise
- `resolvido` — Finalizado

### 4. Módulo Administrativo

**Dashboard (`admin_dashboard.php`):**
- KPIs: empresas, usuários, treinamentos, checklists
- Gráfico de crescimento mensal
- Ranking de usuários por recompensas
- Ranking de empresas por atividade

**Gestão de Empresas (`empresas.php`):**
- Lista de empresas do usuário
- Slide-over para criar nova empresa
- Busca por nome

**Gestão de Colaboradores (`colaboradores.php`):**
- Lista de colaboradores da empresa
- Informações de cargo e recompensas
- Aniversariantes do mês

---

## 🔌 API Endpoints

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/do_login.php` | Processar login |
| GET | `/auth/logout.php` | Encerrar sessão |
| POST | `/auth/switch_company.php` | Trocar empresa |

### Treinamentos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/pages/training_complete_video.php` | Marcar vídeo como visto |
| POST | `/pages/training_finalize.php` | Finalizar treinamento |
| POST | `/pages/training_save.php` | Criar treinamento |

### Checklists

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/pages/checklist_toggle.php` | Marcar/desmarcar tarefa |
| POST | `/pages/checklist_save.php` | Criar checklist |

### Feedback

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/pages/feedback_submit.php` | Enviar feedback |
| POST | `/pages/chamados_update.php` | Atualizar status |

### Empresas

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/pages/company_save.php` | Criar empresa |

### Colaboradores

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/pages/collaborator_save.php` | Criar colaborador |

### Formato de Resposta

Todos os endpoints AJAX retornam JSON:

```json
// Sucesso
{
  "status": "ok",
  "data": { ... }
}

// Erro
{
  "status": "error",
  "message": "Descrição do erro"
}
```

---

## 💻 Guia de Desenvolvimento

### Requisições AJAX

Use a função global `mhPostJSON`:

```javascript
const res = await mhPostJSON('/pages/endpoint.php', {
  param1: 'valor1',
  param2: 'valor2'
});

if (res.status === 'ok') {
  // Sucesso
} else {
  alert(res.message);
}
```

### Criando Nova Página

1. **Crie o arquivo em `/pages/`**
   ```php
   <?php
   require_once __DIR__ . '/../includes/layout_start.php';
   // Seu código aqui
   require_once __DIR__ . '/../includes/layout_end.php';
   ```

2. **Adicione ao menu** em `includes/menu_items.php`

3. **Proteja com permissões** se necessário:
   ```php
   if (!canAccessAdmin()) {
     http_response_code(403);
     exit('Acesso negado');
   }
   ```

### Criando Endpoint AJAX

```php
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sua lógica aqui
    
    while (ob_get_level()) ob_end_clean();
    echo json_encode(['status' => 'ok']);
} catch (Throwable $e) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
```

### Trabalhando com Banco de Dados

```php
// Sempre use prepared statements
$st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$st->execute([$userId]);
$user = $st->fetch();

// Para inserções idempotentes
$pdo->prepare("
    INSERT INTO tabela (col1, col2) VALUES (?, ?)
    ON DUPLICATE KEY UPDATE col2 = VALUES(col2)
")->execute([$val1, $val2]);

// Para transações
try {
    $pdo->beginTransaction();
    // operações
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## 📝 Convenções de Código

### PHP

- **Indentação:** 4 espaços
- **Nomes de funções:** `camelCase`
- **Nomes de variáveis:** `$camelCase`
- **Constantes:** `UPPER_SNAKE_CASE`
- **Arquivos:** `snake_case.php`

### CSS

- **Variáveis:** `--mh-nome-variavel`
- **Classes:** `mh-nome-classe` (prefixo mh = mindhub)
- **BEM simplificado:** `.componente`, `.componente-parte`

### JavaScript

- **Funções globais:** `mhNomeFuncao`
- **Variáveis locais:** `camelCase`
- **Constantes:** `UPPER_SNAKE_CASE`

### Comentários

Todos os arquivos devem ter:

1. **Cabeçalho com ASCII box** explicando o propósito
2. **Seções demarcadas** com `═══════`
3. **Comentários inline** para lógica complexa

```php
/**
 * ╔═══════════════════════════════════════════════════════════════╗
 * ║ NOME_ARQUIVO.PHP — Descrição Breve                           ║
 * ╠═══════════════════════════════════════════════════════════════╣
 * ║ @objetivo      O que este arquivo faz                        ║
 * ║ @acesso        Quem pode acessar                             ║
 * ║ @dependências  Arquivos necessários                          ║
 * ╚═══════════════════════════════════════════════════════════════╝
 */
```

---

## 🎨 Design System

### Cores

| Variável | Hex | Uso |
|----------|-----|-----|
| `--mh-brand` | `#ff6a00` | Cor primária (laranja) |
| `--mh-brand2` | `#ff9153` | Cor secundária |
| `--mh-bg` | `#0f1117` | Fundo principal |
| `--mh-panel` | `#141824` | Fundo de painéis |
| `--mh-text` | `#e8edf7` | Texto principal |
| `--mh-muted` | `#9aa4b2` | Texto secundário |
| `--mh-stroke` | `rgba(255,255,255,.12)` | Bordas |

### Componentes

- `.card` — Container com borda e padding
- `.button` — Botão primário
- `.button.ghost` — Botão transparente
- `.badge` — Tag/etiqueta
- `.progress` — Barra de progresso
- `.kpi` — Card de indicador

### Breakpoints

| Breakpoint | Largura | Comportamento |
|------------|---------|---------------|
| Mobile | ≤ 420px | Layout empilhado |
| Tablet | ≤ 720px | 2 colunas |
| Desktop | ≤ 980px | Sidebar off-canvas |
| Wide | > 980px | Sidebar fixa |

---

## 📄 Licença

Este projeto é **proprietário** e de uso exclusivo da empresa desenvolvedora.

---

## 👥 Equipe

Desenvolvido com ❤️ pela equipe Mindpulse.

---

*Última atualização: Dezembro 2024*



