<?php
/* ============================================================================
 * SUMÁRIO: Script de criação de usuário administrador inicial (31 linhas)
 * ----------------------------------------------------------------------------
 * DESCRIÇÃO: Cria o primeiro usuário administrador no banco de dados com
 * credenciais padrão. Usado em instalação inicial ou ambiente de desenvolvimento.
 * ----------------------------------------------------------------------------
 * LINHAS 1-6:     Inicialização e conexão com banco de dados
 * LINHAS 7-12:    Definição das credenciais de acesso
 * LINHAS 13-15:   Criptografia de segurança da senha
 * LINHA 16:       Espaço em branco para separação visual
 * LINHAS 17-22:   Preparação da consulta SQL com proteção contra injection
 * LINHAS 23-25:   Execução da inserção no banco de dados
 * LINHAS 26-28:   Mensagem de confirmação da operação
 * LINHAS 29-31:   Fechamento do bloco PHP
 * ============================================================================
 */

// O QUE É: Tag de abertura do motor PHP.
// POR QUE ESTÁ ALI: Indica ao servidor web que tudo o que vier a seguir deve ser interpretado como código PHP e não como texto simples ou HTML.

// LINHAS 1-6: Conexão com banco de dados
require_once 'includes/db.php';
// O QUE É: Instrução de inclusão de ficheiro externo com verificação de unicidade.
// POR QUE ESTÁ ALI: Este ficheiro contém a ligação à base de dados ($db). Está ali porque, sem ele, o script não conseguiria comunicar com o SQL para gravar o utilizador. O 'once' evita erros se o ficheiro for chamado noutro lugar.

// LINHAS 7-12: Definição das credenciais do administrador
$username = 'admin';
// O QUE É: Declaração de uma variável de texto (string).
// POR QUE ESTÁ ALI: Define o nome de utilizador que queremos criar. Está isolado numa variável para facilitar a alteração no futuro sem ter de mexer na consulta SQL.

$password = 'admin123';
// O QUE É: Declaração de uma variável de texto (string).
// POR QUE ESTÁ ALI: Define a senha temporária em texto limpo. Está aqui apenas para ser processada pela linha seguinte antes de ser enviada ao banco.

// LINHAS 13-15: Criptografia da senha para segurança
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
// O QUE É: Função de criptografia unidirecional (Hashing).
// POR QUE ESTÁ ALI: É uma medida crítica de segurança. Transforma 'admin123' numa sequência ilegível. Está ali para garantir que, se a base de dados for invadida, os hackers não descubram a senha real.

// LINHA 16: Separação visual
// (espaço em branco)

// LINHAS 17-22: Preparação da consulta SQL segura
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
// O QUE É: String contendo uma instrução SQL com parâmetros preparados (?).
// POR QUE ESTÁ ALI: Define a ordem de inserção na tabela 'users'. Os '?' estão ali por segurança, servindo como "espaços reservados" para evitar ataques de SQL Injection.

$stmt = $db->prepare($sql);
// O QUE É: Método de preparação de consulta (Statement).
// POR QUE ESTÁ ALI: Diz à base de dados para analisar a estrutura da consulta SQL antes de receber os dados. Está ali para separar o "comando" dos "dados", o que é a base da segurança moderna em PHP.

// LINHAS 23-25: Execução da inserção no banco
$stmt->execute([$username, $hashed_password]);
// O QUE É: Método de execução da consulta com mapeamento de dados.
// POR QUE ESTÁ ALI: É o momento em que os dados reais ($username e $hashed_password) são enviados para preencher os '?' e a gravação acontece efetivamente na base de dados.

// LINHAS 26-28: Confirmação da operação
echo "Administrador criado com sucesso!";
// O QUE É: Comando de saída de texto (impressão).
// POR QUE ESTÁ ALI: Fornece feedback visual ao programador. Sem isto, a página ficaria em branco e não saberíamos se o processo terminou corretamente.

// LINHAS 29-31: Fechamento do bloco PHP
?>
// O QUE É: Tag de fecho do motor PHP.
// POR QUE ESTÁ ALI: Indica o fim do código programável. (Embora opcional em ficheiros puramente PHP, é usada aqui para delimitar o bloco).