<?php

/**
 * Constantes e Definições Gerais da aplicação
 */
define("APP_TITLE", "News App");
define("APP_SLOGAN", "Encontre informações sobre artigos e notícias de diferentes fontes e idiomas na web.");
define("NEWS_API_URI", "https://newsapi.org/v2");
define("NEWS_API_KEY", "0070cff08ab34958b0bd48f3afda6bbe");
define("JSON_SOURCES_CACHE_MINUTES", 60 * 24); // 24 horas de cache

/**
 * Funções gerais da aplicação
 */
require_once "functions.php";

/**
 * Desabilita a exibição de erros na aplicação
 */
error_reporting(0);
ini_set("display_errors", "Off");