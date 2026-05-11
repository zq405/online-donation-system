<?php
session_start();
include 'connect.php';

if($_SERVER['REQUEST_METHOD']!=='POST')
    {
        header('Location:register.php');
        exit();
    }

$role=mysqli_real_escape_string($conn,$_POST['role']);
$email=mysqli_real_escape_string($conn,$_POST['email']);
$phone=mysqli_real_escape_string($conn,$_POST['phone']??'');
$password=$_POST['password'];

$hashed_password=password_hash($password,PASSWORD_DEFAULT);

$error=null;

if($role==='donor')
    {
        $name=mysqli_real_escape_string($conn,$_POST['donor_name']);
        $check_sql="SELECT Donors_ID FROM donors WHERE Email='$email'";
        $check_result=mysqli_query($conn,$check_sql);

        if(mysqli_num_rows($check_result)>0)
            {
                $_SESSION['error']="Email already registered";
                header('Location:register.php');
                exit();
            }
        
        $sql="INSERT INTO donors(Name, Email, Password, Phone, Register)
            VALUES('$name','$email','$hashed_password','$phone',NOW())";
        if(mysqli_query($conn,$sql))
            {
                $_SESSION['success']="Register successfull, Please Login";
                header('Location:login.php');
                exit();
            }
            else
                {
                    $error="Register failed".mysqli_error($conn);
                }
    }
    else if($role==='recipient')
        {
            $org_name=mysqli_real_escape_string($conn,$_POST['org_name']);
            $contact_person=mysqli_real_escape_string($conn,$_POST['contact_person']);
            $address=mysqli_real_escape_string($conn,$_POST['address']);
            $name=$contact_person;

            $check_sql="SELECT Recipient_ID FROM recipient WHERE Email='$email'";
            $check_result=mysqli_query($conn,$check_sql);

            if(mysqli_num_rows($check_result)>0)
                {
                    $_SESSION['error']="Email already registered";
                    header('Location:register.php');
                    exit();
                }
            
            $sql="INSERT INTO recipient (Organization_Name,Contact_Person, Name, Email, Password, Phone, Address, Status)
                VALUES('$org_name', '$contact_person','$name','$email','$hashed_password','$phone','$address','pending')";
            
            if(mysqli_query($conn,$sql))
                {
                    $_SESSION['success']="Registration successful! Your account is pending approval";
                    header('Location:login.php');
                    exit();
                }
        }
        else
            {
                $error="Invalid role selected";
            }
    if($error)
        {
            $_SESSION['error']=$error;
            header('Location:register.php');
            exit();
        }
?>