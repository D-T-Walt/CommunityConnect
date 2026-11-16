<?php
    /*Kevon Hedman - 2203313 */
    session_start();

     // Only logged in participants can delete this page
    if (empty($_SESSION['logged_in']) || empty($_SESSION['Email_Address'])) {
        header("Location: index.php");
        exit();
    }

    if(!isset($_POST['delete'],$_POST['registrationID'])){
        header("Location: registrantView.php");
        exit();
    }

    require "connectDatabase.php";

    $email = $_SESSION['Email_Address'];
    $registrationID = (int)$_POST['registrationID'];

    // Delete the registration only if it belongs to the logged in participant
    $stmt = $conn->prepare("DELETE FROM registrations WHERE registrationID = ? AND email = ?");
    
    if (!$stmt) {
        // The query could not be prepared
        $_SESSION['delete_message'] = "Deletion failed: " . $conn->error;
    } else {
        $stmt->bind_param("is", $registrationID, $email);

        if (!$stmt->execute()) {
            // The query ran into a database error
            $_SESSION['delete_message'] = "Deletion failed: " . $stmt->error;
        } elseif ($stmt->affected_rows > 0) {
            // The record was removed
            $_SESSION['delete_message'] = "Registration deleted successfully.";
        } else {
            // Nothing matched both the ID and the logged in email
            $_SESSION['delete_message'] = "Deletion failed: registration #$registrationID was not found for $email.";
        }
        $stmt->close();
    }
    $conn->close();

    // Go back to the list
    header("Location: registrantView.php");
    exit();
?>