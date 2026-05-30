<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$programacao = readJson('programacao.json');
$eventosNarracao = array_slice($programacao, 0, 3);
$textoNarracao = 'Agora na programacao do Sao Joao de Arcoverde: ';
if (!empty($eventosNarracao)) {
    $partesNarracao = [];
    foreach ($eventosNarracao as $evento) {
        $partesNarracao[] = trim(($evento['artista'] ?? 'Atracao') . ' as ' . ($evento['horario'] ?? '--:--') . ' no ' . ($evento['palco'] ?? 'palco principal'));
    }
    $textoNarracao .= implode('. ', $partesNarracao) . '.';
} else {
    $textoNarracao = 'Ainda nao ha eventos cadastrados na programacao.';
}
$activeTab = 'explorar';
$pageTitle = 'Programação';
$pageEyebrow = 'festival';
include __DIR__ . '/includes/header.php';
?>
<section class="filters-row scroll-x" data-programacao-dates>
    <button class="chip chip-date active" data-date="all">19 JUN</button>
    <button class="chip chip-date" data-date="2026-06-20">20 JUN</button>
    <button class="chip chip-date" data-date="2026-06-21">21 JUN</button>
    <button class="chip chip-date" data-date="2026-06-22">22 JUN</button>
    <button class="chip chip-date" data-date="2026-06-24">24 JUN</button>
</section>

<section class="filters-row scroll-x" data-programacao-palcos>
    <button class="chip chip-filter active" data-palco="todos"><i class="fa-solid fa-sliders"></i> Todos os Palcos</button>
    <button class="chip chip-filter" data-palco="Multicultural">Multicultural</button>
    <button class="chip chip-filter" data-palco="Polo Gastronômico">Polo Gastronômico</button>
    <button class="chip chip-filter" data-palco="Palco Principal">Palco Principal</button>
</section>

<article class="card voice-guide-card">
    <div>
        <p class="eyebrow">Assistente de voz</p>
        <h3>Ouvir 3 eventos da programação</h3>
        <p>Quando a página abrir, o app tenta anunciar os próximos destaques. Se o celular bloquear, é só tocar no botão.</p>
    </div>
    <button
        type="button"
        class="btn btn-primary"
        data-voice-trigger
        data-voice-context="programacao"
        data-voice-autoplay="true"
        data-voice-text="<?= sanitize($textoNarracao); ?>"
    >
        <i class="fa-solid fa-volume-high"></i>
        Ouvir agenda
    </button>
</article>

<section class="stack-list programacao-list">
    <?php foreach ($programacao as $atracao): ?>
        <?php
        $favorite = in_array((int) $atracao['id'], $user['favoritos'] ?? [], true);
        ?>
        <article
            class="event-card status-<?= sanitize($atracao['status']); ?>"
            data-event-card
            data-date="<?= sanitize($atracao['data']); ?>"
            data-palco="<?= sanitize($atracao['palco']); ?>"
        >
            <img src="<?= sanitize($atracao['imagem']); ?>" alt="<?= sanitize($atracao['artista']); ?>" class="event-thumb">
            <div class="event-main">
                <div class="row-between">
                    <h4><?= sanitize($atracao['artista']); ?></h4>
                    <strong><?= sanitize($atracao['horario']); ?></strong>
                </div>
                <p><?= sanitize($atracao['palco']); ?></p>
                <div class="row-inline">
                    <span class="status-pill"><?= statusLabel($atracao['status']); ?></span>
                    <span class="lotacao-pill"><?= lotacaoLabel($atracao['lotacao']); ?></span>
                </div>
                <div class="row-inline">
                    <button class="ghost-btn" data-favorite-id="<?= (int) $atracao['id']; ?>">
                        <i class="fa-<?= $favorite ? 'solid' : 'regular'; ?> fa-heart"></i> Favoritar
                    </button>
                    <button class="ghost-btn" data-add-roteiro='<?= json_encode([
                        'titulo' => $atracao['artista'],
                        'local' => $atracao['palco'],
                        'horario' => $atracao['horario'],
                        'tipo' => 'show',
                    ], JSON_UNESCAPED_UNICODE); ?>'>
                        <i class="fa-solid fa-plus"></i> Roteiro
                    </button>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<article class="card card-green">
    <div class="flags-mini"><span></span><span></span><span></span></div>
    <h3>Crie seu próprio roteiro!</h3>
    <p>Favorite as atrações que você não quer perder e receba avisos 15min antes.</p>
    <a href="roteiro.php" class="btn btn-primary">
        <i class="fa-regular fa-map"></i>
        Ver meu roteiro
    </a>
</article>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>
