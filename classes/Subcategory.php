<?php

class Subcategory
{
    /**
     * @var int ID подкатегории из базы данных
     */
    public $id = null;

    /**
     * @var string Название категории
     */
    public $name = null;

    /**
     * @var int ID категории из базы данных
     */
    public $category_id = null;

    /**
     * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
     *
     * @param assoc Значения свойств
     */
    public function __construct(array $data = array())
    {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['category_id'])) $this->category_id = $data['category_id'];
    }

    /**
     * Устанавливаем свойства объекта с использованием значений из формы редактирования
     *
     * @param assoc Значения из формы редактирования
     */
    public function storeFormValues($params)
    {

        // Store all the parameters
        $this->__construct($params);
    }

    /**
     * Возвращаем объект Subcategory, соответствующий заданному ID
     *
     * @param int ID категории
     * @return Subcategory|false Объект Category object или false, если запись не была найдена или в случае другой ошибки
     */
    public static function getById($id): Subcategory|false
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT * FROM subcategories WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        return $row ? new Subcategory($row) : false;
    }

    /**
     * Возвращаем объект Subcategory, соответствующий заданному ID
     *
     * @param int ID категории
     * @return Subcategory|false Объект Category object или false, если запись не была найдена или в случае другой ошибки
     */
    public static function getByCategoryId($id): Array | false
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT * FROM subcategories WHERE category_id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        while ($row = $st->fetch()) {
            $subcategory = new Subcategory($row);
            $list[] = $subcategory;
        }
        $conn = null;
        return $list ? $list : false;
    }

    public static function getList($numRows = 1000000, $order = "name ASC")
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM subcategories";
        $sql = "SELECT * $fromPart
            ORDER BY $order LIMIT :numRows";

        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $subcategory = new Subcategory($row);
            $list[] = $subcategory;
        }

        // Получаем общее количество подкатегорий, которые соответствуют критериям
        $sql = "SELECT COUNT(*) AS totalRows $fromPart";
        $totalRows = $conn->query($sql)->fetch();
        $conn = null;
        return (array("results" => $list, "totalRows" => $totalRows[0]));
    }

    /**
     * Вставляем текущий объект Category в базу данных и устанавливаем его свойство ID.
     */

    public function insert(): void
    {
        // У объекта Subcategory уже есть ID?
        if (!is_null($this->id)) trigger_error("Subcategory::insert(): Attempt to insert a Subcategory object that 
        already has its ID property set (to $this->id).", E_USER_ERROR);

        // Вставляем категорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO subcategories (name, category_id) VALUES (:name, :category_id)";
        $st = $conn->prepare($sql);
        $st->bindValue(":name", $this->name, PDO::PARAM_STR);
        $st->bindValue(":category_id", $this->category_id, PDO::PARAM_STR);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
     * Обновляем текущий объект Category в базе данных.
     */
    public function update(): void
    {
        // У объекта Subcategory уже есть ID?
        if (is_null($this->id)) trigger_error("Subcategory::insert(): Attempt to update a Subategory object that 
        does not have its ID property set.", E_USER_ERROR);

        // Обновляем категорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE subcategories SET name=:name, category_id=:category_id WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":name", $this->name, PDO::PARAM_STR);
        $st->bindValue(":category_id", $this->category_id, PDO::PARAM_STR);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }


    /**
     * Удаляем текущий объект Category из базы данных.
     */
    public function delete(): void
    {
        // У объекта Subcategory уже есть ID?
        if (is_null($this->id)) trigger_error("Category::delete(): Attempt to delete a Subcategory object that 
        does not have its ID property set.", E_USER_ERROR);

        // Удаляем категорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $st = $conn->prepare("DELETE FROM subcategories WHERE id = :id LIMIT 1");
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }
}