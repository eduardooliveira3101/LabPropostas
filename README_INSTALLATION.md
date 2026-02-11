# Instalação e Uso

Este arquivo descreve como instalar as dependências do projeto e como executar a aplicação localmente.

## Requisitos

- PHP 7.4 ou superior (recomendado)
- Composer (https://getcomposer.org)
- Extensões PHP recomendadas: `mbstring`, `fileinfo`, `gd` (dependência do DOMPDF)

## Instalação de dependências

1. Abra um terminal na raiz do projeto.
2. Rode:

```
composer install
```

Isto irá instalar as dependências listadas em `composer.json` (ex.: `dompdf/dompdf`, `phpmailer/phpmailer`).

> Se você já tem a pasta `vendor/` com dependências (ex.: a pasta foi incluída no repositório), este passo pode não ser necessário.

## Configuração de e-mail

Edite o arquivo `config-email.php` e ajuste as credenciais SMTP/endereços para o seu ambiente. O arquivo contém a configuração usada por `teste-email.php` e pelas rotinas de envio.

IMPORTANTE: `config-email.php` pode conter segredos (usuário/senha). Não comite este arquivo em repositórios públicos.

## Executando localmente

Opções rápidas para testes locais:

- Servidor embutido do PHP (na raiz do projeto):

```
php -S localhost:8000
```

Em seguida abra `http://localhost:8000/index.html` no navegador.

## Gerando PDF

O projeto usa `dompdf` para gerar PDFs (veja `template-pdf.php`). Após instalar dependências com `composer install`, as funções de PDF estarão disponíveis.

## Testes

Há alguns scripts de teste em `tests/`. Eles não dependem de um runner específico neste repositório — para testes manuais abra os arquivos em `tests/` e execute conforme descrição interna.

## Observações de segurança

- Nunca comite credenciais; mantenha `config-email.php` fora do controle de versão.
- Se for necessário versionar uma configuração, prefira um `config-email.example.php` sem credenciais.

---

Arquivo criado automaticamente para documentação de instalação e uso local.
