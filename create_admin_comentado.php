<?php
/* ============================================================================
 * SUMÁRIO: Script de criação de usuário administrador inicial (31 linhas)
 * ----------------------------------------------------------------------------
 * DESTINO: Criar o primeiro usuário administrador no banco de dados com
 * credenciais padrão. Usado em instalação inicial ou ambiente de desenvolvimento.
 * ----------------------------------------------------------------------------
 * LINHA 14:       Abertura do motor PHP.
 * LINHAS 16-18:   Inclusão da conexão com o banco de dados.
 * LINHAS 20-22:   Definição do nome de usuário.
 * LINHAS 24-26:   Definição da senha em texto limpo.
 * LINHAS 28-31:   Criptografia de segurança da senha (Hashing).
 * LINHAS 33-35:   Preparação da instrução SQL protegida.
 * LINHAS 37-39:   Criação do objeto de declaração preparada (Statement).
 * LINHAS 41-43:   Execução da gravação dos dados no banco.
 * LINHAS 45-47:   Feedback visual da operação concluída.
 * LINHA 49:       Fechamento da tag PHP.
 * ============================================================================
 */

// O QUE É: Tag de abertura do motor PHP.
// POR QUE ESTÁ ALI: Indica ao servidor web que tudo o que vier a seguir deve ser interpretado como código PHP e não como texto simples ou HTML.

require_once 'includes/db.php';
// O QUE É: Instrução de inclusão de ficheiro externo com verificação de unicidade.
// POR QUE ESTÁ ALI: Este ficheiro contém a ligação à base de dados ($db). Está ali porque, sem ele, o script não conseguiria comunicar com o SQL para gravar o utilizador.

$username = 'admin';
// O QUE É: Declaração de uma variável de texto (string).
// POR QUE ESTÁ ALI: Define o nome de utilizador que queremos criar. Está isolado numa variável para facilitar a alteração no futuro sem ter de mexer na consulta SQL.

$password = 'admin123';
// O QUE É: Declaração de uma variável de texto (string).
// POR QUE ESTÁ ALI: Define a senha temporária em texto limpo. Está aqui apenas para ser processada pela linha seguinte antes de ser enviada ao banco.



$hashed_password = password_hash($password, PASSWORD_DEFAULT);
// O QUE É: Função de criptografia unidirecional (Hashing).
// POR QUE ESTÁ ALI: É uma medida crítica de segurança. Transforma 'admin123' numa sequência ilegível. Está ali para garantir que, se a base de dados for invadida, os hackers não descubram a senha real.

$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
// O QUE É: String contendo uma instrução SQL com parâmetros preparados (?).
// POR QUE ESTÁ ALI: Define a ordem de inserção na tabela 'users'. Os '?' servem como "espaços reservados" para evitar ataques de SQL Injection.

$stmt = $db->prepare($sql);
// O QUE É: Método de preparação de consulta (Statement).
// POR QUE ESTÁ ALI: Diz à base de dados para analisar a estrutura da consulta SQL antes de receber os dados, separando o comando dos dados para maior segurança.

$stmt->execute([$username, $hashed_password]);
// O QUE É: Método de execução da consulta com mapeamento de dados.
// POR QUE ESTÁ ALI: É o momento em que os dados reais são enviados para preencher os '?' e a gravação acontece efetivamente na base de dados.

echo "Administrador criado com sucesso!";
// O QUE É: Comando de saída de texto (impressão).
// POR QUE ESTÁ ALI: Fornece feedback visual ao programador. Sem isto, a página ficaria em branco e não saberíamos se o processo terminou corretamente.

?>
// O QUE É: Tag de fecho do motor PHP.
// POR QUE ESTÁ ALI: Indica o fim do código programável no arquivo.
