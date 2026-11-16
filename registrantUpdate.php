<?php
    /*Olivia McFarlane - 2301555 */
    session_start();
    require "connectDatabase.php";

    if (empty($_SESSION['logged_in']) || empty($_SESSION['Email_Address'])) {
        header("Location: index.php");
        exit();
    }

    if(!isset($_POST['registrationID'])){
        header("Location: registrantView.php");
        exit();
    }

    $message = "";
    $validBool = true;
    $email = $_SESSION['Email_Address'];
    $registrationID = (int)$_POST['registrationID'];
    
    $availableSkills = [
        "Communication",
        "Organizing Events",
        "Fundraising",
        "Cooking/Food Preparation",
        "Technology/IT",
        "Teaching/Tutoring",
        "Teamwork",
        "First Aid",
        "Problem Solving",
        "Photography/Media"
    ];

    if(isset($_POST["save"])){
        if (empty(trim($_POST['Full_Name'])) || empty(trim($_POST['Phone_Number'])) || empty(trim($_POST['Age'])) || (empty(($_POST['Parish'])) || empty(($_POST['Event'])) || 
        empty(($_POST['Preferred_Participation_Date'])) || empty($_POST['Interest_Skills']))){
            $message .= "⚠️ A field is empty!  All fields are required ⚠️";
            $validBool = false;
        }

        $name=trim($_POST["Full_Name"]);
        $phone=trim($_POST["Phone_Number"]);
        $age=(int)$_POST["Age"];
        $parishes=trim($_POST["Parish"]);
        $event=trim($_POST["Event"]);
        $date=trim($_POST["Preferred_Participation_Date"]);
        $skills=$_POST["Interest_Skills"] ?? [];

        //Validate Name and Phone Number using regex patterns and store them in session variables if valid
        if (!preg_match("/^[A-Za-z]+(?:\s+[A-Za-z]+)+$/", $name)) {
            $message .= "<br>⚠️ Full name is invalid ⚠️";
            $validBool = false;
        }

        if (!preg_match('/^\d{10}$/', $phone)) {
            $message .= "<br>⚠️ Phone Number is invalid ⚠️";
            $validBool = false;
        }

        //Validation to make sure the user is at least 14 years old
        if($age < 14) {
            $message .= "<br>⚠️ Age must be at least 14 ⚠️";
            $validBool = false;
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if(!$d || $d->format('Y-m-d') !== $date || $date < "2027-01-01" || $date > "2027-12-31") {
            $message .= "<br>⚠️ Invalid Date Format. Date must be within 2027. ⚠️";
            $validBool = false;
        }

        if(array_diff($skills, $availableSkills)) {
            $message .= "<br>⚠️ Invalid Interest/Skills Selection ⚠️";
            $validBool = false;
        }
        
        if($validBool){
            $nameParts = explode(" ", $name);
            $firstName = $nameParts[0];
            $lastName = end($nameParts);

            try{
                $stRegistrant = $conn->prepare(
                    "UPDATE registrants
                    SET firstName = ?,lastName = ?, age = ?, phoneNumber = ?
                    WHERE email = ?"
                );

                $stRegistrant->bind_param("ssiss", $firstName, $lastName, $age, $phone, $email);
                $stRegistrant->execute();
                $stRegistrant->close();

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
                    $stReg = $conn->prepare(
                        "UPDATE registrations 
                        SET activityID = ?, parish = ?, participationDate = ?
                        WHERE registrationID = ? AND email = ?"
                    );
                    $stReg->bind_param("issis", $activityID, $parishes, $date, $registrationID, $email);
                    $stReg->execute();
                    $stReg->close();
                }

                $stDelSkills = $conn->prepare(
                    "DELETE FROM registrants_skills
                    WHERE email = ?"
                );

                $stDelSkills->bind_param("s", $email);
                $stDelSkills->execute();
                $stDelSkills->close();

                $stSkill = $conn->prepare(
                    "INSERT IGNORE INTO registrants_skills (email, skill)
                    VALUES (?, ?)"
                ); 
                
                $skillArr = is_array($skills) ? $skills : explode(",", $skills);
                
                foreach($skillArr as $skill)
                {   
                    $cleanSkill = trim($skill);
                    if(!empty($cleanSkill)){
                        $stSkill->bind_param("ss", $email, $cleanSkill);
                        $stSkill->execute();
                    }
                }
                $stSkill->close();

                $_SESSION['delete_message'] = "Database record updated successfully.";
                header("Location: registrantView.php");
                exit();
            }catch(mysqli_sql_exception $ex){
                $message = "Database Error: Could not update registration details.";
            }
        }
        
    }

    try{
        $st = $conn->prepare(
            "SELECT reg.registrationID,
                    CONCAT(r.firstName, ' ', r.lastName) AS fullName,
                    r.email,
                    r.phoneNumber,
                    r.age,
                    reg.parish,
                    a.activity,
                    reg.participationDate
            FROM registrations reg
            JOIN registrants r ON reg.email = r.email
            JOIN activities a ON reg.activityID = a.activityID
            WHERE reg.registrationID = ? AND reg.email = ? LIMIT 1"
        );
        $st->bind_param("is", $registrationID, $email);
        $st->execute();
        $currRec = $st->get_result()->fetch_assoc();
        $st->close();

        if(!$currRec){
            header("Location: registrantView.php");
            exit();
        }

        $actResult = $conn->query(
            "SELECT activity
            FROM activities"
        );

        $stSkills = $conn->prepare(
            "SELECT skill
            FROM registrants_skills
            WHERE email = ?"
        );
        $stSkills->bind_param("s", $email);
        $stSkills->execute();
        $resSkills = $stSkills->get_result();
        $userSkills = [];
        while($sRow = $resSkills->fetch_assoc()){
            $userSkills[] = $sRow["skill"];
        }
        $stSkills->close();
    }catch(mysqli_sql_exception $ex){
        die("Error loading registration data.");
    }
    
    if(isset($_POST["cancel"])){
        header("Location: registrantView.php");
        exit();
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
        <title>CommunityConnect - Update Registration</title>
        <link rel="stylesheet" href="mainStyle.css">

        <style>
            body {
                background-image: url("img/backG2.jpg");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                min-height: 100vh;
            }
            .edit-table{
                width: 58%;
                max-width:780px;
                margin: 25px auto 15px;
                border-collapse: collapse;
                background-color: rgba(255, 255, 255, 0.95);
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .edit-table th, .edit-table td{
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            .edit-table th{
                background-color: #176b5b;
                color: white;
            }
            .checkbox-container{
                background-color: #fff;
                border-radius: 12px;
                padding: 15px 20px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: none;
                box-sizing: border-box;
            }
            .checkbox-table{
                width: 100%;
                margin: 0;
                border: none;
                box-shadow: none;
                border-collapse: collapse;
            }
            .checkbox-table td{
                width: 50%;
                padding: 6px 12px;
                vertical-align: top;
                border: none;
            }
            .checkbox-group label{
                display: flex;
                align-items: center;
                margin-bottom: 8px;
                cursor: pointer;
                font-weight: normal;
            }
            .checkbox-group input[type="checkbox"]{
                width: auto;
                margin: 0 8px 0 0;
                accent-color: #176b5b;
            }
            .delete-form .table-actions{
                width: 58%;
                max-width: 780px;
                margin: 15px auto 35px;
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
                <h1>Update Registration #<?php echo $registrationID;?></h1>
            </div>

            <div class="header-right">
                <a href="admin.php" class="admin-link">
                    <span>&#9881;</span> Admin Portal
                </a>
            </div>
        </header>

        <?php
            if (!empty($message)) {
                echo "<p class='error-text'>$message</p>";
            }
        ?>

        <form class = "delete-form" method="post" action="registrantUpdate.php">
            <input type="hidden" name="registrationID" value="<?php echo $registrationID; ?>">

            <table class="edit-table">
                <tr>
                    <th>Field</th>
                    <th>Current / Editable Entry</th>
                </tr>

                <tr>
                    <td><strong>Full Name:</strong></td>
                    <td><input type="text" name="Full_Name" value="<?php echo htmlspecialchars($currRec['fullName']); ?>" required></td>
                </tr>
                <tr>
                    <td><strong>Email Address:</strong></td>
                    <td><input type="email" name="Email_Address" value="<?php echo htmlspecialchars($currRec['email']); ?>" disabled></td>
                </tr>
                <tr>
                    <td><strong>Phone Number:</strong></td>
                    <td><input type="text" name="Phone_Number" value="<?php echo htmlspecialchars($currRec['phoneNumber']); ?>" required></td>
                </tr>
                <tr>
                    <td><strong>Age:</strong></td>
                    <td><input type="number" name="Age" value="<?php echo htmlspecialchars($currRec['age']); ?>" required></td>
                </tr>
                <tr>
                    <td><strong>Parish:</strong></td>
                    <td>
                        <select name="Parish" required>
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

                                foreach($parishes as $key => $value){
                                    $selected = ($currRec['parish'] === $key) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($key) . "' $selected>" . htmlspecialchars($value) . "</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><strong>Activity/Event:</strong></td>
                    <td>
                        <select name="Event" required>
                            <?php
                                while ($actRow = $actResult->fetch_assoc()) {
                                    $selected = ($actRow['activity'] === $currRec['activity']) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($actRow['activity']) . "' $selected>" . htmlspecialchars($actRow['activity']) . "</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><strong>Preferred Participation Date:</strong></td>
                    <td><input type="date" name="Preferred_Participation_Date" value="<?php echo htmlspecialchars($currRec['participationDate']); ?>" required></td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding-top: 20px;"><strong>Interest/Skills:</strong></td>
                    <td class="checkbox-group">
                        <div class = "checkbox-container">
                            <table class = "checkbox-table">
                                <tr>
                                    <td>
                                        <?php
                                            $half = ceil(count($availableSkills) / 2);
                                            $firstHalf = array_slice($availableSkills, 0, $half);
                                            $secondHalf = array_slice($availableSkills, $half);

                                            foreach ($firstHalf as $skill) {
                                                $checked = in_array($skill, $userSkills) ? 'checked' : '';
                                                echo "<label><input type='checkbox' name='Interest_Skills[]' value='" . htmlspecialchars($skill) . "' $checked> " . htmlspecialchars($skill) . "</label>";
                                            }
                                            echo "</td><td>";
                                            foreach ($secondHalf as $skill) {
                                                $checked = in_array($skill, $userSkills) ? 'checked' : '';
                                                echo "<label><input type='checkbox' name='Interest_Skills[]' value='" . htmlspecialchars($skill) . "' $checked> " . htmlspecialchars($skill) . "</label>";
                                            }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
            
            <div class='table-actions'>
                <input type="submit" name="cancel" value="Cancel">
                <input type="submit" name="save" value="Save Changes">
            </div>
        </form>
    </body>

</html>
