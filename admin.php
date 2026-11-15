<?php session_start(); // This will enable protected admin PDF downloads ?>
<!DOCTYPE html>
<!-- Diwani Walters - 2303848 -->
<html>

    <head>
        <title>CommunityConnect – Administration Dashboard</title>
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

        <header style="background-color: #176b5b; color: white; padding: 20px; width: 100%; text-align: center;">
            <h1>Administration Dashboard</h1>
        </header>

        <?php
            //$message = "";
                //Check if the button is clicked
                if (isset($_POST["adminLogin"])) {
                    if (!empty(trim($_POST['password']))) {
                        // Process admin login
                        if ($_POST['password'] === 'admin123') {
                            // ADDITION: Remember successful admin authentication for the PDF report.
                            $_SESSION['cc_admin_authenticated'] = true;
                            // Admin password is correct, display the admin dashboard
                            echo "<h2>Welcome to the Administration Dashboard</h2>";
                            // read the contents of the registrations.txt file and display them in a table
                            require "connectDatabase.php"; 

                            $sql = "SELECT reg.registrationDate,
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
                            GROUP BY reg.registrationID
                            ORDER BY reg.registrationDate DESC, reg.registrationID DESC";

                            $result = $conn->query($sql);

                            if (!$result) {
                                echo "<h1 No Registrations </h1>";
                            } elseif ($result -> num_rows > 0) {
                                echo "<table>";
                                echo "<tr><th>Registration Date</th><th>Full Name</th><th>Email Address</th><th>Phone Number</th><th>Age</th><th>Parishes</th><th>Event</th><th>Participation Date</th><th>Interest Skills</th></tr>";

                                    //Loops through each record in registrations
                                    while ($row = $result -> fetch_assoc()){
                                        echo "<tr>";
                                        //Each field is displayed in its own table data cell
                                        foreach ($row as $value) {
                                            echo "<td>" . htmlspecialchars($value ?? "") . "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    echo "</table>";
                                    $result -> free();
                                } else {
                                    //If the registrations file is empty
                                    echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ No Registrations Found ⚠️</h1>";

                                }
                                $conn->close();
                            // ADDITION: Administrative PDF report download.
                            echo "<p style='text-align:center;'><a class='return' href='adminReportPDF.php'>Download Administrative PDF Report</a></p>";
                            echo"<a href = 'index.php' style='display: block; width: 100px; margin: 20px auto; text-align: center;' class='return'>Exit</a>";

                        } else {
                            //If the password was wrong
                            echo "<div class='popup-overlay'>
                                <div class='popup-box popup-error'>
                                    <div class='popup-icon'>✕</div>
                                    <h2>Login Failed</h2>
                                    <p>Incorrect password entered.</p>
                                    <button type='button' onclick=\"window.location.href='admin.php';\">OK</button>
                                </div>
                            </div>";
                        }
                    } else {
                        //If a password wasn't entered
                        echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ Password Required ⚠️</h1>";
                        header("Refresh: 2; url=admin.php");
                        exit();
                    }                    
                }else{
                    //If the button wasn't pressed
                    echo "<form action=\"\" method=\"post\">";
                    echo "<label>Enter Admin Password:</label>";
                    echo "<input type=\"password\" name=\"password\">";
                    echo "<input type=\"submit\" name=\"adminLogin\" value=\"Login\">";
                    echo "</form>";

                    echo"<a href = 'index.php' style='display: block; width: 100px; margin: 20px auto; text-align: center;' class='return'>Exit</a>";
                }
            ?> 

    </body>

</html>
