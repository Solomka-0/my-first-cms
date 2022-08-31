<?php
require('../config.php');
$subcategories = Subcategory::getByCategoryId($_POST['category']);
?>
<li>
    <label for="subcategoryId">Подкатегория</label>
    <select name="subcategoryId">
        <?php foreach ($subcategories as $key=>$subcategory) {?>
            <option value=
                    "<?php echo $key?>" <?= $key == 0 ? 'selected' : ''?>
            > <?php echo $subcategory->name?> </option>
        <?php }?>
    </select>
</li>