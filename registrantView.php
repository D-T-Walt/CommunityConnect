<?php 
    session_start();

    // Only logged in participants can view this page
    if (empty($_SESSION['logged_in']) || empty($_SESSION['Email_Address'])) {
        header("Location: index.php");
        exit();
    }

    require "connectDatabase.php";
    $email = $_SESSION['Email_Address'];

    //Pull registration that belongs to the logged in participant 
    $stmt = $conn->prepare(
        "SELECT reg.registrationID,
            reg.registrationDate,
            CONCAT(r.firstName, ' ', lastName) AS fullName,
            r.email,
            r.phoneNumber,
            r.age,
            reg.parish,
            a.activity,
            reg.participationDate,
            GROUP_CONCAT(s.skill ORDER BY s.skill SEPARATOR ', ') AS skills
        FROM registrations reg
        JOIN registrants r ON reg.email = r.email 
        JOIN activities a ON reg.activityID = a.activityID
        LEFT JOIN registrants_skills s ON reg.email= s.email
        WHERE reg.email = ?
        GROUP BY reg.registrationID
        ORDER BY reg.registrationDate DESC, reg.registrationID DESC"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Infromation passed back from registrationDelete
        $message = $_SESSION['delete_message'] ?? "";
        unset($_SESSION['delete_message']);

        // If participant has no registrations left, remove all records relating to them
        $accountRemoved = false;

        if($result -> num_rows === 0 ){
            //Remove skills
            $del = $conn->prepare("DELETE FROM registrants_skills WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
            $del->close();

            //Remove from registrations
            $del = $conn->prepare("DELETE FROM registrations WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
            $del->close();

            //Remove from registrants
            $del = $conn->prepare("DELETE FROM registrants WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
            $accountRemoved = ($del->affected_rows > 0);
            $del->close();


            //Log out
            $_SESSION = [];
            session_destroy();

        }
        if (isset($_POST["logout"])) {
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit(); 
        }
?>
<!DOCTYPE html>
<html>

    <head>
        <title>CommunityConnect – My Registrations</title>
        <link rel="stylesheet" href="mainStyle.css">

        <style>
            body {
                background-image: url("img/bg4.jpg");
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
                <h1>My Registrations</h1>
            </div>

            <div class="header-right">
                <a href="admin.php" class="admin-link">
                    <span>&#9881;</span> Admin Portal
                </a>
            </div>

        </header>
        <?php
        if ($message !== "" && !$accountRemoved){
            echo "<h2 style='text-align: center;'>" . htmlspecialchars($message) . "</h2>";
        }
        if ($result -> num_rows > 0) {
            echo "<form method='post' class='button-form table-form'>";
            echo "<table>";
            echo "<tr><th>Select</th><th>Registration Date</th><th>Full Name</th><th>Email Address</th><th>Phone Number</th><th>Age</th><th>Parishes</th><th>Event</th><th>Participation Date</th><th>Interest Skills</th></tr>";

                //Loops through each record in registrations
                while ($row = $result -> fetch_assoc()){
                    $id = $row['registrationID'];
                    unset($row['registrationID']);

                    echo "<tr>";
                    //Each field is displayed in its own table data cell
                    echo "<td><input type='radio' name='registrationID' value='$id' required></td>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? "") . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                // Buttons at the end of the page, each sends the selected record to its own page
                echo "<div class='table-actions'>";
                echo "<input type='submit' name='portal' value='Back' formaction='registrantPortal.php' formnovalidate> ";
                echo "<input type='submit' name='update' value='Update' formaction='registrantUpdate.php'> ";
                echo "<input type='submit' name='downloadPDF' value='Download Confirmation PDF' formaction='registrationPDF.php'> ";
                echo "<input type='submit' name='delete' value='Delete' formaction='registrantDelete.php'> ";
                echo "</div>";
                echo "</form>";
            } else {
                // If the participant has no registrations
            if ($accountRemoved)
                $text =  "You have no registrations left, so your account and all your information have been removed. You have been logged out.";
            else {
                $text = "You have no registrations left. You have been logged out.";
            }
            
            echo "<div class='popup-overlay'>
            <div class='popup-box popup-success'>
                <div class='popup-icon'>✓</div>
                <h2>Account Removed</h2>
                <p>" . htmlspecialchars($text) . "</p>
                <button type='button' onclick=\"window.location.href='index.php';\">OK</button>
            </div>
            </div>";
            }
            $stmt->close();
            $conn->close();
        ?>
    </body>
</html>