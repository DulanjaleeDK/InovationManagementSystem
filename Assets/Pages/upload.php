<?php
use Dotenv\Dotenv;
session_start();
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    echo $username;
} else {
    header("Location: ../../../index.php");
    exit();
}

$username = htmlspecialchars($_GET['un']);

require './dbconnection.php';

if (isset($_POST['submit']) && isset($_FILES['profile-pic'])) {
    echo "<pre>";
    print_r($_FILES['profile-pic']);
    echo "</pre>";

    $img_name = $_FILES['profile-pic']['name'];
    $img_size = $_FILES['profile-pic']['size'];
    $tmp_name = $_FILES['profile-pic']['tmp_name'];
    $error = $_FILES['profile-pic']['error'];

    if ($error === 0) {
        if ($img_size > 1500000) {
            $em = "Sorry, your file is too large.";
            header("Location: ./Admin/profile.php?error=$em");
        } else {
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);

            $allowed_exs = array("jpg", "jpeg", "png");

            if (in_array($img_ex_lc, $allowed_exs)) {
                $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                $img_upload_path = '../img/profilePics/' . $new_img_name;
                move_uploaded_file($tmp_name, $img_upload_path);

                // Insert into Database
                $sql = "INSERT INTO users(userName,profilePic) VALUES('$username','$new_img_name') ON DUPLICATE KEY UPDATE profilePic = '$new_img_name'";
                if (mysqli_query($connection, $sql)) {
                    echo "<p class='text-white'>Records inserted successfully.</p>";
                    // Remove old file
                    $old_img_path = '../img/profilePics/' . $_SESSION['image_url'];
                    if (file_exists($old_img_path)) {
                        unlink($old_img_path);
                    }
                } else {
                    echo "<p class='text-white'>ERROR: Could not able to execute $sql. " . mysqli_error($connection) . "</p>";
                    echo "<p class='text-white'>ERROR: Could not able to execute $sql. " . mysqli_error($conn) . "</p>";
                }
                header("Location: ./Admin/profile.php?status=success&msg=Image uploaded successfully");
            } else {
                $em = "File type is not allowed! Please upload a PNG, JPG or JPEG file.";
                header("Location: ./Admin/profile.php?status=error&msg=$em");
            }
        }
    } else {
        $em = "Unknown error occurred! Please check if the file is uploaded properly.";
        header("Location: ./Admin/profile.php?status=error&msg=$em");
    }

} else {
    header("Location: ./Admin/profile.php?status=error&msg=Please select an img file");
}