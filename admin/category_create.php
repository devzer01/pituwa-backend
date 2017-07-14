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

    $title = "";
    $description = "";
    $imgUrl = "";

    if (isset($_FILES['uploaded_file']['name'])) {

        $currentTime = time();
        $uploaded_file_ext = @pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);

        if (@move_uploaded_file($_FILES['uploaded_file']['tmp_name'], TEMP_PATH."{$currentTime}.".$uploaded_file_ext)) {

            $response = array();

            $imgLib = new imglib($dbo);
            $response = $imgLib->createCategoryImg(TEMP_PATH."{$currentTime}.".$uploaded_file_ext, TEMP_PATH."{$currentTime}.".$uploaded_file_ext);

            if ($response['error'] === false) {

                $result = array("error" => false,
                                "normalPhotoUrl" => $response['imgUrl']);

                $imgUrl = $result['normalPhotoUrl'];

            }

            unset($imgLib);
        }
    }

    if (!empty($_POST)) {

        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';

        $title = helper::clearText($title);
        $title = helper::escapeText($title);

        $description = helper::clearText($description);
        $description = helper::escapeText($description);

        $imgUrl = helper::clearText($imgUrl);
        $imgUrl = helper::escapeText($imgUrl);

        if ($authToken === helper::getAuthenticityToken() && !APP_DEMO) {

            $category = new categories($dbo);

            $result = $category->add($title, $description, $imgUrl);

            if ($result['error'] === false) {

                header("Location: /admin/categories.php");
                exit;
            }
        }
    }

    $page_id = "category_create";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css");
    $page_title = "Create Category";

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
                                    <h4>Create Category</h4>
                                </div>
                            </div>

                            <form method="post" action="/admin/category_create.php" enctype="multipart/form-data">

                                <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                <div class="row">

                                    <div class="input-field col s12">
                                        <input placeholder="Title" id="title" type="text" name="title" maxlength="255" class="validate" value="<?php echo stripslashes($title); ?>">
                                        <label for="title">Title</label>
                                    </div>

                                    <div class="input-field col s12">
                                        <input placeholder="Description" id="description" type="text" name="description" maxlength="255" class="validate" value="<?php echo stripslashes($description); ?>">
                                        <label for="title">Description</label>
                                    </div>

                                    <div class="file-field input-field col s12">
                                        <div class="btn">
                                            <span>Image</span>
                                            <input type="file" name="uploaded_file">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text" placeholder="Image for category. Recommended 512x512 px">
                                        </div>
                                    </div>

                                    <div class="input-field col s12">
                                        <button type="submit" class="btn waves-effect waves-light" name="" >Create</button>
                                    </div>

                                </div>

                            </form>

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
