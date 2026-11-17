<!DOCTYPE html>
<!-- Diwani Walters - 2303848 -->
<!--Olivia McFarlane - 2306823 -->
<?php
    //Start the session to access session variables
    session_start();
    require "connectDatabase.php";
        //Checks if the user is already logged-in in and redirect to the next page if they are
        if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
            //Retrieve the logged-in user's email
            $email = $_SESSION["Email_Address"];

            //Query to get the registrants profile information
            try{
                $st = $conn->prepare(
                    "SELECT firstName, lastName, password, age, phoneNumber
                    FROM registrants
                    WHERE email = ? LIMIT 1"
                );

                $st->bind_param("s", $email);
                $st->execute();

                $result = $st->get_result();

                //If registrant is found, populate session variable
                if($row = $result->fetch_assoc()){
                    $_SESSION["Full_Name"] = $row["firstName"] . " " . $row["lastName"];
                    $_SESSION["Password"] = $row["password"];
                    $_SESSION["Age"] = $row["age"];
                    $_SESSION["Phone_Number"] = $row["phoneNumber"];
                }

                $st->close();
            }catch(mysqli_sql_exception $ex){

            }

            header("Location: registration1.php");
            exit();
        }
        
        // Check if the button was clicked and validate the form data
        if (isset($_POST["next"])) {
        $validBool = true;
        $message = " ";

        //Make sure all required fields are filled out
        if (empty(trim($_POST['Full_Name'])) || empty(trim($_POST['Email_Address'])) || empty(trim($_POST['Phone_Number'])) || 
        empty(trim($_POST['Age'])) || empty(trim($_POST['Password'])) || empty(trim($_POST['ConfirmPassword']))) {
            echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ All fields are Required ⚠️</h1>";
            header("Refresh: 2; url=registration.php");
            exit();
        }

        //Validate Name, Email and Phone Number using regex patterns and store them in session variables if valid
        if (!preg_match("/^[A-Za-z]+(?:\s+[A-Za-z]+)+$/", $_POST['Full_Name'])) {
            $message .= "⚠️ Full name is invalid ⚠️";
            $validBool = false;
        } else {
            //Store the name in the session as well as a cookie for 20 minutes
            $_SESSION["Full_Name"] = $_POST["Full_Name"];
            setcookie("Full_Name", $_SESSION["Full_Name"], time() + 1200);
        }

        if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $_POST['Email_Address'])) {
            $message .= "<br>⚠️ Email address is invalid ⚠️";
            $validBool = false;
        } else {
            $_SESSION["Email_Address"] = $_POST["Email_Address"];
        }

        if (!preg_match('/^\d{10}$/', $_POST['Phone_Number'])) {
            $message .= "<br>⚠️ Phone Number is invalid ⚠️";
            $validBool = false;
        } else {
            $_SESSION["Phone_Number"] = $_POST["Phone_Number"];
        }

        //Validation to make sure the user is at least 14 years old
        if(trim($_POST['Age']) < 14) {
            $message .= "<br>⚠️ Age must be at least 14 ⚠️";
            $validBool = false;
        } else {
            $_SESSION["Age"] = $_POST["Age"];
        }

        //Validation to make sure the same password was entered twice
        if(trim($_POST['Password']) !== trim($_POST['ConfirmPassword'])) {
            $message .= "<br>⚠️ Password Confirmation was Incorrect ⚠️";
            $validBool = false;
        } else {
            
            $_SESSION["Password"] = password_hash($_POST["Password"], PASSWORD_DEFAULT);
        }
        
        //If there are any validation violations, error is displayed and redirection happens
        //Otherwise, the user is allowed to proceed to the next page
        if(!$validBool){
            echo "<div class='popup-overlay'>
                    <div class='popup-box popup-error'>
                        <div class='popup-icon'>✕</div>
                        <h2>Progression Failed</h2>
                        <p>" . $message . "</p>
                        <button type='button' onclick=\"window.location.href='registration.php';\">OK</button>
                    </div>
                </div>";
        }
        else {
            header("Location: registration1.php");
            exit();
        }
    }    

    // Check if the user has previously filled out the form and stored their name and event in cookies
    //Otherwise check if the user has previously filled out the form and stored only their name in a cookie
    //Otherwise, display a welcome message prompting the user to fill out the form
    if (isset($_COOKIE["Full_Name"]) && isset($_COOKIE["Event"])) {

        $name = $_COOKIE["Full_Name"];
        $event = $_COOKIE["Event"];

        $belowMessage = "Welcome back, " . $name . 
                "! Your previously selected event is " . 
                $event . ".";
    }  elseif(isset($_COOKIE["Full_Name"])){
        $name = $_COOKIE["Full_Name"];
        $belowMessage = "Welcome back, " . $name ;
    }   else {
        $belowMessage = "Welcome! Please fill out the form below to register for our community service and event activities.";
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
        <title>CommunityConnect – Community Service & Event Registration</title>

        <style>
            table{
                width: 100%;
                margin: 8px 0 15px;
                border: none;
                border-collapse: collapse;
                background-color: transparent;
                box-shadow: none;
                border-radius: 0;
                overflow: visible;
            }

            table tr {
                background-color: transparent;
            }

            table td {
                padding: 5px 8px;
                border-bottom: none;
                color: #294b45;
                vertical-align: middle;
            }

            table tr:hover td {
                background-color: transparent;
            }

            table td:first-child {
                width: auto;
                text-align: left;
            }

            table td:last-child {
                width: 45px;
                text-align: center;
            }

            table label {
                font-weight: normal;
                cursor: pointer;
            }

            table input[type="checkbox"] {
                width: 16px;
                height: 16px;
                margin: 0;
                accent-color: #21866f;
                cursor: pointer;
            }

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
                <h1>Registration: Personal Information</h1>
            </div>

            <div class="header-right">
                <a href="admin.php" class="admin-link">
                    <span>&#9881;</span> Admin Portal
                </a>
            </div>

        </header>

        <?php
        // Display message if one exists
        if (!empty($belowMessage)) {
        echo "<p style='text-align: center;'><strong style='color: #176b5b;'>" . $belowMessage . "</strong></p>";
        }
        ?>
        <form method="post" enctype="multipart/form-data">
             <!--If a specific field data was stored in the session before, display the data in the input field. Otherwise, display an empty input field-->
            <p>
                <label><strong>Full Name:</strong></label><br>
                <?php
                if (isset($_SESSION["Full_Name"])) {
                    echo "<input type=\"text\" name=\"Full_Name\" value=\"" . $_SESSION["Full_Name"] . "\" placeholder=\"Ex-James Anderson\" >";
                } else {
                    echo "<input type=\"text\" name=\"Full_Name\" placeholder=\"Ex-James Anderson\" >";
                }
                ?>
            </p>

            <p>
                <label><strong>Email:</strong></label><br>
                <?php
                if (isset($_SESSION["Email_Address"])) {
                    echo "<input type=\"email\" name=\"Email_Address\" value=\"" . $_SESSION["Email_Address"] . "\" placeholder=\"Ex-anderson@gmail.com\" >";
                } else {
                    echo "<input type=\"email\" name=\"Email_Address\" placeholder=\"Ex-anderson@gmail.com\" >";
                }
                ?>
            </p>

            <p>
                <label><strong>Telephone Number:</strong></label><br>
                <?php
                if (isset($_SESSION["Phone_Number"])) {
                    echo "<input type=\"text\" name=\"Phone_Number\" value=\"" . $_SESSION["Phone_Number"] . "\" placeholder=\"8881234567\" title=\"10-digit number\" minlength=\"10\" maxlength=\"10\" >";
                } else {
                    echo "<input type=\"text\" name=\"Phone_Number\" placeholder=\"8881234567\" title=\"10-digit number\" minlength=\"10\" maxlength=\"10\" >";
                }
                ?>
            </p>

            <p>
                <label><strong>Age:</strong></label><br>
                <?php
                if (isset($_SESSION["Age"])) {
                    echo "<input type=\"number\" name=\"Age\" value=\"" . $_SESSION["Age"] . "\" min=\"14\" title=\"Must be atleast 14 years old\" >";
                } else {
                    echo "<input type=\"number\" name=\"Age\" min=\"14\" title=\"Must be atleast 14 years old\" >";
                }
                ?>
            </p>

            <p>
                <label><strong>Password:</strong></label><br>
                <input type="password" name="Password" placeholder="******">
            </p>

            <p>
                <label><strong>Confirm Password:</strong></label><br>
                <input type="password" name="ConfirmPassword" title="Must match the above entered password">
            </p>

            <a href = 'registrantPortal.php' class="return-form">Back</a>
                <input type="reset" name="reset" value="Clear">
                <input type="submit" name="next" value="Next">
        </form>
    </body>

</html>