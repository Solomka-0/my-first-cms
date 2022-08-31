<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

<?php print_r($results['formAction']) ?>

<h1><?php echo $results['pageTitle'] ?></h1>

<form action="admin.php?action=<?php echo $results['formAction'] ?>" method="post">
    <input type="hidden" name="articleId" value="<?php echo $results['article']->id ?>">

    <?php if (isset($results['errorMessage'])) { ?>
        <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

    <ul>
        <li>
            <label for="title">Article Title</label>
            <input type="text" name="title" id="title" placeholder="Name of the article" required autofocus
                   maxlength="255" value="<?php echo htmlspecialchars($results['article']->title ?? '') ?>"/>
        </li>
        <li>
            <label for="summary">Article Summary</label>
            <textarea name="summary" id="summary" placeholder="Brief description of the article" required
                      maxlength="1000"
                      style="height: 5em;"><?php echo htmlspecialchars($results['article']->summary ?? '') ?></textarea>
        </li>
        <li>
            <label for="content">Article Content</label>
            <textarea name="content" id="content" placeholder="The HTML content of the article" required
                      maxlength="100000"
                      style="height: 30em;"><?php echo htmlspecialchars($results['article']->content ?? '') ?></textarea>
        </li>
        <li class="category-field">
            <label for="categoryId">Категория</label>
            <select name="categoryId">
                <option value="0"<?php echo !$results['article']->categoryId ? " selected" : "" ?>>(none)</option>
                <?php foreach ($results['categories'] as $category) { ?>
                    <option value="<?php echo $category->id ?>"<?php echo ($category->id == $results['article']->categoryId) ? ' selected' : "" ?>><?php echo htmlspecialchars($category->name) ?></option>
                <?php } ?>
            </select>
        </li>

        <?php
        function subcategoriesSelection(array $subcategories, int $key = null)
        {
            if (!is_null($key)) {
                foreach ($subcategories as $subcategory) {
                    if ($subcategory->category_id == $key) yield $subcategory;
                }
            } else {
                foreach ($subcategories as $subcategory) yield $subcategory;
            }
        }

        ?>

        <div id="m1">
        </div>

        <script>
            $(document).ready(function () {
                $('.category-field').bind('change', function () {
                    $.ajax({
                        url: 'ajax/getSubcategories.php',
                        type: 'POST',
                        data: {category: $('.category-field>select').val(),},
                        dataType: 'html',
                        success: function (data) {
                            $('#m1').html(data);
                        }
                    });
                })
                let div = document.getElementById('m1');
                div.innerHTML = '<li>\
                    <label for="subcategoryId">Подкатегория</label>\
                    <select name="subcategoryId">\
                        <?php foreach (subcategoriesSelection($results['subcategories'], $results['article']->categoryId) as $key=>$subcategory) {?>\
                        <option value=\
                                        "<?php echo $key?>" <?= $key == 0 ? 'selected' : ''?>\
                        > <?php echo $subcategory->name?> </option>\
                        <?php }?>\
                    </select>\
            </li>';
            })
        </script>

        <li>
            <label for="access">Модификатор доступа</label>
            <select name="access">
                <?php foreach (Article::$accessValues as $accessKey => $accessValue) { ?>
                    <option value=
                            "<?php echo $accessKey ?>" <?= $accessKey == array_key_first($results['article']->access) ? 'selected' : '' ?>
                    > <?php echo $accessValue ?> </option>
                <?php } ?>
            </select>
        </li>

        <li>
            <label for="publicationDate">Publication Date</label>
            <input type="date" name="publicationDate" id="publicationDate" placeholder="YYYY-MM-DD" required
                   maxlength="10"
                   value="<?php echo $results['article']->publicationDate ? date("Y-m-d", $results['article']->publicationDate) : "" ?>"/>
        </li>


    </ul>

    <div class="buttons">
        <input type="submit" name="saveChanges" value="Save Changes"/>
        <input type="submit" formnovalidate name="cancel" value="Cancel"/>
    </div>

</form>

<?php if ($results['article']->id) { ?>
    <p><a href="admin.php?action=deleteArticle&amp;articleId=<?php echo $results['article']->id ?>"
          onclick="return confirm('Delete This Article?')">
            Delete This Article
        </a>
    </p>
<?php } ?>

<?php include "templates/include/footer.php" ?>

              