<?php
defined('C5_EXECUTE') or die("Access Denied.");

$c = Page::getCurrentPage();

/** @var \Concrete\Core\Utility\Service\Text $th */
$th = Core::make('helper/text');
/** @var \Concrete\Core\Localization\Service\Date $dh */
$dh = Core::make('helper/date');

if ($c->isEditMode() && $controller->isBlockEmpty()) {
    ?>
    <div class="ccm-edit-mode-disabled-item"><?php echo t('Empty Page List Block.') ?></div>
    <?php
} else {
    ?>

    <div class="ccm-block-page-list-wrapper">

        <?php if (isset($pageListTitle) && $pageListTitle) {
            ?>
            <div class="ccm-block-page-list-header">
                <h5><?php echo h($pageListTitle) ?></h5>
            </div>
            <?php
        } ?>

        <?php if (isset($rssUrl) && $rssUrl) {
            ?>
            <a href="<?php echo $rssUrl ?>" target="_blank" class="ccm-block-page-list-rss-feed">
                <i class="fa fa-rss"></i>
            </a>
            <?php
        } ?>

        <div class="ccm-block-page-list-pages">

            <?php

            $includeEntryText = false;
            if (
                (isset($includeName) && $includeName)
                ||
                (isset($includeDescription) && $includeDescription)
                ||
                (isset($useButtonForLink) && $useButtonForLink)
            ) {
                $includeEntryText = true;
            }

            foreach ($pages as $page) {

                // Prepare data for each page being listed...
                $buttonClasses = 'ccm-block-page-list-read-more';
                $entryClasses = 'ccm-block-page-list-page-entry';
                $title = $page->getCollectionName();
                if ($page->getCollectionPointerExternalLink() != '') {
                    $url = $page->getCollectionPointerExternalLink();
                    if ($page->openCollectionPointerExternalLinkInNewWindow()) {
                        $target = '_blank';
                    }
                } else {
                    $url = $page->getCollectionLink();
                    $target = $page->getAttribute('nav_target');
                }
                $target = empty($target) ? '_self' : $target;
                $description = $page->getCollectionDescription();
                $description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
                $thumbnail = false;
                if ($displayThumbnail) {
                    $thumbnail = $page->getAttribute('thumbnail');
                }
                if (is_object($thumbnail) && $includeEntryText) {
                    $entryClasses = 'ccm-block-page-list-page-entry-horizontal';
                }

                $date = $dh->formatDateTime($page->getCollectionDatePublic(), true);

                // aftm stuff:

                // check for expired event
                /**
                 * @var $endDate \DateTime
                 */
                $endDate = $page->getAttribute('aftm_notice_end_date');
                if ($endDate) {
                    $today = new \DateTime();
                    // default timezone is probably UTC, so adjust for Austin, Texas or configured zone
                    $tz = \Application\Tops\sys\TopsConfiguration::getValue('timezone', 'settings', 'CST');
                    $today->setTimezone(new \DateTimeZone($tz));
                    if ($today->format('Y-m-d') > $endDate->format('Y-m-d')) {
                        continue;
                    }
                }

                $topics = $page->getAttribute('aftm_event_topics');
                $thumbnailColumn = 3;
                if (is_array($topics)) {
                    foreach ($topics as $topic) {
                        if ($topic->treeNodeName == 'Jam') {
                            $thumbnailColumn = 2;
                            break;
                        }
                    }
                }
                $descriptionColumn = 12 - $thumbnailColumn;

                $eventDateValue = $page->getAttribute('aftm_event_date', 'display');
                $eventLocationValue = $page->getAttribute('aftm_event_location', 'display');

                //Other useful page data...

                //$last_edited_by = $page->getVersionObject()->getVersionAuthorUserName();

                /* DISPLAY PAGE OWNER NAME
                 * $page_owner = UserInfo::getByID($page->getCollectionUserID());
                 * if (is_object($page_owner)) {
                 *     echo $page_owner->getUserDisplayName();
                 * }
                 */

                /* CUSTOM ATTRIBUTE EXAMPLES:
                 * $example_value = $page->getAttribute('example_attribute_handle', 'display');
                 *
                 * When you need the raw attribute value or object:
                 * $example_value = $page->getAttribute('example_attribute_handle');
                 */

                /* End data preparation. */

                /* The HTML from here through "endforeach" is repeated for every item in the list... */ ?>
                <div class="row">

                <div class="<?php echo $entryClasses ?>">

                    <?php if (is_object($thumbnail)) {
                        echo "<div class='col-md-$thumbnailColumn'>";
                        ?>

                        <!-- div class="ccm-block-page-list-page-entry-thumbnail" -->
                            <?php
                            $img = Core::make('html/image', array($thumbnail));
                            $tag = $img->getTag();
                            $tag->addClass('img-responsive');
                            $tag->addClass('img-thumbnail');
                            // $tag->addClass($thumbnailClass);
                            echo "<a href=".h($url)." target='$target')";
                            echo $tag.'</a>';
                            ?>
                        <!-- /div -->
                        <?php
                        echo '</div>';
                        echo '<div class="col-md-9">';
                    }
                    else {
                        echo "<div class='col-md-$descriptionColumn'>";
                    }

                    ?>

                    <?php if ($includeEntryText) {
                        ?>
                        <div class="ccm-block-page-list-page-entry-text">

                            <?php if (isset($includeName) && $includeName) {
                                ?>
                                <div class="ccm-block-page-list-title aftm-eventlist-title">
                                    <?php if (isset($useButtonForLink) && $useButtonForLink) {
                                        ?>
                                        <?php echo h($title); ?>
                                        <?php

                                    } else {
                                        ?>
                                        <a href="<?php echo h($url) ?>" class="aftm-eventlist-title-link"
                                           target="<?php echo h($target) ?>"><?php echo h($title) ?></a>
                                        <?php

                                    } ?>
                                </div>
                                <?php
                            } ?>

                            <?php if (isset($includeDate) && $includeDate) {
                                ?>
                                <div class="ccm-block-page-list-date"><?php echo h($date) ?></div>
                                <?php
                            } ?>

                            <p class="aftm-eventlist-detail">
                                <?php print trim($eventDateValue);?>
                                <br>
                                <?php print trim($eventLocationValue);?>
                            </p>

                            <?php if (isset($includeDescription) && $includeDescription) {
                                ?>
                                <div class="ccm-block-page-list-description"><?php echo h($description) ?></div>
                                <?php
                            } ?>

                            <?php if (isset($useButtonForLink) && $useButtonForLink) {
                                ?>
                                <div class="ccm-block-page-list-page-entry-read-more">
                                    <a href="<?php echo h($url) ?>" target="<?php echo h($target) ?>"
                                       class="<?php echo h($buttonClasses) ?>"><?php echo h($buttonLinkText) ?></a>
                                </div>
                                <?php
                            } ?>

                        </div>
                        <?php
                    } ?>
                </div>
                </div> <!-- end column -->
                </div>  <!-- end page row -->
                <?php
            }
            ?>
            <!-- end page loop -->
        </div><!-- end .ccm-block-page-list-pages -->

        <?php if (count($pages) == 0) { ?>
            <div class="ccm-block-page-list-no-pages"><?php echo h($noResultsMessage) ?></div>
        <?php } ?>

    </div><!-- end .ccm-block-page-list-wrapper -->


    <?php if ($showPagination) { ?>
        <?php echo $pagination; ?>
    <?php } ?>

    <?php

} ?>
