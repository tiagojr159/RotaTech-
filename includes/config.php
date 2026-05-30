<?php
declare(strict_types=1);

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit('Acesso negado.');
}

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

date_default_timezone_set('America/Fortaleza');
session_start();

define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'data');
define('UPLOADS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('ASSETS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'assets');
define('APP_NAME', 'RotaTech Arcoverde');
define('APP_BASE_URL', '/rotatech/');
define('APP_ABSOLUTE_URL', 'https://ki6.com.br/rotatech/');
define('MASTER_USER_EMAIL', 'tiagojr159@hotmail.com');

if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0775, true);
}
if (!is_dir(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0775, true);
}

function seedUsers(): array
{
    $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);

    return [
        [
            'id' => 1,
            'nome' => 'João Silva',
            'usuario' => 'joaosilva_02',
            'email' => 'cordel@arcoverde.com',
            'senha_hash' => $defaultPassword,
            'avatar' => 'assets/img/avatar-joao.svg',
            'nivel' => 12,
            'titulo' => 'EXPLORADOR DE ARCOVERDE',
            'pontos' => 850,
            'criado_em' => '2026-06-01',
            'favoritos' => [1, 2],
            'figurinhas' => [1],
        ],
        [
            'id' => 2,
            'nome' => 'Maria Clara',
            'usuario' => 'mariaclaraforro',
            'email' => 'maria@arcoverde.com',
            'senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
            'avatar' => 'assets/img/avatar-maria.svg',
            'nivel' => 10,
            'titulo' => 'GUIA DO ARRAIAL',
            'pontos' => 680,
            'criado_em' => '2026-06-02',
            'favoritos' => [],
            'figurinhas' => [1, 2],
        ],
        [
            'id' => 3,
            'nome' => 'Lucas',
            'usuario' => 'lucasforro',
            'email' => 'lucas@arcoverde.com',
            'senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
            'avatar' => 'assets/img/avatar-lucas.svg',
            'nivel' => 11,
            'titulo' => 'MESTRE DA SANFONA',
            'pontos' => 760,
            'criado_em' => '2026-06-02',
            'favoritos' => [3],
            'figurinhas' => [1, 3],
        ],
        [
            'id' => 4,
            'nome' => 'Beatriz Souza',
            'usuario' => 'bia_festiva',
            'email' => 'bia@arcoverde.com',
            'senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
            'avatar' => 'assets/img/avatar-beatriz.svg',
            'nivel' => 8,
            'titulo' => 'TRILHEIRA JUNINA',
            'pontos' => 530,
            'criado_em' => '2026-06-03',
            'favoritos' => [],
            'figurinhas' => [1],
        ],
        [
            'id' => 5,
            'nome' => 'Marta',
            'usuario' => 'martadocordeu',
            'email' => 'marta@arcoverde.com',
            'senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
            'avatar' => 'assets/img/avatar-marta.svg',
            'nivel' => 9,
            'titulo' => 'EMBAIXADORA DA VILA',
            'pontos' => 600,
            'criado_em' => '2026-06-03',
            'favoritos' => [4],
            'figurinhas' => [1, 4],
        ],
    ];
}

function defaultDataMap(): array
{
    return [
        'users.json' => seedUsers(),
        'programacao.json' => [
            [
                'id' => 1,
                'artista' => 'Alceu Valença',
                'palco' => 'Palco Multicultural',
                'data' => '2026-06-24',
                'horario' => '22:00',
                'status' => 'ao_vivo',
                'imagem' => 'assets/img/atracao-alceu.svg',
                'categoria' => 'Forró',
                'descricao' => 'Clássicos nordestinos com participação especial.',
                'lotacao' => 'movimento_moderado',
            ],
            [
                'id' => 2,
                'artista' => 'Elba Ramalho',
                'palco' => 'Palco Multicultural',
                'data' => '2026-06-24',
                'horario' => '00:30',
                'status' => 'proxima_atracao',
                'imagem' => 'assets/img/atracao-elba.svg',
                'categoria' => 'Forró',
                'descricao' => 'Grandes sucessos em noite histórica no pátio.',
                'lotacao' => 'alta_lotacao',
            ],
            [
                'id' => 3,
                'artista' => 'Trio Pé de Serra',
                'palco' => 'Polo Gastronômico',
                'data' => '2026-06-24',
                'horario' => '21:00',
                'status' => 'finalizado',
                'imagem' => 'assets/img/atracao-trio.svg',
                'categoria' => 'Pé de Serra',
                'descricao' => 'Xote tradicional com sanfona, zabumba e triângulo.',
                'lotacao' => 'pouco_movimento',
            ],
            [
                'id' => 4,
                'artista' => 'Orquestra Sanfônica',
                'palco' => 'Polo Multicultural',
                'data' => '2026-06-24',
                'horario' => '23:30',
                'status' => 'proxima_atracao',
                'imagem' => 'assets/img/atracao-orquestra.svg',
                'categoria' => 'Instrumental',
                'descricao' => 'Repertório junino em formação especial.',
                'lotacao' => 'movimento_moderado',
            ],
            [
                'id' => 5,
                'artista' => 'Quadrilha Junina',
                'palco' => 'Palco Principal',
                'data' => '2026-06-24',
                'horario' => '20:00',
                'status' => 'finalizado',
                'imagem' => 'assets/img/atracao-quadrilha.svg',
                'categoria' => 'Dança',
                'descricao' => 'Apresentação campeã com enredo regional.',
                'lotacao' => 'alta_lotacao',
            ],
        ],
        'restaurantes.json' => [
            [
                'id' => 1,
                'nome' => 'O Casarão do Forró',
                'categoria' => 'Regional Nordestina',
                'distancia' => '1.2km',
                'avaliacao' => 4.9,
                'faixa_preco' => '$$$',
                'aberto_ate' => '03:00',
                'imagem' => 'assets/img/rest-casarao.svg',
                'prato_destaque' => 'Baião Cremoso da Vila',
                'preco_prato' => 'R$ 36,00',
                'descricao' => 'Sabor raiz com ingredientes da região.',
                'lotacao' => 'movimento_moderado',
            ],
            [
                'id' => 2,
                'nome' => 'Churrasco da Vila',
                'categoria' => 'Carnes e Parrilla',
                'distancia' => '0.5km',
                'avaliacao' => 4.7,
                'faixa_preco' => '$$',
                'aberto_ate' => '00:00',
                'imagem' => 'assets/img/rest-churrasco.svg',
                'prato_destaque' => 'Escondidinho de Carne de Sol',
                'preco_prato' => 'R$ 42,00',
                'descricao' => 'Cortes especiais e clima de arraial.',
                'lotacao' => 'alta_lotacao',
            ],
            [
                'id' => 3,
                'nome' => 'Bodega do Zé',
                'categoria' => 'Petiscos',
                'distancia' => '0.3km',
                'avaliacao' => 4.8,
                'faixa_preco' => '$$',
                'aberto_ate' => '02:00',
                'imagem' => 'assets/img/rest-bodega.svg',
                'prato_destaque' => 'Macaxeira com Charque',
                'preco_prato' => 'R$ 29,00',
                'descricao' => 'Petiscos rápidos para antes dos shows.',
                'lotacao' => 'pouco_movimento',
            ],
            [
                'id' => 4,
                'nome' => 'Vila de Todos os Santos',
                'categoria' => 'Cozinha Contemporânea',
                'distancia' => '1.0km',
                'avaliacao' => 4.6,
                'faixa_preco' => '$$$',
                'aberto_ate' => '01:30',
                'imagem' => 'assets/img/rest-vila.svg',
                'prato_destaque' => 'Risoto de Queijo Coalho',
                'preco_prato' => 'R$ 54,00',
                'descricao' => 'Ambiente elegante com toque regional.',
                'lotacao' => 'movimento_moderado',
            ],
        ],
        'grupos.json' => [
            [
                'id' => 1,
                'nome' => 'Amantes do Forró',
                'capa' => 'assets/img/group-amantes.svg',
                'privacidade' => 'publico',
                'codigo' => 'FORRO26',
                'vip' => true,
                'membros' => [1, 2, 3, 4],
                'roteiro' => [1, 2, 3],
                'criado_por' => 3,
            ],
            [
                'id' => 2,
                'nome' => 'Caravana Arcoverde',
                'capa' => 'assets/img/group-caravana.svg',
                'privacidade' => 'publico',
                'codigo' => 'CARAVA26',
                'vip' => false,
                'membros' => [1, 4, 5],
                'roteiro' => [4],
                'criado_por' => 5,
            ],
            [
                'id' => 3,
                'nome' => 'Arraial do Sertão',
                'capa' => 'assets/img/group-arraial.svg',
                'privacidade' => 'privado',
                'codigo' => 'SERTAO26',
                'vip' => false,
                'membros' => [3, 5],
                'roteiro' => [],
                'criado_por' => 3,
            ],
        ],
        'roteiros.json' => [
            [
                'id' => 1,
                'user_id' => 1,
                'tipo' => 'pessoal',
                'grupo_id' => null,
                'itens' => [
                    [
                        'id' => 1,
                        'horario' => '19:00',
                        'titulo' => 'Show Alceu Valença',
                        'local' => 'Palco Multicultural',
                        'tipo' => 'show',
                        'sugerido_por' => 'Você',
                        'status' => 'agora',
                    ],
                    [
                        'id' => 2,
                        'horario' => '21:30',
                        'titulo' => 'Bodega do Zé',
                        'local' => 'Vila de Todos os Santos',
                        'tipo' => 'gastronomia',
                        'sugerido_por' => 'Mariana',
                        'status' => 'pendente',
                    ],
                ],
            ],
            [
                'id' => 2,
                'user_id' => null,
                'tipo' => 'grupo',
                'grupo_id' => 1,
                'itens' => [
                    [
                        'id' => 1,
                        'horario' => '20:00',
                        'titulo' => 'Show de Alceu Valença',
                        'local' => 'Palco Principal',
                        'tipo' => 'show',
                        'sugerido_por' => 'Lucas',
                        'status' => 'confirmado',
                    ],
                    [
                        'id' => 2,
                        'horario' => '22:30',
                        'titulo' => 'Jantar na Bodega do Zé',
                        'local' => 'Polo Gastronômico',
                        'tipo' => 'gastronomia',
                        'sugerido_por' => 'Maria',
                        'status' => 'confirmado',
                    ],
                    [
                        'id' => 3,
                        'horario' => '00:00',
                        'titulo' => 'Trio Pé de Serra',
                        'local' => 'Polo Gastronômico',
                        'tipo' => 'show',
                        'sugerido_por' => 'Você',
                        'status' => 'confirmado',
                    ],
                ],
            ],
        ],
        'figurinhas.json' => [
            ['id' => 1, 'titulo' => 'Eu vivi o São João de Arcoverde', 'descricao' => 'Conquista inicial.', 'imagem' => 'assets/img/sticker-vivi.svg', 'categoria' => 'conquista', 'desbloqueada' => true, 'progresso' => 100],
            ['id' => 2, 'titulo' => 'Sanfona', 'descricao' => 'Som que embala a festa.', 'imagem' => 'assets/img/sticker-sanfona.svg', 'categoria' => 'instrumento', 'desbloqueada' => false, 'progresso' => 0],
            ['id' => 3, 'titulo' => 'Fogueira', 'descricao' => 'Calor da tradição.', 'imagem' => 'assets/img/sticker-fogueira.svg', 'categoria' => 'tradição', 'desbloqueada' => false, 'progresso' => 0],
            ['id' => 4, 'titulo' => 'Bandeirinhas', 'descricao' => 'Cores da vila.', 'imagem' => 'assets/img/sticker-bandeirinhas.svg', 'categoria' => 'decoração', 'desbloqueada' => false, 'progresso' => 0],
            ['id' => 5, 'titulo' => 'Cacto do Sertão', 'descricao' => 'Ícone da região.', 'imagem' => 'assets/img/sticker-cacto.svg', 'categoria' => 'natureza', 'desbloqueada' => false, 'progresso' => 0],
            ['id' => 6, 'titulo' => 'QR Code Secreto', 'descricao' => 'Ponto interativo encontrado.', 'imagem' => 'assets/img/sticker-qr.svg', 'categoria' => 'digital', 'desbloqueada' => false, 'progresso' => 0],
            ['id' => 7, 'titulo' => 'Restaurante Visitado', 'descricao' => 'Sabores descobertos.', 'imagem' => 'assets/img/sticker-rest.svg', 'categoria' => 'gastronomia', 'desbloqueada' => false, 'progresso' => 0],
            ['id' => 8, 'titulo' => 'Show Assistido', 'descricao' => 'Música ao vivo curtida.', 'imagem' => 'assets/img/sticker-show.svg', 'categoria' => 'show', 'desbloqueada' => false, 'progresso' => 0],
        ],
        'convites.json' => [
            ['id' => 1, 'group_id' => 3, 'from_user_id' => 3, 'to_user_id' => 1, 'status' => 'pendente'],
            ['id' => 2, 'group_id' => 2, 'from_user_id' => 5, 'to_user_id' => 1, 'status' => 'pendente'],
            ['id' => 3, 'group_id' => 1, 'from_user_id' => 2, 'to_user_id' => 1, 'status' => 'pendente'],
        ],
        'pontos.json' => [
            ['id' => 1, 'nome' => 'Posto de Saúde', 'tipo' => 'saude', 'local' => 'Atrás do Palco Principal', 'distancia' => '150m', 'cor' => '#22c55e', 'icone' => 'fa-briefcase-medical'],
            ['id' => 2, 'nome' => 'Sanitários Premium', 'tipo' => 'sanitario', 'local' => 'Praça de Alimentação', 'distancia' => '50m', 'cor' => '#c96f2b', 'icone' => 'fa-restroom'],
            ['id' => 3, 'nome' => 'Base de Segurança', 'tipo' => 'seguranca', 'local' => 'Entrada Sul', 'distancia' => '300m', 'cor' => '#b91c1c', 'icone' => 'fa-shield-halved'],
            ['id' => 4, 'nome' => 'Ponto de Táxi', 'tipo' => 'mobilidade', 'local' => 'Rua da Feira', 'distancia' => '220m', 'cor' => '#0f766e', 'icone' => 'fa-taxi'],
            ['id' => 5, 'nome' => 'Estacionamento', 'tipo' => 'mobilidade', 'local' => 'Lote Norte', 'distancia' => '400m', 'cor' => '#334155', 'icone' => 'fa-square-parking'],
            ['id' => 6, 'nome' => 'Achados e Perdidos', 'tipo' => 'apoio', 'local' => 'Central de Serviços', 'distancia' => '180m', 'cor' => '#7c3aed', 'icone' => 'fa-box-open'],
        ],
        'notificacoes.json' => [
            ['id' => 1, 'titulo' => 'Show começa em 15 minutos', 'descricao' => 'Elba Ramalho sobe ao Palco Multicultural.', 'tipo' => 'alerta', 'lida' => false],
            ['id' => 2, 'titulo' => 'Restaurante próximo aberto até 03:00', 'descricao' => 'O Casarão do Forró está a 1.2km.', 'tipo' => 'gastronomia', 'lida' => false],
            ['id' => 3, 'titulo' => 'Base de segurança a 300m', 'descricao' => 'Direção recomendada: Entrada Sul.', 'tipo' => 'seguranca', 'lida' => false],
        ],
    ];
}

function bootstrapJsonData(): void
{
    $map = defaultDataMap();
    foreach ($map as $file => $seedData) {
        $path = DATA_PATH . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($path)) {
            file_put_contents(
                $path,
                json_encode($seedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
        }
    }
}

bootstrapJsonData();
