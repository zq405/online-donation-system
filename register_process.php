<?php
session_start();
include 'connect.php';

function isValidEmail($email)
{
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))
        {
            return false;
        }
    $parts=explode('@',$email);
    if(count($parts)!=2 || empty($parts[0]) || empty($parts[1]))
        {
            return false;
        }
    if(strpos($parts[1],'.')===false)
        {
            return false;
        }

    return true;
}

function sanitizeEmail($email)
{
    $email=trim($email);
    return filter_var($email,FILTER_SANITIZE_EMAIL);
}

if($_SERVER['REQUEST_METHOD']!=='POST')
    {
        header('Location:register.php');
        exit();
    }

$role=mysqli_real_escape_string($conn,$_POST['role']);
$raw_email=$_POST['email'];
$phone=mysqli_real_escape_string($conn,$_POST['phone']??'');
$password=$_POST['password'];

$email=sanitizeEmail($raw_email);

if(empty($email)||!isValidEmail(($email)))
    {
        $_SESSION['error']="Invalid email format. Please enter a valid email address";
        header('Location:register.php');
        exit();
    }

$domain=explode('@',$email)[1];
$commonDomains=['gmail.com','yahoo.com','hotmail.com','outlook.com','live.com','icloud.com','mail.com'];

$isCommonDomain=false;
foreach($commonDomains as $common)
    {
        if($domain===$common || strpos($domain,'.'.$common)!==false)
            {
                $isCommonDomain=true;
                break;
            }
    }

if(strlen($password)<6)
    {
        $_SESSION['error']="Password must be at least 6 characters long";
        header('Location:register.php');
        exit();
    }

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
        
        $sql="INSERT INTO donors(Name, Email, Password, Phone, Register_Date)
            VALUES('$name','$email','$hashed_password','$phone',NOW())";
        if(mysqli_query($conn,$sql))
            {
                $success=true;
            }
            else
                {
                    $error="Register failed".mysqli_error($conn);
                }
    }
    else
        {
            $error="Invalid role selected";
        }
    if($success)
        {
            $_SESSION['register_success']=true;
            $_SESSION['register_message']="Registration Successful!";
            $_SESSION['user_email']=$email;

            header('Location: register_success.php');
            exit();
        }
    if($error)
        {
            $_SESSION['error']=$error;
            header('Location:register.php');
            exit();
        }
?>