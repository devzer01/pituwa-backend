<?php

    /*!
     * ifsoft.co.uk engine v1.0
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk
     *
     * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
    }

    $page_id = "item_search";

    $error = false;
    $error_message = '';
    $query = '';
    $result = array();
    $result['items'] = array();

    $stats = new stats($dbo);
    $settings = new settings($dbo);
    $admin = new admin($dbo);

    if (isset($_GET['query'])) {

        $query = isset($_GET['query']) ? $_GET['query'] : '';

        $query = helper::clearText($query);
        $query = helper::escapeText($query);

        if (strlen($query) > 2) {

            $search = new search($dbo);

            $result = $search->itemsQuery($query, 0);
        }
    }

    helper::newAuthenticityToken();

    $css_files = array("my.css");
    $page_title = "Search Items";

    include_once($_SERVER['DOCUMENT_ROOT']."/common/header.inc.php");

?>

<body>

<?php

    include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_topbar.inc.php");
?>


<main class="content">
    <div class="row">
        <div class="col s12 m12 l12">

            <?php

                include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_banner.inc.php");
            ?>

            <div class="card">
                <div class="card-content">
                    <div class="row">
                        <div class="col s12">

                        <div class="row">
                            <div class="col s6">
                                <h4>Search Items</h4>
                            </div>
                        </div>

                        <form method="get" action="/admin/item_search.php">

                            <div class="row">
                                <div class="input-field col s7">
                                    <input type="text" class="validate" id="query" name="query" value="<?php echo stripslashes($query); ?>">
                                    <label for="query">Find items by title and/or content. Minimum of 3 characters.</label>
                                </div>

                                <div class="input-field col s2">
                                    <button type="submit" class="btn waves-effect waves-light teal btn-large" name=""><i class="material-icons">search</i></button>
                                </div>
                            </div>

                        </form>

                        <div class="col s12">

                            <?php

                                if (count($result['items']) > 0) {

                                        foreach ($result['items'] as $key => $value) {

                                            draw($value, $helper);
                                        }

                                } else {

                                        if (strlen($query) < 3) {

                                            ?>

                                            <div class="row">
                                                <div class="col s12">
                                                    <div class="card blue-grey darken-1">
                                                        <div class="card-content white-text">
                                                            <span class="card-title">Enter in the search box text. Minimum of 3 characters.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php

                                        } else {

                                            ?>

                                            <div class="row">
                                                <div class="col s12">
                                                    <div class="card blue-grey darken-1">
                                                        <div class="card-content white-text">
                                                            <span class="card-title">Matches found: 0</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                        }
                                    }
                                    ?>
                        </div>

			</div>
		  </div>
		</div>
	  </div>
	</div>
</div>
</main>

        <?php

            include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_footer.inc.php");
        ?>

        <script type="text/javascript">


        </script>

</body>
</html>

<?php

    function draw($item, $helper)
    {
        ?>

            <div class="row item" data-id="<?php echo $item['id']; ?>">
                <div class="col s5">
                    <div class="card">
                        <div class="card-image">
                            <img src="<?php echo $item['imgUrl']; ?>">
                            <span class="card-title"><?php echo $item['categoryTitle']; ?></span>
                        </div>
                        <div class="card-content">
                            <p><?php echo $item['itemTitle']; ?></p>
                        </div>
                        <div class="card-action">
                            <a href="/admin/item_edit.php/?id=<?php echo $item['id']; ?>">Edit</a>
                            <a href="/admin/item_view.php/?id=<?php echo $item['id']; ?>">View</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php
    }