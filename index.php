<?php

//phpinfo(); die();

require("config.php");

try {
    initApplication();
} catch (Exception $e) { 
    $results['errorMessage'] = $e->getMessage();
    require(TEMPLATE_PATH . "/viewErrorPage.php");
}


function initApplication()
{
    $action = isset($_GET['action']) ? $_GET['action'] : "";

    switch ($action) {
        case 'archive':
          archive();
          break;
        case 'viewArticle':
          viewArticle();
          break;
        default:
          homepage();
    }
}

function subcategoryArchive(int $subcategoryId) {
    $results = [];

    $data = Article::getList( 100000, subcategoryId: $subcategoryId);

    $results['subcategory'] = Subcategory::getById($subcategoryId);

    $results['pageHeading'] = $results['subcategory'] ?  $results['subcategory']->name : "Article Archive";
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";

    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];


    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }

    $data = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ( $data['results'] as $subcategory ) {
        $results['subcategories'][$subcategory->category_id] = $subcategory;
    }

    require( TEMPLATE_PATH . "/archive.php" );
}

function archive() 
{
    $results = [];



    $subcategoryId = $_GET['subcategoryId'] ?? null;

    if (isset($subcategoryId)) {
        subcategoryArchive($subcategoryId);
        exit;
    }

    $categoryId = ( isset( $_GET['categoryId'] ) && $_GET['categoryId'] ) ? (int)$_GET['categoryId'] : null;
    
    $results['category'] = Category::getById($categoryId);

    $data = Article::getList( 100000, $results['category'] ? $results['category']->id : null );

    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }

    $data = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ( $data['results'] as $subcategory ) {
        $results['subcategories'][$subcategory->category_id] = $subcategory;
    }
    
    $results['pageHeading'] = $results['category'] ?  $results['category']->name : "Article Archive";
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 * 
 * @return null
 */
function viewArticle() 
{   
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $articleId = (int)$_GET["articleId"];
    $results['article'] = Article::getById($articleId);
    
    if (!$results['article']) {
        throw new Exception("Статья с id = $articleId не найдена");
    }
    
    $results['category'] = Category::getById($results['article']->categoryId);
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    require(TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage() 
{
    $results = array();
    $data = Article::getList(HOMEPAGE_NUM_ARTICLES);


    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];

    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }

    $data = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ( $data['results'] as $subcategory ) {
        $results['subcategories'][$subcategory->category_id] = $subcategory;
    }
    
    $results['pageTitle'] = "Простая CMS на PHP";
    
    require(TEMPLATE_PATH . "/homepage.php");
    
}