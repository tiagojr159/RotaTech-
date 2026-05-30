<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$hospedagens = readJson('hospedagens.json');
$activeTab = 'explorar';
$pageTitle = 'Sao Joao 2026';
$pageEyebrow = 'festival';
include __DIR__ . '/includes/header.php';
?>
<section class="search-wrap">
    <div class="input-icon">
        <i class="fa-solid fa-bed"></i>
        <input type="search" placeholder="Buscar hotel ou pousada..." data-hosp-search>
    </div>
</section>

<section class="filters-row scroll-x">
    <button class="chip chip-filter active" data-hosp-filter="todos">Todos</button>
    <button class="chip chip-filter" data-hosp-filter="hotel">Hoteis</button>
    <button class="chip chip-filter" data-hosp-filter="pousada">Pousadas</button>
</section>

<section class="stack-list" data-hosp-list>
    <?php foreach ($hospedagens as $item): ?>
        <article class="point-card hospedagem-card" data-hosp-card data-hosp-nome="<?= sanitize(mb_strtolower($item['nome'])); ?>" data-hosp-cat="<?= sanitize(mb_strtolower($item['categoria'])); ?>" data-hosp-endereco="<?= sanitize(mb_strtolower($item['endereco'])); ?>">
            <?php if (!empty($item['imagem'])): ?>
                <img src="<?= sanitize($item['imagem']); ?>" alt="<?= sanitize($item['nome']); ?>" class="hospedagem-thumb">
            <?php else: ?>
                <span class="point-icon" style="background: #1f7a4a22; color: #1f7a4a;">
                    <i class="fa-solid fa-bed"></i>
                </span>
            <?php endif; ?>
            <div>
                <h4><?= sanitize($item['nome']); ?></h4>
                <p><?= sanitize(ucfirst($item['categoria'])); ?> - <?= sanitize($item['endereco']); ?></p>
            </div>
            <i class="fa-solid fa-angle-right"></i>
        </article>
    <?php endforeach; ?>
</section>
<p class="center muted hidden" data-hosp-empty>Nenhuma hospedagem encontrada para esse filtro.</p>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>
