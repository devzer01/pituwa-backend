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

    if (isset($_GET['id'])) {

        $categoryId = isset($_GET['id']) ? $_GET['id'] : 0;

        $categoryId = helper::clearInt($categoryId);

        $category = new categories($dbo);
        $categoryInfo = $category->info($categoryId);

        if ($categoryInfo['error'] === true || $categoryInfo['removeAt'] != 0) {

            header("Location: /admin/categories.php");
            exit;
        }

    } else {

        header("Location: /admin/categories.php");
        exit;
    }

    if (!empty($_POST)) {

        $imgUrl = isset($_POST['imgUrl']) ? $_POST['imgUrl'] : '';

        $imgUrl = helper::clearText($imgUrl);
        $imgUrl = helper::escapeText($imgUrl);
    }

    if (strlen($imgUrl) == 0 && isset($_FILES['uploaded_file']['name'])) {

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

            $result = $category->edit($categoryId, $title, $description, $imgUrl);

            if ($result['error'] === false) {

                header("Location: /admin/categories.php");
                exit;
            }
        }
    }

    $page_id = "category_edit";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css");
    $page_title = "Edit Category";

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
                                    <h4>Edit Category</h4>
                                </div>
                            </div>

                            <form method="post" action="/admin/category_edit.php/?id=<?php echo $categoryInfo['id']; ?>" enctype="multipart/form-data">

                                <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">
                                <input type="hidden" name="imgUrl" value="<?php echo $categoryInfo['imgUrl']; ?>">

                                <div class="row">

                                    <div class="input-field col s12">
                                        <input placeholder="Title" id="title" type="text" name="title" maxlength="255" class="validate" value="<?php echo $categoryInfo['title']; ?>">
                                        <label for="title">Title</label>
                                    </div>

                                    <div class="input-field col s12">
                                        <input placeholder="Description" id="title" type="text" name="description" maxlength="255" class="validate" value="<?php echo $categoryInfo['description']; ?>">
                                        <label for="description">Description</label>
                                    </div>

                                    <div class="input-field col s12 m7 image-preview">
                                        <div class="card">
                                            <div class="card-image">
                                                <img src="<?php echo $categoryInfo['imgUrl']; ?>">
                                            </div>
                                            <div class="card-action">
                                                <a href="javascript:void(0)" onclick="Action.remove(); return false;">Delete</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="file-field input-field col s12" style="display: none">
                                        <div class="btn">
                                            <span>Image</span>
                                            <input type="file" name="uploaded_file">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text" placeholder="Image for category">
                                        </div>
                                    </div>

                                    <script type="text/javascript">

                                        window.Action || ( window.Action = {} );

                                        Action.remove = function () {

                                            $('input[name=imgUrl]').val("");
                                            $('div.file-field').show();
                                            $('div.image-preview').hide();
                                        };

                                    </script>

                                    <div class="input-field col s12">
                                        <button type="submit" class="btn waves-effect waves-light" name="" >Save</button>
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
