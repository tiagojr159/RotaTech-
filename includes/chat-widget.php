<?php
declare(strict_types=1);

if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Acesso negado.');
}
?>
<div class="chatbot-widget" data-chatbot-widget>
    <button type="button" class="chatbot-fab" data-chatbot-toggle aria-controls="chatbot-panel" aria-expanded="false" aria-label="Abrir guia turistico">
        <i class="fa-solid fa-comments"></i>
    </button>
    <section class="chatbot-panel hidden" id="chatbot-panel" aria-label="Guia turistico de Arcoverde">
        <header class="chatbot-header">
            <div>
                <strong>Guia RotaTech</strong>
                <span>Seu guia em Arcoverde</span>
            </div>
            <button type="button" class="chatbot-close" data-chatbot-close aria-label="Fechar guia">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>
        <div class="chatbot-messages" data-chatbot-messages aria-live="polite">
            <p class="chatbot-message bot">Ola! Posso ajudar com a programacao do Sao Joao, restaurantes, hospedagens e pontos de apoio em Arcoverde.</p>
        </div>
        <form class="chatbot-form" data-chatbot-form>
            <label class="sr-only" for="chatbot-input">Mensagem para o guia</label>
            <input id="chatbot-input" name="message" type="text" maxlength="700" placeholder="Pergunte sobre Arcoverde..." autocomplete="off" required>
            <button type="submit" aria-label="Enviar mensagem">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </section>
</div>
<span class="location-tracker" data-location-tracker hidden></span>
