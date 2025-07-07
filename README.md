
## 👤 Autores
- Nome: Roberto Henrique Duarte, João Victor Costa Arruda

# 🛒 Ecomerce - Loja Virtual em PHP

Este repositório contém um sistema de e-commerce desenvolvido com PHP. A aplicação permite que usuários visualizem produtos, adicionem itens ao carrinho, realizem cadastros, façam login e acompanhem seus pedidos. Também há suporte à criação de administradores.

Este sistema foi desenvolvido com base nos **conceitos, exemplos e práticas abordados durante as aulas de Programação II**, incluindo:

- Programação estruturada e modular em PHP
- Integração com banco de dados MySQL
- Manipulação de formulários e sessões
- Conceitos de CRUD aplicados ao comércio eletrônico

Todo o código-fonte foi elaborado pelo grupo com base no conteúdo das aulas, respeitando os critérios pedagógicos e técnicos da disciplina.

## 🚀 Funcionalidades

- Cadastro e login de usuários
- Visualização de produtos
- Adição de produtos ao carrinho
- Finalização de pedidos
- Visualização de pedidos realizados
- Logout
- Criação de usuário administrador

## 📁 Estrutura de Arquivos

- `index.php` - Página inicial da loja
- `produto.php` - Exibição dos detalhes de um produto
- `carrinho.php` - Gerenciamento do carrinho de compras
- `finalizar.php` - Conclusão da compra
- `pedidos.php` - Histórico de pedidos do usuário
- `cadastro.php` - Formulário de cadastro de usuário
- `login.php` - Tela de login
- `logout.php`, `logout-sucesso.php` - Logout do sistema
- `CriarAdmin.php` - Script para criação de administrador

## 🧰 Tecnologias Utilizadas

- PHP
- HTML/CSS (possivelmente com Bootstrap)
- Banco de dados (ex: MySQL) — *[não incluso no ZIP, adicionar instruções se necessário]*
- Git

## ⚙️ Como Executar

1. Clone o repositório ou extraia o `.zip`:
   ```bash
   git clone https://github.com/seu-usuario/seu-repositorio.git
2. Mova os arquivos para o diretório raiz do seu servidor local:
   - Exemplo com XAMPP: `C:\xampp\htdocs\Ecomerce`
   - Exemplo com WAMP: `C:\wamp64\www\Ecomerce`

3. Inicie o servidor Apache e o MySQL através do painel do XAMPP ou WAMP.

4. Crie o banco de dados:
   - Acesse o **phpMyAdmin** através de `http://localhost/phpmyadmin`
   - Crie um banco de dados chamado, por exemplo, `ecomerce`
   - Importe o arquivo `.sql` correspondente (se fornecido)

5. Verifique e configure a conexão com o banco de dados nos arquivos PHP (geralmente em um arquivo de configuração, ex: `config.php` ou direto nos scripts PHP)

6. Acesse o sistema no navegador:
http://localhost/Ecomerce/index.php

## 🛠️ Configuração do Banco de Dados
- Acesse o phpMyAdmin:
http://localhost/phpmyadmin
 
- Crie um novo banco de dados com o nome:
ecomerce

- Importe o arquivo SQL com a estrutura do banco: 
    - Vá até a aba Importar
    - Selecione o arquivo ``ecommerce.sql``, na pasta sql.
    - Clique em Executar
    - Verifique o arquivo de conexão com o banco no seu projeto (ex: conexao.php) e atualize as credenciais, se necessário:
    ```php
    $host = 'localhost';
    $usuario = 'root';
    $senha = '';
    $banco = 'ecomerce';
    ```










## 🔐 Observações Importantes

- A criação de administrador deve ser feita apenas uma vez, acessando diretamente o arquivo:
http://localhost/Ecomerce/CriarAdmin.php


- Após criar o administrador, **recomenda-se remover ou restringir o acesso a `CriarAdmin.php`** por segurança.

- Certifique-se de que as permissões de escrita/leitura estejam corretas se o sistema estiver sendo executado em ambiente Linux.

## 🧪 Sugestões de Melhorias Futuras

- Implementar sistema de recuperação de senha
- Adicionar autenticação baseada em sessões de forma mais robusta
- Separar camadas de apresentação e lógica (MVC)
- Implementar painel administrativo para gerenciar produtos e pedidos
- Responsividade com CSS moderno (Bootstrap 5 ou Tailwind)
- Proteção contra SQL Injection com uso de PDO ou MySQLi com prepared statements





