<?php include "templates/include/header.php" ?>
	  
    <h1><?php echo htmlspecialchars( $results['pageHeading'] ) ?></h1>
    
    <?php if ( isset($results['category'])) { ?>
    <h3 class="categoryDescription"><?php echo htmlspecialchars( $results['category']->description ) ?></h3>
    <?php } ?>

    <ul id="headlines" class="archive">

    <?php foreach ( $results['articles'] as $article ) { ?>

            <li>
                <h2>
                    <span class="pubDate">
                        <?php echo date('j F Y', $article->publicationDate)?>
                    </span>
                    <a href=".?action=viewArticle&amp;articleId=<?php echo $article->id?>">
                        <?php echo htmlspecialchars( $article->title )?>
                    </a>
                    <?php
                    if (isset($article->categoryId)) { ?>
                        <span class="category">
                        in
                        <a href=".?action=archive&amp;categoryId=<?php echo $article->categoryId; ?>">
                            <?php
                            echo htmlspecialchars($results['categories'][$article->categoryId]->name)
                            ?>
                        </a>
                        <a href=".?action=archive&amp;subcategoryId=<?php echo $results['subcategories'][$article->categoryId]->id; ?>">
                        <?='-> ' . htmlspecialchars($results['subcategories'][$article->categoryId]->name)?>
                        </a>
                    </span>
                    <?php } else { ?>
                        <span class="category">
                        <?php echo "Без категории" ?>
                    </span>
                    <?php } ?>
                </h2>
              <p class="summary"><?php echo htmlspecialchars( $article->summary )?></p>
            </li>

    <?php } ?>

    </ul>

    <p><?php echo $results['totalRows']?> article<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

    <p><a href="./">Return to Homepage</a></p>
	  
<?php include "templates/include/footer.php" ?>