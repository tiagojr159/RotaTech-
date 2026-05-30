# RotaTech Arcoverde — São João 2026

Aplicativo web mobile-first para o desafio de experiência integrada do São João de Arcoverde, desenvolvido em PHP + JSON com visual inspirado no Figma de referência.

## Tecnologias

- PHP 8+
- HTML5 + CSS3 + JavaScript puro
- JSON como banco de dados local
- Sessão PHP para autenticação
- PWA (`manifest.json` + `service-worker.js`)

## Como rodar

1. Entre na pasta do projeto:
   ```bash
   cd c:\xampp\htdocs\rota
   ```
2. Inicie o servidor embutido do PHP:
   ```bash
   php -S localhost:8000
   ```
3. Acesse:
   ```text
   http://localhost:8000
   ```

## Login de teste

- Email: `cordel@arcoverde.com`
- Senha: `123456`

## Estrutura resumida

- `index.php`: splash institucional
- `login.php` / `cadastro.php`: autenticação
- `home.php`, `programacao.php`, `restaurantes.php`, `album.php`, `roteiro.php`, `grupos.php`, `criar-grupo.php`, `detalhes-grupo.php`, `perfil.php`: telas principais
- `api.php`: ações AJAX via POST em JSON
- `includes/`: configuração, funções, header e navegação inferior
- `data/*.json`: armazenamento local dos dados do app
- `assets/css/style.css` e `assets/js/app.js`: UI e interações

## Persistência em JSON

Os arquivos em `data/` funcionam como banco de dados.  
Se não existirem, são criados automaticamente com dados de demonstração no primeiro acesso.

## PWA e instalação

1. Abra o app no navegador compatível (Chrome/Edge mobile ou desktop).
2. Use o banner **"Instalar app no celular"** quando aparecer.
3. Também é possível instalar pelo menu do navegador.

## Funcionalidades implementadas

- Login/logout com sessão
- Programação com filtros e favorito
- Restaurantes com busca e alternância lista/mapa
- Álbum de figurinhas com desbloqueio
- Roteiro pessoal e de grupo com adicionar/remover
- Grupos com convites, criação e entrada por código
- Perfil com edição
- Alertas e sugestão inteligente simulada

