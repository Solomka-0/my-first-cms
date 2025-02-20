<?php


/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /**
     * @var int ID статей из базы данны
     */
    public $id = null;

    /**
     * @var int Дата первой публикации статьи
     */
    public $publicationDate = null;

    /**
     * @var string Полное название статьи
     */
    public $title = null;

    /**
     * @var int ID категории статьи
     */
    public $categoryId = null;

    /**
     * @var int ID категории статьи
     */
    public $subcategoryId = null;

    /**
     * @var string Краткое описание статьи
     */
    public $summary = null;

    /**
     * @var string HTML содержание статьи
     */
    public $content = null;

    /**
     * @var array модификатор доступа
     */
    public $access = [];

    /**
     * @var array доступные модификаторы доступа
     */
    public static $accessValues = [
        0 => 'Закрыт для просмотра',
        1 => 'Открыт для просмотра',
        2 => 'Открыт для редактирование / просмотра',
    ];

    /**
     * Создаст объект статьи
     *
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data = array())
    {
        if (isset($data['id'])) {
            $this->id = (int)$data['id'];
        }

        if (isset($data['publicationDate'])) {
            $this->publicationDate = (string)$data['publicationDate'];
        }

        //die(print_r($this->publicationDate));

        if (isset($data['title'])) {
            $this->title = $data['title'];
        }

        if (isset($data['categoryId'])) {
            $this->categoryId = (int)$data['categoryId'];
        }

        if (isset($data['subcategoryId'])) {
            $this->subcategoryId = (int)$data['subcategoryId'];
        }

        if (isset($data['summary'])) {
            $this->summary = $data['summary'];
        }

        if (isset($data['content'])) {
            $this->content = $data['content'];
        }

        if (isset($data['access'])) {
            $this->access = [];
            $this->access[$data['access']] = self::$accessValues[$data['access']];
        }

    }


    /**
     * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
     *
     * @param assoc Значения записи формы
     */
    public function storeFormValues($params)
    {
        // Сохраняем все параметры
        $this->__construct($params);

        // Разбираем и сохраняем дату публикации
        if (isset($params['publicationDate'])) {
            $publicationDate = explode('-', $params['publicationDate']);

            if (count($publicationDate) == 3) {
                list ($y, $m, $d) = $publicationDate;
                $this->publicationDate = mktime(0, 0, 0, $m, $d, $y);
            }
        }
    }


    /**
     * Возвращаем объект статьи соответствующий заданному ID статьи
     *
     * @param int ID статьи
     * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
     */
    public static function getById($id)
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) "
            . "AS publicationDate FROM articles WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;

        if ($row) {
            return new Article($row);
        }
    }


    /**
     * Возвращает все (или диапазон) объекты Article из базы данных
     *
     * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
     * @param int $categoryId Вернуть статьи только из категории с указанным ID
     * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
     * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
     */
    public static function getList($numRows = 1000000,
                                   $categoryId = null,
                                   $order = "publicationDate DESC",
                                   $subcategoryId = null,
                                   $shortLength = 50, $root = false)
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM articles";
        $condition = [];
        if (isset($subcategoryId)) $condition[] = "subcategoryId = :subcategoryId";
        if (isset($categoryId)) $condition[] = "categoryId = :categoryId";
        if (!$root) $condition[] = "access != 0";
        $condition_str = implode(' AND ', $condition);


        $subcategoryCondition = $subcategoryId ? "subcategoryId = :subcategoryId " : "";
        $categoryClause = $categoryId ? "categoryId = :categoryId " : "";
        $accessCondition = $root ? '' : 'access != 0 ';

        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) 
                AS publicationDate
                $fromPart " . ($condition_str ? 'WHERE ' . $condition_str . ' ' : '') .
            "ORDER BY  $order  LIMIT :numRows";


        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        /**
         * Можно использовать debugDumpParams() для отладки параметров,
         * привязанных выше с помощью bind()
         * @see https://www.php.net/manual/ru/pdostatement.debugdumpparams.php
         */

        if ($categoryId)
            $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        if ($subcategoryId)
            $st->bindValue(":subcategoryId", $subcategoryId, PDO::PARAM_INT);


        $st->execute(); // выполняем запрос к базе данных
        $list = array();


        while ($row = $st->fetch()) {
            $article = new Article($row);
            $article->summary = mb_substr($article->summary, 0, $shortLength);
            $article->summary = (strlen($article->summary) >= $shortLength
                ? trim($article->summary) . '...'
                : $article->summary);
            $list[] = $article;
        }

        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT COUNT(*) AS totalRows $fromPart" . ($categoryClause ? " WHERE $categoryClause" : "") . "";

        $st = $conn->prepare($sql);
        if ($categoryId)
            $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        $st->execute(); // выполняем запрос к базе данных
        $totalRows = $st->fetch();
        $conn = null;


        return (array(
            "results" => $list,
            "totalRows" => $totalRows[0]
        )
        );
    }

    /**
     * Вставляем текущий объект Article в базу данных, устанавливаем его ID
     */
    public function insert()
    {

        // Есть уже у объекта Article ID?
        if (!is_null($this->id)) trigger_error("Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR);

        // Вставляем статью
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO articles ( publicationDate, categoryId, title, summary, content, access ) VALUES ( FROM_UNIXTIME(:publicationDate), :categoryId, :title, :summary, :content, :access )";
        $st = $conn->prepare($sql);
        $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->bindValue(":title", $this->title, PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, PDO::PARAM_STR);
        $st->bindValue(":access", array_key_first($this->access), PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
     * Обновляем текущий объект статьи в базе данных
     */
    public function update()
    {

        // Есть ли у объекта статьи ID?
        if (is_null($this->id)) trigger_error("Article::update(): "
            . "Attempt to update an Article object "
            . "that does not have its ID property set.", E_USER_ERROR);

        // Обновляем статью
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate),"
            . " categoryId=:categoryId, title=:title, summary=:summary, access=:access,"
            . " content=:content WHERE id = :id";

        $st = $conn->prepare($sql);
        $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->bindValue(":title", $this->title, PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, PDO::PARAM_STR);
        $st->bindValue(":access", array_key_first($this->access), PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }


    /**
     * Удаляем текущий объект статьи из базы данных
     */
    public function delete()
    {

        // Есть ли у объекта статьи ID?
        if (is_null($this->id)) trigger_error("Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR);

        // Удаляем статью
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $st = $conn->prepare("DELETE FROM articles WHERE id = :id LIMIT 1");
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }

}
