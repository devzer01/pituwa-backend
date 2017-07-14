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

    $stats = new stats($dbo);

    $page_id = "main";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css");
    $page_title = "General";

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
                                <h4>Statistics</h4>
                            </div>
                        </div>

				<div class="col s12">
					<table class="striped responsive-table">
							<tbody>
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th>Count</th>
                                </tr>
                                <tr>
                                    <td>Accounts</td>
                                    <td><?php echo $stats->getAccountsTotal(); ?></td>
                                </tr>
                                <tr>
                                    <td>Active accounts</td>
                                    <td><?php echo $stats->getAccountsCount(ACCOUNT_STATE_ENABLED); ?></td>
                                </tr>
                                <tr>
                                    <td>Blocked accounts</td>
                                    <td><?php echo $stats->getAccountsCount(ACCOUNT_STATE_BLOCKED); ?></td>
                                </tr>
                                <tr>
                                    <td>Total comments</td>
                                    <td><?php echo $stats->getCommentsTotal(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total active comments (not removed)</td>
                                    <td><?php echo $stats->getCommentsCount(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total likes</td>
                                    <td><?php echo $stats->getLikesTotal(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total active likes (not removed)</td>
                                    <td><?php echo $stats->getLikesCount(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total categories</td>
                                    <td><?php echo $stats->getCategoriesTotal(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total active categories (not removed)</td>
                                    <td><?php echo $stats->getCategoriesCount(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total items</td>
                                    <td><?php echo $stats->getItemsTotal(); ?></td>
                                </tr>
                                <tr>
                                    <td>Total active items (not removed)</td>
                                    <td><?php echo $stats->getItemsCount(); ?></td>
                                </tr>
                            </tbody>
                        </table>
				</div>

				<div class="row">
					<div class="col s12">
						<h4>The recently registered users</h4>
					</div>
				</div>

				<div class="col s12">

                    <?php

                        $result = $stats->getAccounts(0);

                        $inbox_loaded = count($result['users']);

                        if ($inbox_loaded != 0) {

                        ?>

						<table class="bordered data-tables responsive-table">
							<tbody>
                                <tr>
                                    <th>Id</th>
                                    <th>Account state</th>
                                    <th>Username</th>
                                    <th>Fullname</th>
                                    <th>Facebook</th>
                                    <th>Email</th>
                                    <th>Sign up date</th>
                                    <th>Ip address</th>
                                    <th>Action</th>
                                </tr>

                            <?php

                            foreach ($result['users'] as $key => $value) {

                                draw($value);
                            }

                            ?>

                            </tbody>
                        </table>

                        <?php

                            } else {

                                ?>

                                <div class="row">
                                    <div class="col s12">
                                        <div class="card blue-grey darken-1">
                                            <div class="card-content white-text">
                                                <span class="card-title">List is empty.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php
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

    function draw($user)
    {
        ?>

        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php if ($user['state'] == 0) {echo "Enabled";} else {echo "Blocked";} ?></td>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['fullname']; ?></td>
            <td><?php if (strlen($user['fb_id']) == 0) {echo "Not connected to facebook.";} else {echo "<a target=\"_blank\" href=\"https://www.facebook.com/app_scoped_user_id/{$user['fb_id']}\">Facebook account link</a>";} ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo date("Y-m-d H:i:s", $user['regtime']); ?></td>
            <td><?php if (!APP_DEMO) {echo $user['ip_addr'];} else {echo "It is not available in the demo version";} ?></td>
            <td><a href="/admin/profile.php/?id=<?php echo $user['id']; ?>">Go to account</a></td>
        </tr>

        <?php
    }