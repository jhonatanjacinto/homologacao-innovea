<?php

/**
 * Exibe a estrutura interna de uma variável (Debug)
 * @param mixed $var        Variável a ser verificada
 * @return never
 */
function custom_print($var): never 
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

/**
 * Exibe o cabeçalho padrão da aplicação
 * @return void
 */
function app_header(): void 
{
    include __DIR__ . "/templates/header.php";
}

/**
 * Exibe o rodapé padrão da aplicação
 * @return void
 */
function app_footer(): void
{
    include __DIR__ . "/templates/footer.php";
}

/**
 * Retorna o título apropriado para a página atual
 * @return string
 */
function app_get_title(): string 
{
    if (empty($_GET)) {
        return APP_TITLE . " - " . APP_SLOGAN;
    }

    $search_args = (object) get_search_form_args();
    $page_title = match($search_args->search_type) {
        1 => "Resultados para: " . $search_args->term,
        2 => "Resultados para Busca por Categoria/País",
        3 => "Resultados para busca por fonte",
        4 => "Resultados para o domínio: " . $search_args->domain
    };

    return $page_title . " - " . APP_TITLE;
}

/**
 * Exibe o formulário de busca de notícias
 * @return array
 */
function app_search_form(): void 
{
    $search_args = (object) get_search_form_args();
    include __DIR__ . "/templates/search-form.php";
}

/**
 * Retorna as informações enviadas por meio do form de busca
 * @return array
 */
function get_search_form_args(): array
{
    $search_type = 1;
    if ($_GET["term"]) {
        $term = filter_input(INPUT_GET, "term", FILTER_SANITIZE_SPECIAL_CHARS);
        $date_from = filter_input(INPUT_GET, "date_from", FILTER_SANITIZE_SPECIAL_CHARS);
        $date_to = filter_input(INPUT_GET, "date_to", FILTER_SANITIZE_SPECIAL_CHARS);
    } else if ($_GET["category"] && $_GET["country"]) {
        $search_type = 2;
        $category_code = filter_input(INPUT_GET, "category", FILTER_SANITIZE_SPECIAL_CHARS);
        $country_code = filter_input(INPUT_GET, "country", FILTER_SANITIZE_SPECIAL_CHARS);
    } else if ($_GET["source"]) {
        $search_type = 3;
        $source_code = filter_input(INPUT_GET, "source", FILTER_SANITIZE_SPECIAL_CHARS);
    } else if ($_GET["domain"]) {
        $search_type = 4;
        $domain = clear_domain($_GET["domain"]);
    }

    $search_args = array(
        "search_type" => $search_type,
        "term" => $term ?? "",
        "date_from" => $date_from ?? "",
        "date_to" => $date_to ?? "",
        "category" => $category_code ?? "",
        "country" => $country_code ?? "",
        "source" => $source_code ?? "",
        "domain" => $domain ?? ""
    );

    return $search_args;
}

/**
 * Exibe o bloco de resultados para a busca realizada
 * @return void
 */
function app_search_results(): void 
{
    $search_args = (object) get_search_form_args();
    $results = match ($search_args->search_type) {
        1 => search_by_term($search_args->term, $search_args->date_from, $search_args->date_to),
        2 => search_by_category_country($search_args->category, $search_args->country),
        3 => search_by_source($search_args->source),
        4 => search_by_domain($search_args->domain),
        default => []
    };

    include __DIR__ . "/templates/search-results.php";
}

/**
 * Exibe o card que representa as informações de uma notícia/artigo
 * @param ?string $title         Título da notícia
 * @param ?string $source        Fonte da notícia
 * @param ?string $description   Descrição padrão
 * @param ?string $author        Autor da notícia
 * @param ?string $url           Link da notícia
 * @return void
 */
function app_news_card(?string $title, ?string $source, ?string $description, ?string $author, ?string $url): void 
{
    $news = (object) array(
        "title" => $title,
        "source" => $source,
        "description" => $description,
        "author" => $author,
        "url" => $url
    );

    include __DIR__ . "/templates/news-card.php";
}

/**
 * Limpa a string correspondente ao domínio garantindo que somente o HOST da URL informada será considerado.
 * @param string $domain        Domínio a ser analizado
 * @return string|null
 */
function clear_domain(string $domain): ?string
{
    $domain = filter_var($domain, FILTER_SANITIZE_URL);
    $domain_parsed = parse_url($domain);
    ["host" => $host, "path" => $path] = $domain_parsed;

    if (str_contains($path, "/")) {
        $path = substr($path, 0, strpos($path, "/"));
    }

    $domain = $host ?: $path;
    return $domain;
}

/**
 * Retorna a lista de categorias disponíveis para filtragem de notícias na API
 * @return array
 */
function get_available_categories(): array 
{
    $categories = array(
        "business" => "Business",
        "entertainment" => "Entretenimento",
        "general" => "Geral",
        "health" => "Saúde",
        "science" => "Ciência",
        "sports" => "Esporte",
        "technology" => "Tecnologia"
    );

    asort($categories);

    return $categories;
}

/**
 * Retorna a lista parcial de países disponíveis para filtragem de notícias na API
 * @return array
 */
function get_available_countries(): array 
{
    $countries = array(
        "ar" => "Argentina",
        "br" => "Brasil",
        "ca" => "Canadá",
        "us" => "Estados Unidos",
        "de" => "Alemanha",
        "fr" => "França",
        "es" => "Espanha",
        "au" => "Austrália",
        "nz" => "Nova Zelândia",
        "mx" => "México"
    );

    asort($countries);

    return $countries;
}

/**
 * Retorna a lista de fontes de notícias para busca de manchetes disponibilizadas pela API da aplicação. Também realiza o 
 * cache local destas informações cujo tempo limite é definido pela constante JSON_SOURCES_CACHE_MINUTES.
 * @return array
 */
function get_headlines_sources(): array
{
    $sources = array();
    $sources_cache_file = __DIR__ . "/cache/headline-sources.json";

    if (file_exists($sources_cache_file)) {
        $cache_time_limit = round((time() - filemtime($sources_cache_file)) / 60);
        if ($cache_time_limit < JSON_SOURCES_CACHE_MINUTES) {
            $json_sources_text = file_get_contents($sources_cache_file);
            $sources = json_decode($json_sources_text);
            return $sources;
        }
    }

    $endpoint_uri = NEWS_API_URI . "/top-headlines/sources?apiKey=" . NEWS_API_KEY;
    $json_content = api_get_request($endpoint_uri);
    $json_result = json_decode($json_content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        exit("Não foi possível carregar a lista de fontes de notícias disponíveis da API!");
    }

    $sources = $json_result->sources;
    file_put_contents($sources_cache_file, json_encode($sources));

    return $sources;
}

/**
 * Realiza a busca dos artigos por Termo (opcionalmente, por período também)
 * @param string $term          Termo a ser buscado
 * @param string $date_from     Data inicial
 * @param string $date_to       Data final
 * @return array
 */
function search_by_term(string $term, string $date_from = "", string $date_to = ""): array 
{
    $query_args = array(
        "apiKey" => NEWS_API_KEY,
        "q" => $term,
        "from" => $date_from ?: date("Y-m-d"),
        "to" => $date_to,
        "sortBy" => "publishedAt"
    );

    $endpoint_uri = NEWS_API_URI . "/everything?" . http_build_query($query_args);
    $json_content = api_get_request($endpoint_uri);
    if ($json_content === false) {
        return [];
    }

    $json_result = json_decode($json_content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        exit("Não foi possível carregar a lista de artigos para a pesquisa definida!");
    }

    $articles = $json_result->articles;
    return $articles;
}

/**
 * Realiza a busca dos artigos por Categoria e País
 * @param string $category          Código da Categoria
 * @param string $country           Código do País
 * @return array
 */
function search_by_category_country(string $category, string $country): array 
{
    $valid_categories = get_available_categories();
    $valid_countries = get_available_countries();

    if (!in_array($category, array_keys($valid_categories)) or !in_array($country, array_keys($valid_countries))) {
        return [];
    }

    $query_args = array(
        "apiKey" => NEWS_API_KEY,
        "country" => $country,
        "category" => $category,
        "sortBy" => "publishedAt"
    );

    $endpoint_uri = NEWS_API_URI . "/top-headlines?" . http_build_query($query_args);
    $json_content = api_get_request($endpoint_uri);
    if ($json_content === false) {
        return [];
    }

    $json_result = json_decode($json_content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        exit("Não foi possível carregar a lista de artigos para a pesquisa por categoria e país!");
    }

    $articles = $json_result->articles;
    return $articles;
}

/**
 * Realiza a busca dos artigos por fonte
 * @param string $source          Fonte a ser pesquisada
 * @return array
 */
function search_by_source(string $source): array 
{
    $available_sources = get_headlines_sources();
    if (!in_array($source, array_column($available_sources, "id"))) {
        return [];
    }

    $query_args = array(
        "apiKey" => NEWS_API_KEY,
        "sources" => $source,
        "sortBy" => "publishedAt"
    );

    $endpoint_uri = NEWS_API_URI . "/top-headlines?" . http_build_query($query_args);
    $json_content = api_get_request($endpoint_uri);
    if ($json_content === false) {
        return [];
    }

    $json_result = json_decode($json_content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        exit("Não foi possível carregar a lista de artigos para a pesquisa por fonte!");
    }

    $articles = $json_result->articles;
    return $articles;
}

/**
 * Realiza a busca dos artigos por domínio específico
 * @param string $domain          Domínio a ser pesquisado
 * @return array
 */
function search_by_domain(string $domain): array 
{
    $query_args = array(
        "apiKey" => NEWS_API_KEY,
        "domains" => $domain,
        "sortBy" => "publishedAt"
    );

    $endpoint_uri = NEWS_API_URI . "/everything?" . http_build_query($query_args);
    $json_content = api_get_request($endpoint_uri);
    if ($json_content === false) {
        return [];
    }

    $json_result = json_decode($json_content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        exit("Não foi possível carregar a lista de artigos para a pesquisa por domínio!");
    }

    $articles = $json_result->articles;
    return $articles;
}

/**
 * Realiza a requisição GET ao endpoint informado e retorna o conteúdo disponibilizado pela API
 * @param string $endpoint      Endpoint a ser consultado
 * @return string|false
 */
function api_get_request(string $endpoint): string|false
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
    $json_text_content = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($status_code !== 200) {
        return false;
    }

    return $json_text_content ?: false;
}