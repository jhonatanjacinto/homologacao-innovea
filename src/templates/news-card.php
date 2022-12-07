<?php /** Objeto $news definido no contexto da função app_news_card() */ ?>
<article class="news-card">
    <a href="<?= $news->url ?>" target="_blank">
        <span class="source"><?= $news->source ?: "Não informado" ?></span>
        <h3><?= $news->title ?: "Sem título" ?></h3>
        <span class="author">Por: <?= $news->author ?: "Não informado" ?></span>
        <p><?= $news->description ?: "Não informado" ?></p>
    </a>
</article>