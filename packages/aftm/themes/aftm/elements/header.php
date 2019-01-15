<?php  defined('C5_EXECUTE') or die("Access Denied.");
$this->inc('elements/header_top.php');
$as = new GlobalArea('Header Search');
$blocks = $as->getTotalBlocksInArea();
$displayThirdColumn = $blocks > 0 || $c->isEditMode();

?>

<header>
    <div class="container">
        <div class="row" id="header-row-1">
            <div class="col-sm-8" >
                <div class="row">
                    <div class="col-sm-12" id="logo-col">
                        <a href=" /"><img alt="Austin Friends of Traditional Music" src="/packages/aftm/images/aftm-header.png" /></a>
                    </div>
                </div>

                <div class="row" id="header-row-2">
                    <div class="col-sm-12" >
                        <div>
                            <?php
                            $a = new GlobalArea('Header Navigation');
                            $a->display();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div>
                <?php
                // search area
                $as->display();
                ?>
                </div>
                <div  style="font-size: small">
                    <?php
                    $a = new GlobalArea('Small Menu');
                    $a->display();
                    ?>
                </div>

            </div>
        </div>
    </div>
</header>