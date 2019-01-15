<?php defined('C5_EXECUTE') or die('Access Denied.');

if (isset($error)) {
    ?><?php echo $error?><br/><br/><?php

}

if (!isset($query) || !is_string($query)) {
    $query = '';
}

if (isset($title) && ($title !== '')) {
    echo '<h3>'.h($title).'</h3>';
}

?>
<form action="<?php echo $view->url($resultTarget)?>" method="get" class="ccm-search-block-form">
<?php
    echo '<div class="row">';
    echo '<div class="col-md-9">';

    if ($query === '') {
        ?><input name="search_paths[]" type="hidden" value="<?php echo htmlentities($baseSearchPath, ENT_COMPAT, APP_CHARSET) ?>" /><?php

    } elseif (isset($_REQUEST['search_paths']) && is_array($_REQUEST['search_paths'])) {
        foreach ($_REQUEST['search_paths'] as $search_path) {
            ?><input style="border: 3px solid red" name="search_paths[]" type="hidden" value="<?php echo htmlentities($search_path, ENT_COMPAT, APP_CHARSET) ?>" /><?php

        }
    }
    ?>

    <input title="Search AFTM.us" name="query" type="text" value="<?php echo htmlentities($query, ENT_COMPAT, APP_CHARSET)?>" class="form-control aftm-search-block-input" />

    <?php
        echo '</div>'. // end column
            '<div class="col-md-3">';
        if (isset($buttonText) && ($buttonText !== '')) {
        ?>
            <input name="submit" type="submit" value="<?php echo h($buttonText)?>" class="btn btn-default aftm-search-block-button" /><?php
        }
        echo '</div>'; // end of column
    echo '</div>'; // end of row

    if (isset($do_search) && $do_search) {
        echo '<div class="row"><div class="col-md-12">';
        if (count($results) == 0) {
            ?><h4 style="margin-top:32px"><?php echo t('There were no results found. Please try another keyword or phrase.')?></h4><?php

        } else {
            $tt = Core::make('helper/text');
            ?><div id="searchResults"><?php
                foreach ($results as $r) {
                    $currentPageBody = $this->controller->highlightedExtendedMarkup($r->getPageIndexContent(), $query);
                    ?><div class="searchResult">
                        <h3><a href="<?php echo $r->getCollectionLink()?>"><?php echo $r->getCollectionName()?></a></h3>
                        <p><?php
                            if ($r->getCollectionDescription()) {
                                echo $this->controller->highlightedMarkup($tt->shortText($r->getCollectionDescription()), $query);
                                ?><br/><?php

                            }
                            echo $currentPageBody;
                            ?> <br/><a href="<?php echo $r->getCollectionLink()?>" class="pageLink"><?php echo $this->controller->highlightedMarkup($r->getCollectionLink(), $query)?></a>
                        </p>
                    </div><?php

                }
            ?></div><?php
            $pages = $pagination->getCurrentPageResults();
            if ($pagination->getTotalPages() > 1 && $pagination->haveToPaginate()) {
                $showPagination = true;
                echo $pagination->renderDefaultView();
            }
        }
        echo '</div></div>';
    }
?></form><?php
