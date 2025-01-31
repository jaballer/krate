<?php
	if (!isset($page_title)) {
		$page_title = 'KrateCMS';
	}
	$url = $url ?? '';
	$is_logged_in = isset($_SESSION['user_id']);
	
	$adminLoggedIn = admin_is_logged_in();
	if ($adminLoggedIn) {
		$adminMessage = "admin is logged in";
	} else {
		$adminMessage = "admin is not logged in";
	}

?>
<!doctype html>

<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9KJN3YRHDT"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'G-9KJN3YRHDT');
    </script>
    <meta charset="utf-8">
    <title>KrateCMS
		<?php if (isset($page_title)) {
			echo '- ' . h($page_title);
		} ?><?php if (isset($preview) && $preview) {
			echo ' [PREVIEW]';
		} ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo STYLES_PATH; ?>/public.css">
    <link rel="stylesheet" href="<?php echo STYLES_PATH; ?>/simple.css">

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/9b6vdo6p51qb89toe164crjl7qyvmjbnp3qyv43i0d4wp3mw/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body>

<?php
	if ($adminLoggedIn) {
		include($_SERVER['DOCUMENT_ROOT'] . '/../templates/components/nav_admins.php');
	}
?>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="/">KrateCMS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
			<?php include($_SERVER['DOCUMENT_ROOT'] . '/../templates/components/nav_main.php'); ?>

            <!-- Update navbar text to reflect user status -->
            <span class="navbar-text">
            <?php if ($is_logged_in): ?>
                <a class="btn btn-secondary text-white" href="/users/logout.php">Log Out</a>
            <?php else: ?>
                <a class="btn btn-primary text-white" href="/users/login.php">Log In</a>
            <?php endif; ?>
            </span>

            <ul class="navbar-nav">
                <?php if (isset($isAdmin) && $isAdmin): ?>
                    <li class="nav-item">
                        <a href="<?php echo url_for('/admin/index.php'); ?>" class="nav-link">
                            Admin Dashboard
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php echo display_session_message(); ?>
