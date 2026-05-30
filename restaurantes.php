<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$restaurantes = readJson('restaurantes.json');
$activeTab = 'explorar';
$pageTitle = 'São João 2026';
$pageEyebrow = 'festival';
include __DIR__ . '/includes/header.php';
?>
<section class="search-wrap">
    <div class="input-icon">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" placeholder="Buscar sabores do sertão..." data-rest-search>
    </div>
</section>

<section class="toggle-wrap" data-view-toggle>
    <button class="toggle-btn active" data-view="lista"><i class="fa-solid fa-list"></i> Lista</button>
    <button class="toggle-btn" data-view="mapa"><i class="fa-regular fa-map"></i> Mapa</button>
</section>

<section class="filters-row scroll-x">
    <button class="chip chip-filter active">Culinária</button>
    <button class="chip chip-filter" data-filter-open>Aberto Agora</button>
    <button class="chip chip-filter">Distância</button>
</section>

<section class="stack-list" data-view-lista>
    <?php foreach ($restaurantes as $rest): ?>
        <article class="restaurant-card" data-rest-card data-rest-nome="<?= sanitize(mb_strtolower($rest['nome'])); ?>" data-rest-cat="<?= sanitize(mb_strtolower($rest['categoria'])); ?>" data-open="<?= sanitize($rest['aberto_ate']); ?>">
            <div class="rest-image-wrap">
                <img src="<?= sanitize($rest['imagem']); ?>" alt="<?= sanitize($rest['nome']); ?>">
                <span class="rating-pill"><i class="fa-solid fa-star"></i> <?= sanitize((string) $rest['avaliacao']); ?></span>
            </div>
            <div class="rest-body">
                <div class="row-between">
                    <h3><?= sanitize($rest['nome']); ?></h3>
                    <span class="price-tag"><?= sanitize($rest['faixa_preco']); ?></span>
                </div>
                <p><?= sanitize($rest['categoria']); ?> • <?= sanitize($rest['distancia']); ?></p>
                <p class="open-until"><i class="fa-regular fa-clock"></i> Aberto até <?= sanitize($rest['aberto_ate']); ?></p>
                <span class="lotacao-pill"><?= lotacaoLabel($rest['lotacao']); ?></span>
                <div class="menu-highlight">
                    <p class="label"><i class="fa-solid fa-utensils"></i> DESTAQUE DO MENU</p>
                    <div class="menu-item">
                        <img src="<?= sanitize($rest['imagem']); ?>" alt="<?= sanitize($rest['prato_destaque']); ?>">
                        <div>
                            <strong><?= sanitize($rest['prato_destaque']); ?></strong>
                            <p><?= sanitize($rest['descricao']); ?></p>
                            <span><?= sanitize($rest['preco_prato']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="map-view hidden" data-view-mapa>
    <article class="card card-soft">
        <h3>Mapa Inteligente</h3>
        <p>Pontos com lotação simulada e distância em tempo real.</p>
    </article>
    <div class="map-mock">
        <div class="pin pin-1">Casarão</div>
        <div class="pin pin-2">Bodega do Zé</div>
        <div class="pin pin-3">Churrasco</div>
        <div class="roads"></div>
    </div>
</section>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>

