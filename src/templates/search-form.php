<section class="search-form">
    <h2>Busca de Notícias</h2>
    <div class="tabs flex">
        <button type="button" class="<?= $search_args->search_type === 1 ? "active" : null ?>">Termo e Período</button>
        <button type="button" class="<?= $search_args->search_type === 2 ? "active" : null ?>">Categoria e País</button>
        <button type="button" class="<?= $search_args->search_type === 3 ? "active" : null ?>">Manchetes Principais</button>
        <button type="button" class="<?= $search_args->search_type === 4 ? "active" : null ?>">Domínio Específico</button>
    </div>
    <div class="tab-contents">
        <!-- Busca por Termo e Período -->
        <div class="content <?= $search_args->search_type === 1 ? "active" : null ?>">
            <form method="get" action="results.php">
                <div class="field-group">
                    <input type="text" name="term" id="term" class="field-input" value="<?= $search_args->term ?? "" ?>" placeholder="* Digite o termo a ser buscado aqui" required />
                </div>
                <div class="field-group flex">
                    <input type="date" name="date_from" id="date_from" class="field-input" value="<?= $search_args->date_from ?? "" ?>" placeholder="Data inicial" />
                    <input type="date" name="date_to" id="date_to" class="field-input" value="<?= $search_args->date_to ?? "" ?>" placeholder="Data final" />
                </div>
                <p class="disclaim">* Se o período não for especificado, retorna resultados dos últimos 30 dias.</p>
                <div class="field-group">
                    <button type="submit" class="btn">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
        <!-- Busca por Categoria e País -->
        <div class="content <?= $search_args->search_type === 2 ? "active" : null ?>">
            <form method="get" action="results.php">
                <div class="field-group">
                    <?php $categories = get_available_categories(); ?>
                    <select name="category" id="category" class="field-input" required>
                        <option value="">* Selecione uma Categoria</option>
                        <?php foreach ($categories as $category_id => $category_name) : ?>
                            <option value="<?= $category_id ?>" <?= $search_args->category === $category_id ? "selected" : null ?>>
                                <?= $category_name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group">
                    <?php $countries = get_available_countries(); ?>
                    <select name="country" id="country" class="field-input" required>
                        <option value="">* Selecione o País</option>
                        <?php foreach ($countries as $country_id => $country_name) : ?>
                            <option value="<?= $country_id ?>" <?= $search_args->country === $country_id ? "selected" : null ?>>
                                <?= $country_name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group">
                    <button type="submit" class="btn">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
        <!-- Busca Manchetes Principais -->
        <div class="content <?= $search_args->search_type === 3 ? "active" : null ?>">
            <form method="get" action="results.php">
                <div class="field-group">
                    <?php $sources = get_headlines_sources(); ?>
                    <select name="source" id="source" class="field-input" required>
                        <option value="">* Selecione uma Fonte</option>
                        <?php foreach ($sources as $source) : ?>
                            <option value="<?= $source->id ?>" <?= $source->id === $search_args->source ? "selected" : null ?>>
                                <?= $source->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="disclaim">
                    * Exibe as 10 principais manchetes da fonte selecionada.
                </p>
                <div class="field-group">
                    <button type="submit" class="btn">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
        <!-- Busca por domínio específico -->
        <div class="content <?= $search_args->search_type === 4 ? "active" : null ?>">
            <form method="get" action="results.php">
                <div class="field-group">
                    <label for="domain">Domínio:</label>
                    <input type="text" name="domain" id="domain" class="field-input" value="<?= $search_args->domain ?? "" ?>" placeholder="Ex: nytimes.com, g1.com.br" required />
                </div>
                <p class="disclaim">
                    * Os resultados estarão restritos ao domínio informado.
                </p>
                <div class="field-group">
                    <button type="submit" class="btn">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>