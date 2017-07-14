<?php

    /*!
     * ifsoft.co.uk engine v1.0
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk
     *
     * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
    }

    $title = "";
    $description = "";
    $content = "";
    $imgUrl = "";
    $previewImgUrl = "";
    $category = 0;
    $allow_comments = "";

    if (isset($_FILES['uploaded_file']['name'])) {

        $currentTime = time();
        $uploaded_file_ext = @pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);

        if (@move_uploaded_file($_FILES['uploaded_file']['tmp_name'], TEMP_PATH."{$currentTime}.".$uploaded_file_ext)) {

            $response = array();

            $imgLib = new imglib($dbo);
            $response = $imgLib->createItemImg(TEMP_PATH."{$currentTime}.".$uploaded_file_ext, TEMP_PATH."{$currentTime}.".$uploaded_file_ext);

            if ($response['error'] === false) {

                $result = array("error" => false,
                                "normalPhotoUrl" => $response['imgUrl']);

                $imgUrl = $result['normalPhotoUrl'];
                $previewImgUrl = $result['normalPhotoUrl'];
            }

            unset($imgLib);
        }
    }

    if (!empty($_POST)) {

        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $category = isset($_POST['category']) ? $_POST['category'] : 0;

        $allow_comments = isset($_POST['allow_comments']) ? $_POST['allow_comments'] : '';

        $title = helper::clearText($title);
        $title = helper::escapeText($title);

        $description = helper::clearText($description);
        $description = helper::escapeText($description);

        $description = $title;

        $imgUrl = helper::clearText($imgUrl);
        $imgUrl = helper::escapeText($imgUrl);

        $previewImgUrl = helper::clearText($previewImgUrl);
        $previewImgUrl = helper::escapeText($previewImgUrl);

        $content = addslashes($content);

        $category = helper::clearInt($category);

        if ($allow_comments === "on") {

            $allow_comments = 1;

        } else {

            $allow_comments = 0;
        }

        if ($authToken === helper::getAuthenticityToken() && !APP_DEMO) {

            $item = new items($dbo);

            $result = $item->add($category, $title, $description, $content, $imgUrl, $previewImgUrl, $allow_comments);

            if ($result['error'] === false) {

                header("Location: /admin/item_view.php/?id=".$result['itemId']);
                exit;
            }
        }
    }

    $page_id = "item_create";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css");
    $page_title = "Create Item";

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
                                <div class="col s2">
                                    <h4>Create Item</h4>
                                </div>
                            </div>

                            <form method="post" action="/admin/item_create.php" enctype="multipart/form-data">

                                <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                <div class="row">

                                    <div class="input-field col s12">
                                        <select name="category">
                                            <option value="0" disabled selected>Choose category</option>

                                            <?php

                                                $category = new categories($dbo);
                                                $result = $category->getList();
                                                unset($category);

                                                foreach ($result['items'] as $val) {

                                                    ?>
                                                    <option value="<?php echo $val['id']; ?>"><?php echo $val['title']; ?></option>
                                                    <?php
                                                }
                                            ?>
                                        </select>
                                        <label>Category</label>
                                    </div>

                                    <div class="input-field col s12">
                                        <input placeholder="Title" id="title" type="text" name="title" maxlength="255" class="validate" value="<?php echo stripslashes($title); ?>">
                                        <label for="title">Title</label>
                                    </div>

                                    <div class="file-field input-field col s12">
                                        <div class="btn">
                                            <span>Image</span>
                                            <input type="file" name="uploaded_file">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text" placeholder="Image for item (Not necessarily, Optional)">
                                        </div>
                                    </div>

                                    <div class="input-field col s12">
                                        <textarea id="textarea1" class="materialize-textarea" name="content" rows="10" cols="80"><?php echo stripslashes($content); ?></textarea>

                                        <script type="text/javascript">

                                            tinymce.init({ selector:'textarea',

                                                toolbar: [
                                                'undo redo | styleselect | bold italic | link image',
                                                'alignleft aligncenter alignright'
                                            ],
                                                plugins: [
                                                    'image'
                                                ]});
                                            $('#textarea1').trigger('autoresize');

                                            $(document).ready(function() {

                                                $('select').material_select();
                                            });

                                        </script>

                                    </div>

                                    <div class="input-field col s12" style="margin-bottom: 20px;">
                                        <div>
                                            <input type="checkbox" id="allow_comments" checked="checked" name="allow_comments" />
                                            <label for="allow_comments">Allows comments</label>
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
