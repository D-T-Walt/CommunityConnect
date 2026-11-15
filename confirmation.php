<!DOCTYPE html>
<!-- Diwani Walters - 2303848 -->
<?php
$file = "registrations.txt";
$message = "";
$belowMessage = "";

session_start();
require "connectDatabase.php";

    // Check if the previous form was filled out, if not error is displayed and they are redirected to the first form
    if (!isset($_SESSION["Full_Name"]) || !isset($_SESSION["Parish"])  ) {
            echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ Previous Information Has Not Been Completed⚠️</h1>";
            header("Refresh: 2; url=registration.php");
            exit();
    }

    // Check if the button was clicked
    if (isset($_POST["delete"])) {
        if (!empty(trim($_POST['newInfo'])) && preg_match("/^[A-Za-z]+(?:\s+[A-Za-z]+)+$/", $_POST['newInfo'])) {
            //use unset() to remove the previous name from the session and then store the new name in the session and cookie
            unset($_SESSION["Full_Name"]);
            $belowMessage = "Initial Full Name has been deleted.";

            $_SESSION["Full_Name"] = $_POST["newInfo"];
            setcookie("Full_Name", $_SESSION["Full_Name"], time() + 1200);
            $belowMessage .= "Your name has been updated. Please review your information and confirm.";
        } else {
            //if button is pressed and no new name is entered, display a message to the user to enter a new name
            $belowMessage = "Please enter a valid new name to update.";
        }
    }

    // Check if the confirm button was clicked
    if (isset($_POST["confirm"])) {
        $name=$_SESSION["Full_Name"];
        $email=$_SESSION["Email_Address"];
        $phone=$_SESSION["Phone_Number"];
        $age=$_SESSION["Age"];
        $parishes=$_SESSION["Parish"];
        $event=$_SESSION["Event"];
        $date=$_SESSION["Preferred_Participation_Date"];
        $skills=$_SESSION["Interest_Skills"];
        $password=$_SESSION["Password"];

        $nameParts = explode(" ", $name);
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        try {
            // Check this back so if the registrant already exists in the database
            if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
                $stRegistrant = $conn->prepare(
                    "INSERT INTO registrants (email, firstName, lastName, password, age, phoneNumber)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stRegistrant->bind_param("ssssis", $email, $firstName, $lastName, $password, $age, $phone);
                $stRegistrant->execute();
                $stRegistrant->close();
            }
            

            $activityID = null;
            $stAct = $conn->prepare(
                "SELECT activityID
                FROM activities
                WHERE activity = ? LIMIT 1"
            );

            $stAct->bind_param("s", $event);
            $stAct->execute();
            $resultAct = $stAct->get_result();

            if($rowAct = $resultAct->fetch_assoc()){
                $activityID = $rowAct["activityID"];
            }
            $stAct->close();

            if($activityID !== null){
                $regDate = date("Y-m-d");
                $stReg = $conn->prepare(
                    "INSERT INTO registrations (email, activityID, parish, participationDate, registrationDate)
                    VALUES (?, ?, ?, ?, ?)"
                );

                $stReg->bind_param("sisss", $email, $activityID, $parishes, $date, $regDate);
                $stReg->execute();
                
                //Preserve the generated registration ID for the confirmation email.
                $newRegistrationID = (int)$conn->insert_id;
            }

            $skillArr = is_array($skills) ? $skills : explode("|", $skills);
            $stSkill = $conn->prepare(
                "INSERT IGNORE INTO registrants_skills (email, skill)
                VALUES (?, ?)"
            ); 
            
            foreach($skillArr as $skill)
            {   
                $cleanSkill = trim($skill);
                if(!empty($cleanSkill)){
                    $stSkill->bind_param("ss", $email, $cleanSkill);

                    $stSkill->execute();
                }
            }
            
            //Email confirmation (does not interrupt registration if mail is unavailable). 
            //This works with new the file created called "sendRegistrationEmail"
            if ($activityID !== null && isset($stReg) && $stReg->affected_rows > 0) {
                try {
                    if (!is_file(__DIR__ . '/vendor/autoload.php')) {
                        error_log('CommunityConnect: install Composer packages before sending email.');
                    } elseif (!is_file(__DIR__ . '/sendRegistrationEmail.php')) {
                        error_log('CommunityConnect: sendRegistrationEmail.php is missing.');
                    } else {
                        require_once __DIR__ . '/sendRegistrationEmail.php';
                        $emailSent = cc_send_registration_email($email, $name, $event, $date, $parishes, $newRegistrationID);
                        if (!$emailSent) {
                            error_log('CommunityConnect: confirmation email could not be sent to ' . $email);
                        } else {
                            $message .= "A confirmation email has been sent to your email address.<br>";
                        }
                    }
                } catch (\Throwable $mailError) {
                    error_log('CommunityConnect email error: ' . $mailError->getMessage());
                }
            }
            $stSkill->close();

            $message .= "Database records saved successfully.<br>";
        }catch(mysqli_sql_exception $ex){
            $message .= "Database Error: Registration could not be saved to database.<br>";
        }

        // Clear the the cookies
        setcookie("Full_Name", "", time() - 3600);
        setcookie("Event", "", time() - 3600);

        $message.= "Your session has ended and your registration has been confirmed. Thank you for registering!<br>";

        echo "<div class='popup-overlay'>
            <div class='popup-box popup-success'>
                <div class='popup-icon'>✓</div>
                <h2>Registration Success</h2>
                <p>" . $message . "</p>
                <button type='button' onclick=\"window.location.href='registrantPortal.php';\">OK</button>
            </div>
            </div>";
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
        <link rel="stylesheet" href="mainStyle.css">

        <style>
            body {
                background-image: url("img/bg3.jpg");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                min-height: 100vh;
            }
        </style>
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
            echo "<form class=\"delete-form\" id=\"deleteForm\" action=\"\" method=\"post\">";
                echo "<table>";
                echo "<tr><th>Field</th><th>Your Entries</th><th>Edit Name</th></tr>";
                //Display the name field first with the input bax for deleting and rewriting, because the name should be at the top of the list even after update.
                echo "<tr><td>Full Name</td><td>" . htmlspecialchars($_SESSION["Full_Name"]) . "</td><td><input class=\"form-control\" type=\"text\" name=\"newInfo\" ></td></tr>";
                //For each session index and value, the index underscores are replaced with spaces for better readability
                //And the value is displayed in the entries field
                foreach ($_SESSION as $index => $value) {
                    $replacedUnderscore = str_replace("_", " ", $index);
                    //When Full_Name index is seen the loop continues to the next loop, because Full_Name is already displayed
                    if($index === "Full_Name" || $index === "Password" || $index === "logged_in" || $index === "cc_admin_authenticated"){
                        continue;
                    }else{
                        echo "<tr><td>" . htmlspecialchars($replacedUnderscore) . "</td><td>" . htmlspecialchars($value) . "</td><td></td></tr>";
                    }
                }
                echo "</table>";
                echo "<p style='text-align: center; color: #a52834;'>" . $belowMessage . "</p>";
            echo "</form>";
        ?>

        <!--Navigation Buttons-->
        <div class="button-container">

            <a href="registration1.php" class="return-form">
                Back
            </a>

            <form method="post" class="button-form">
                <input type="submit" name="delete" form="deleteForm" value="Delete & Rewrite Name">
            </form>

            <form method="post" class="button-form">
                <input type="submit" name="confirm" value="Confirm & Finish Registration">
            </form>

        </div>
    </body>

</html>