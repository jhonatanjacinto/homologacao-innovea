<?php

/**
 * Configurações Gerais
 */
require "src/config.php";

// Cabeçalho
app_header();

// Form de Pesquisa
app_search_form();

// Resultados da Busca
app_search_results();

// Rodapé
app_footer();