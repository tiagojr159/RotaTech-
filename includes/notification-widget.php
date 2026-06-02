<?php
declare(strict_types=1);

if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Acesso negado.');
}
?>
<div class="notification-widget" data-notification-widget>
    <button type="button" class="notification-bell" data-notification-toggle aria-controls="notification-panel" aria-expanded="false" aria-label="Abrir notificacoes">
        <i class="fa-regular fa-bell"></i>
        <span class="notification-count hidden" data-notification-count>0</span>
    </button>
    <section class="notification-panel hidden" id="notification-panel" aria-label="Notificacoes">
        <header class="notification-header">
            <div>
                <strong>Notificacoes</strong>
                <span>Alertas do Sao Joao</span>
            </div>
            <button type="button" data-notification-close aria-label="Fechar notificacoes">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>
        <div class="notification-list" data-notification-list>
            <p class="notification-empty">Carregando notificacoes...</p>
        </div>
    </section>
</div>
