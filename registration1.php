<!DOCTYPE html>
<!-- Diwani Walters - 2303848 -->
<?php
session_start();
    // Check if the previous form was filled out, if not error is displayed and they are redirected to the previous form
    if (!isset($_SESSION["Full_Name"])) {
        echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ Personal Information Has Not Been Completed⚠️</h1>";
        header("Refresh: 2; url=registration.php");
        exit();
    }
    // Check if the button was clicked and validate the form data
    if (isset($_POST["register"])) {
        $validBool = true;
        $message = " ";

        /*if (!isset($_POST['Parish']) || !isset($_POST['Event']) || 
        !isset($_POST['Preferred_Participation_Date']) || empty($_POST['Interest_Skills'])) {
            echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ All fields are Required  ⚠️</h1>";
            header("Refresh: 2; url=registration1.php");
            exit();
        }*/

        //Make sure all required fields are filled out
        if (empty(($_POST['Parish'])) || empty(($_POST['Event'])) || 
        empty(($_POST['Preferred_Participation_Date'])) || empty($_POST['Interest_Skills'])) {
            echo "<h1 style='color: red; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;'>⚠️ All fields are Required ⚠️</h1>";
            header("Refresh: 2; url=registration1.php");
            exit();
        }

        //Validate the submitted parish against the 14 parishes of Jamaica and store it in the session variable if valid
        if(trim($_POST['Parish']) !== "Kingston" && trim($_POST['Parish']) !== "St. Andrew" && 
        trim($_POST['Parish']) !== "St. Thomas" && trim($_POST['Parish']) !== "Portland" && 
        trim($_POST['Parish']) !== "St. Mary" && trim($_POST['Parish']) !== "St. Ann" && 
        trim($_POST['Parish']) !== "Trelawny" && trim($_POST['Parish']) !== "St. James" && 
        trim($_POST['Parish']) !== "Hanover" && trim($_POST['Parish']) !== "Westmoreland" && 
        trim($_POST['Parish']) !== "St. Elizabeth" && trim($_POST['Parish']) !== "Manchester" && 
        trim($_POST['Parish']) !== "Clarendon" && trim($_POST['Parish']) !== "St. Catherine") {
            $message .= "<br>⚠️ Invalid Parish ⚠️";
            $validBool = false;
        } else {
            $_SESSION["Parish"] = $_POST["Parish"];
        }

        //Validate the submitted event against the 5 events offered by the organization and store it in the session variable if valid
        if(trim($_POST['Event']) !== "Community Clean-up" && trim($_POST['Event']) !== "Sports Day" && trim($_POST['Event']) !== "Tutoring" && 
        trim($_POST['Event']) !== "Donation Drive" && trim($_POST['Event']) !== "Community Beautification" && 
        trim($_POST['Event']) !== "Senior Citizen Outreach") {
            $message .= "<br>⚠️ Invalid Event ⚠️";
            $validBool = false;
        } else {
            //Store the event in the session as well as a cookie for 20 minutes
            $_SESSION["Event"] = $_POST["Event"];
            setcookie("Event", $_SESSION["Event"], time() + 1200);
        }

        //Validate the submitted preferred participation date to ensure it is within the year 2027 and store it in the session variable if valid
        if(trim($_POST['Preferred_Participation_Date']) < "2027-01-01" || 
            trim($_POST['Preferred_Participation_Date']) > "2027-12-31" || 
            empty(trim($_POST['Preferred_Participation_Date']))) {
                $message .= "<br>⚠️ Invalid Preferred Participation Date ⚠️";
                $validBool = false;
        } else {
            $_SESSION["Preferred_Participation_Date"] = $_POST["Preferred_Participation_Date"];
        }

        //Check if atleast one skill/interest was selected and check to make sure the the skills/interest selected are among the 10 skills/interests 
        //offered by the organization. If valid, store them in the session variable as a string separated by " | "
        if(!isset($_POST['Interest_Skills']) || 
            (!in_array("Communication", $_POST['Interest_Skills']) && 
                !in_array("Teaching/Tutoring", $_POST['Interest_Skills']) && 
                !in_array("Organizing Events", $_POST['Interest_Skills']) && 
                !in_array("Teamwork", $_POST['Interest_Skills']) && 
                !in_array("Fundraising", $_POST['Interest_Skills']) && 
                !in_array("First Aid", $_POST['Interest_Skills']) && 
                !in_array("Cooking/Food Preparation", $_POST['Interest_Skills']) && 
                !in_array("Problem Solving", $_POST['Interest_Skills']) && 
                !in_array("Technology/IT", $_POST['Interest_Skills']) && 
                !in_array("Photography/Media", $_POST['Interest_Skills']))) {

                $message .= "<br>⚠️ Invalid Interest/Skills Selection ⚠️";
                $validBool = false;
        } else {
            //implode the selected skills/interests into a string separated by " | " and store it in the session variable
            $interestSkills = $_POST['Interest_Skills'] ?? [];
            $skills = implode(" | ", $interestSkills);
            $_SESSION["Interest_Skills"] = $skills;
        }

        //If there are any validation violations, error is displayed and redirection happens
        //Otherwise, the user is allowed to proceed to the next page        
        if(!$validBool){
            echo "<div class='popup-overlay'>
                    <div class='popup-box popup-error'>
                        <div class='popup-icon'>✕</div>
                        <h2>Progression Failed</h2>
                        <p>" . $message . "</p>
                        <button type='button' onclick=\"window.location.href='registration1.php';\">OK</button>
                    </div>
                </div>";
        }
        else {
            header("Location: confirmation.php");
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
        }  
        elseif(isset($_COOKIE["Full_Name"])){
            $name = $_COOKIE["Full_Name"];
            $belowMessage = "Welcome back, " . $name ;
        }
        else {
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
                background-image: url("img/backG2.jpg");
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
                <h1>Registration: Event Information</h1>
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
            <p>
                <!--Associative array of parishes is created with the parish names as both keys and values.-->
                <!--Dynamic dropdown-->
                <label><strong>Parish:</strong></label>
                <select name="Parish" >
                    <?php
                        $parishes = [
                            "Kingston" => "Kingston",
                            "St. Andrew" => "St. Andrew",
                            "St. Thomas" => "St. Thomas",
                            "Portland" => "Portland",
                            "St. Mary" => "St. Mary",
                            "St. Ann" => "St. Ann",
                            "Trelawny" => "Trelawny",
                            "St. James" => "St. James",
                            "Hanover" => "Hanover",
                            "Westmoreland" => "Westmoreland",
                            "St. Elizabeth" => "St. Elizabeth",
                            "Manchester" => "Manchester",
                            "Clarendon" => "Clarendon",
                            "St. Catherine" => "St. Catherine"
                        ];

                        asort($parishes); //Sort the parishes in ascending order keeping the key to values mapping intact.

                        // Check if a parish is already selected in the session and set the default option accordingly
                        if (isset($_SESSION["Parish"])) {
                            echo "<option value=''>Choose your parish</option>";
                        } else {
                            echo "<option value='' disabled selected>Choose your parish</option>";
                        }

                        foreach ($parishes as $index => $value) {
                            $selected = (isset($_SESSION["Parish"]) && $_SESSION["Parish"] === $index)
                                ? "selected"
                                : ""; //If the current parish is stored in teh session, the variable $selected is set to "selected" to mark the option as selected in the dropdown. Otherwise, it is set to an empty string.

                            echo "<option value='$index' $selected>$value</option>";
                        }
                    ?>
                </select>
            </p>

            <p>
                <label><strong>Activity/Event:</strong></label>
                <!--Indexed array of events is created with the event names as values.-->
                <select name="Event" >
                    <?php
                        $events = [ "Community Clean-up", "Sports Day",
                        "Tutoring",
                        "Donation Drive",
                        "Community Beautification",
                        "Senior Citizen Outreach",
                        ];

                        // Check if an event is already selected in the session and set the default option accordingly
                        if (isset($_SESSION["Event"])) {
                            echo "<option value=''>Choose your event</option>";
                        } else {
                            echo "<option value='' disabled selected>Choose your event</option>";
                        }

                        foreach ($events as $index => $value) {
                            $selected = (isset($_SESSION["Event"]) && $_SESSION["Event"] === $value)
                                ? "selected"
                                : ""; //If the current event is stored in teh session, the variable $selected is set to "selected" to mark the option as selected in the dropdown. Otherwise, it is set to an empty string.

                            echo "<option value='$value' $selected>$value</option>";
                        }
                    ?>
                </select>
            </p>

            <p>
                <label><strong>Preferred Participation Date:</strong></label><br>
                <?php
                if (isset($_SESSION["Preferred_Participation_Date"])) {
                    echo "<input type=\"date\" name=\"Preferred_Participation_Date\" value=\"" . $_SESSION["Preferred_Participation_Date"] . "\" min=\"2027-01-01\" max=\"2027-12-31\" >";
                } else {
                    echo "<input type=\"date\" name=\"Preferred_Participation_Date\" min=\"2027-01-01\" max=\"2027-12-31\" >";
                }
                ?>
            </p>
            
            <!--For each of the interest/skills checkboxes, if the user has previously selected that option and it is stored in the session (by checking in the stored string), mark it as checked. Otherwise, leave it unchecked.-->
            <p>
                <label><strong>Interests/Skills:</strong></label>
                <table>
                    <tr>
                        <td><label>Communication</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Communication"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Communication") !== false)
                                    echo "checked";
                            ?>>
                        </td>

                        <td><label>Teaching/Tutoring</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Teaching/Tutoring"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Teaching/Tutoring") !== false)
                                    echo "checked";
                            ?>>
                        </td>
                    </tr>

                    <tr>
                        <td><label>Organizing Events</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Organizing Events"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Organizing Events") !== false)
                                    echo "checked";
                            ?>>
                        </td>

                        <td><label>Teamwork</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Teamwork"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Teamwork") !== false)
                                    echo "checked";
                            ?>>
                        </td>
                    </tr>

                    <tr>
                        <td><label>Fundraising</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Fundraising"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Fundraising") !== false)
                                    echo "checked";
                            ?>>
                        </td>

                        <td><label>First Aid</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="First Aid"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "First Aid") !== false)
                                    echo "checked";
                            ?>>
                        </td>
                    </tr>

                    <tr>
                        <td><label>Cooking/Food Preparation</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Cooking/Food Preparation"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Cooking/Food Preparation") !== false)
                                    echo "checked";
                            ?>>
                        </td>

                        <td><label>Problem Solving</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Problem Solving"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Problem Solving") !== false)
                                    echo "checked";
                            ?>>
                        </td>
                    </tr>

                    <tr>
                        <td><label>Technology/IT</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Technology/IT"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Technology/IT") !== false)
                                    echo "checked";
                            ?>>
                        </td>

                        <td><label>Photography/Media</label></td>
                        <td>
                            <input type="checkbox" name="Interest_Skills[]" value="Photography/Media"
                            <?php
                                if (isset($_SESSION["Interest_Skills"]) &&
                                    strpos($_SESSION["Interest_Skills"], "Photography/Media") !== false)
                                    echo "checked";
                            ?>>
                        </td>
                    </tr>
                </table> 
            </p>

                <!-- Return to the personal information page. -->
                <?php
                    if(isset($_SESSION["logged_in"])){
                        echo "<a href = 'registrantPortal.php' class=\"return-form\">Cancel</a>";
                    }
                    else {
                        echo "<a href = 'registration.php' class=\"return-form\">Back</a>";
                    }
                ?>

                
                <!-- Clear the current form fields in the browser. -->
                <input type="reset" name="reset" value="Clear">

                <!-- Submit the preferences for PHP validation and registration. -->
                <input type="submit" name="register" value="Register">
        </form>
    </body>

</html>