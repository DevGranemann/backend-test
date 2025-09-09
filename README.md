# API de Investimentos
API desenvolvida para agerenciar investimentos.

## Funcionalidades
- **Cadastro de Proprietários:** funcionalidade que permite cadastrar um usuário que será, futuramente, proprietário de investimentos;

- **Cadastro de Investimentos:** esta funcionalidade podemos criar um novo investimento para um determinado proprietário já cadastrado;

- **Listagem de Investimentos:** lista todos os investimentos feitos por um usuário e também o saldo atual de cada investimento. Possui paginação;

- **Saque:** esta funcionalidade permite o usuário retirar apenas o valor total de seu investimento, ou seja, o valor que investiu mais o lucro obtido. Lembrando que as taxas de impostos são aplicadas apenas no valor de lucros que aquele investimento gerou;

- **Listagem de Ganhos Retirados:** aqui obtemos uma lista de todos os investimentos que foram sacados pelo proprietário. Esta lista contém informações as informações de valor que aquele investimento gerou e a data do saque, por exemplo;

- **Projeção de Saldos Futuros:** com essa funcionalidades podemos observar melhor e estudar as projeções dos investimentos. É possível ``setar`` um valor em anos para melhor simulação.

## Estrutura do Repositório

```
src/
│
├── Migrations/
│    ├── VersionXXXXX.php -> Migrações que foram criadas e executadas
├── Controller/
│   ├── InvestmentController.php -> Controlador do Investment 
│   └── OwnerController.php      -> Controlador do Owner
├── Entity/
│   └── Investment.php           -> Objeto que representa Investment no BD
│   └── Owner.php                -> Objeto que representa Owner no BD     
├── Utils/
│   └──  InvestmentCalculator.php -> Possui os calculos do investimento  
│   └──  TakeInvestmentOut.php    -> Possui a lógica para saque dos investimentos
├── Tests/
│   ├── Utils/
│       └── InvestmentCalculatorTest.php -> Documento onde está os testes dos calculos do investimento "InvestmentCalculator.php"
```

## Exemplos dos Endpoints
A documentação está disponivél no ambiente local em http://localhost:8000/api/doc
### Owner Create
```
{
	"message": "Proprietário criado com sucesso.",
	"owner": {
		"id": 4,
		"name": "Maria da Silva"
	}
}
```
### Investment Create
```
{
	"message": "Investimento criado com sucesso.",
	"Investment": {
		"id": 26,
		"ownerId": 2,
		"ownerName": "João da Silva",
		"creationDate": "2025-07-03 00:00:00",
		"investmentValue": 16000
	}
}
```
### Invesment List 
```
{
	"page": 1,
	"limit": 5,
	"total": 25,
	"pages": 5,
	"investments": [
		{
			"id": 1,
			"ownerId": 1,
			"ownerName": "Eduardo da Silva",
			"creationDate": "2025-08-03 00:00:00",
			"investmentValue": 0,
			"valueWithWinnings": 0,
			"winningsValueOnly": 0
		},
		{
			"id": 2,
			"ownerId": 1,
			"ownerName": "Eduardo da Silva",
			"creationDate": "2025-07-03 00:00:00",
			"investmentValue": 0,
			"valueWithWinnings": 0,
			"winningsValueOnly": 0
		},
```
### Investment Draw
```
{
	"message": "Saque realizado com sucesso",
	"withdrawValue": 127018.2,
	"investmentId": 28,
	"ownerId": 1,
	"ownerName": "Eduardo da Silva"
}
```
### Withdraw Gains
```
{
	"withdrawnGains": [
		{
			"investmentId": 1,
			"withdrawnAt": "2025-09-06 14:33:50",
			"profit": null,
			"ownerId": 1,
			"ownerName": "Eduardo da Silva"
		},
		{
			"investmentId": 2,
			"withdrawnAt": "2025-09-06 18:44:41",
			"profit": 20.85,
			"ownerId": 1,
			"ownerName": "Eduardo da Silva"
		},
		{
			"investmentId": 3,
			"withdrawnAt": "2025-09-06 19:15:12",
			"profit": 41.71,
			"ownerId": 1,
			"ownerName": "Eduardo da Silva"
		},
```
### Future Balances
```
{
	"investmentId": 5,
	"ownerId": 1,
	"ownerName": "Eduardo da Silva",
	"years": 5,
	"projections": [
		{
			"year": 0,
			"date": "2025-07-03",
			"expectedBalance": 6000
		},
		{
			"year": 1,
			"date": "2026-07-03",
			"expectedBalance": 6385.3
		},
		{
			"year": 2,
			"date": "2027-07-03",
			"expectedBalance": 6795.33
		},
```

## Ferramentas e Tecnologias Utilizadas:
- PHP 8.4.12;
- Symfony 5.12.0;
- BD MySQL (por linha de comando);
- Doctrine ORM;
- Insomnia (para testar as rotas);
- NelmioApiDocBundle (Documentação da API);
- PHPUnit (testes unitários);

## Decisão das Tecnologias

- **Symfony**: decidi utilizar o Symfony pela sua robustez, pela organização por ser MVC e também pela sua modularidade;
- **MySQL**: pela praticidade e rapidez: por linha de comando tem acesso total ao banco, comandos simples. Portabilidade: qualquer servidor com MySQL terá o cliente     CLI disponível — garante que você consegue administrar em qualquer ambiente;
- **Abstração do SQL**: em vez de escrever queries manualmente, podemos trabalhar com objetos PHP e deixa o Doctrine gerar as queries. Produtividade: criação automática de tabelas/mapeamento via migrations. Compatibilidade: caso seja necessário trocar MySQL por PostgreSQL, por exemplo, o Doctrine adapta as queries;

## Quer Testar Localmente Este Projeto?

Este guia descreve os passos necessários para rodar a API em um ambiente LOCAL:

### Pré-requisitos

Antes de começar, verifique se você possui as seguintes ferramentas instaladas no seu sistema:

- [PHP 8+](https://www.php.net/downloads.php)  
- [Composer](https://getcomposer.org/download/)  
- [MySQL](https://dev.mysql.com/downloads/)  
- [Symfony CLI](https://symfony.com/download) (opcional, mas recomendado)  
- [Insomnia](https://insomnia.rest/download) (para testar as rotas)


### Passos para rodar a API localmente

### 1. Clonar o repositório
```bash
git clone https://github.com/seu-usuario/api-investimentos.git
cd api-investimentos
```
### 2. Instalar as dependências
```bash
	composer install
```

### 3. Configurar as variáveis de ambiente
Crie o arquivo .env.local na raiz do projeto e configure a conexão com o banco de dados MySQL:
```bash
	DATABASE_URL="mysql://usuario:senha@127.0.0.1:3306/nome_do_banco"
```

### 4. Criar o BD e rodar as migrations

```bash
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate
```

### 5. Rodar o servidor local
Com Symfony CLI:
```bash
symfony server:start
```
Ou 
```bash
symfony serve
```
### 6. Testar a API com o Insomnia
Abra o Insomnia e configure as rotas da API. Exemplo
```bash
GET http://127.0.0.1:8000/api/investments
```
## Cobertura dos Testes Unitários

A API conta com uma suíte de testes unitários implementada com **PHPUnit**, garantindo a confiabilidade dos cálculos e projeções de investimento.  
Os testes cobrem os seguintes cenários:

### InvestmentCalculatorTest
- **Cálculo de investimento válido**  
  - Verifica se o saldo final do investimento é calculado corretamente com base nos meses decorridos e na taxa de rendimento.

- **Tratamento de exceções**  
  - Lança exceção caso a data de criação do investimento seja `null`.  
  - Lança exceção caso a data de criação esteja em formato inválido.

- **Cálculo de meses entre datas**  
  - Valida o cálculo da diferença em meses entre duas datas distintas.

- **Projeção de saldos futuros**  
  - Geração da projeção de crescimento do investimento para os próximos anos (padrão: 3 anos além do ano inicial).  
  - Valida a estrutura do array retornado (`year`, `date`, `expectedBalance`).  
  - Suporta número customizado de anos, verificando se a projeção é calculada corretamente até o ano especificado.

## Autenticação

A API utiliza **API Key Authenticator** para proteger as rotas

- O cliente deve enviar a chave de API em cada requisição, através do header HTTP:

```http
GET/ api/ 
X_API_KEY: senha
```

- Caso a chave seja inválido ou ausente, a API retornará:
  ```bash
	{
		"error": "Chave inválida"
	}
  ```
