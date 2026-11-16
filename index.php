<!DOCTYPE html>
<!-- Diwani Walters - 2303848 -->
<?php
        session_start();
        require "connectDatabase.php";

        // Check if the button was clicked and validate the form data
        if (isset($_POST["next"])) {
        //Make sure all required fields are filled out
            if (empty(trim($_POST['user'])) || empty(trim($_POST['pw']))) {
                echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ All fields are Required ⚠️</h1>";
                header("Refresh: 2; url=index.php");
            exit();
            } else{
                $user=filter_input(INPUT_POST,'user',FILTER_VALIDATE_EMAIL);
                $pw= $_POST['pw'];
            }

            if(!$user){
                echo "<div class='popup-overlay'>
                    <div class='popup-box popup-error'>
                        <div class='popup-icon'>✕</div>
                        <h2>Log in Failed</h2>
                        <p>" . htmlspecialchars("⚠️ Invalid Email ⚠️") . "</p>
                        <button type='button' onclick=\"window.location.href='index.php';\">OK</button>
                    </div>
                </div>";
            }

            $check=$conn->prepare('SELECT email, password FROM registrants WHERE email=?');
            $check->bind_param('s',$user);
            $check->execute();

            $row = $check->get_result()->fetch_assoc();
            // Retrieve the result and check whether an enrolment record was found
            if(!$row){
                $check->close();
                echo "<div class='popup-overlay'>
                    <div class='popup-box popup-error'>
                        <div class='popup-icon'>✕</div>
                        <h2>Log in Failed</h2>
                        <p>" . htmlspecialchars("⚠️ Email Address Not Found ⚠️") . "</p>
                        <button type='button' onclick=\"window.location.href='index.php';\">OK</button>
                    </div>
                </div>";          
            } else{
                $stored_pw = $row['password'];
                if (password_verify($pw, $stored_pw)) {
                    $_SESSION['logged_in']= TRUE;
                    $_SESSION["Email_Address"] = $row['email'];
                    $check->close();
                    header("Location: registrantPortal.php");
                    exit();
                } else {
                    $check->close();
                    echo "<div class='popup-overlay'>
                            <div class='popup-box popup-error'>
                                <div class='popup-icon'>✕</div>
                                <h2>Log in Failed</h2>
                                <p>" . htmlspecialchars("⚠️ Incorrect Password ⚠️") . "</p>
                                <button type='button' onclick=\"window.location.href='index.php';\">OK</button>
                            </div>
                        </div>";
                }
            }            
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
        <title>CommunityConnect – Login</title>
        <style>
            body {
                background-image: url("img/bg1.jpg");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                min-height: 100vh;
            }

        </style>

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
                <h1>Log-In</h1>
            </div>

            <div class="header-right">
                <a href="registrantPortal.php" class="registrant-link">
                    &#128100; Registrant Portal
                </a>
                
                <a href="admin.php" class="admin-link">
                    <span>&#9881;</span> Admin Portal
                </a>
            </div>

        </header>

        <form method="post" enctype="multipart/form-data">
            <p>
                <label><strong>Email:</strong></label><br>
                <input type="email" name="user" title="Enter your email address">
            </p>

            <p>
                <label><strong>Password:</strong></label><br>
                <input type="password" name="pw" title="Enter your password">
            </p>                            
                <input type="reset" name="reset" value="Clear">
                <input type="submit" name="next" value="Log-in"><br><br>
                <a href="registration.php">New user? Click Here</a>
        </form>
    </body>

</html>