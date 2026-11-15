<!DOCTYPE html>
<!-- Diwani Walters - 2303848 -->

<?php
    //Start the session to access session variables
    session_start();
    if(!isset($_SESSION["logged_in"])) {
        header("Location: index.php");
        exit();
    }

    if (isset($_POST["logout"])) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit(); 
    }
?>

<html>

    <head>
        <title>CommunityConnect: Registrant Portal</title>

        <link rel="stylesheet" href="mainStyle.css">
    </head>

    <body>

        <header class="page-header">

            <div class="header-left">
                <?php
                    if (isset($_SESSION["logged_in"])) {
                        echo "
                            <form method='post'>
                                <input type='submit' name='logout' value='Logout' class='logout'>
                            </form>
                        ";
                    }
                ?>
            </div>

            <div class="header-center">
                <h1>Registrant Portal</h1>
            </div>

            <div class="header-right">
                <a href="admin.php" class="admin-link">
                    <span>&#9881;</span> Admin Portal
                </a>
            </div>

        </header>

        <div class="portal-buttons">
            <a href="registration.php" class="portal-button">
                <span class="portal-icon">&#128221;</span>
                <span class="portal-button-title">Register</span>
                <span class="portal-button-description">
                    Register to volunteer at a Community Event.
                </span>
                <span class="portal-arrow">&#10140;</span>
            </a>

            <a href="registrantView.php" class="portal-button">
                <span class="portal-icon">&#9881;</span>
                <span class="portal-button-title">Manage Registrations</span>
                <span class="portal-button-description">
                    Update/Delete your registrations.
                </span>
                <span class="portal-arrow">&#10140;</span>
            </a>

        </div>
        
    </body>

</html>