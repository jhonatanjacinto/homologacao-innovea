<?php /** Array $results definido no contexto da função app_search_results() */ ?>
<section class="search-results">
    <h2>Resultados</h2>
    <?php if ($results) : ?>
        <div class="news-container flex">
            <?php 
                foreach ($results as $news) {
                    app_news_card(
                        $news->title,
                        $news->source->name,
                        $news->description,
                        $news->author,
                        $news->url
                    );
                }
            ?>
        </div>
    <?php else: ?>
        <p>Não há resultados a serem exibidos!</p>
    <?php endif; ?>
</section>