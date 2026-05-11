<?php
include 'connect.php';

if($_SERVER['REQUEST_METHOD']!=='POST')
    {
        header('Location:login.php');
        exit();
    }

$email=mysqli_real_escape_string($conn,$_POST['email']);
$password=$_POST['password'];

$user=null;

$sql_donor="SELECT Donors_ID AS user_id, Name, Email,'donor' AS role, Password FROM donors WHERE Email='$email'";
$result_donor=mysqli_query($conn,$sql_donor);

if(mysqli_num_rows($result_donor)==1)
    {
        $user=mysqli_fetch_assoc($result_donor);
    }

if(!$user)
    {
        $sql_recipient="SELECT Recipient_ID AS user_id, Name, Email, 'recipient' AS role, Password FROM recipient WHERE Email='$email'";
        $result_recipient=mysqli_query($conn,$sql_recipient);

        if(mysqli_num_rows($result_recipient)==1)
            {
                $user=mysqli_fetch_assoc($result_recipient);
            }
    }

if(!$user)
    {
        $sql_admin="SELECT Admin_ID AS user_id, Name, Email, Role AS role, Password FROM admin WHERE Email='$email'";
        $result_admin=mysqli_query($conn,$sql_admin);
        if(mysqli_num_rows($result_admin)==1)
            {
                $user=mysqli_fetch_assoc($result_admin);
            }
    }
echo "<pre>";
echo "=== LOGIN SUCCESS DEBUG ===\n";
echo "user 变量类型: " . gettype($user) . "\n";

if($user && is_array($user)) {
    echo "user_id: " . ($user['user_id'] ?? 'NOT SET') . "\n";
    echo "user_name: " . ($user['Name'] ?? 'NOT SET') . "\n";
    echo "user_role: " . ($user['role'] ?? 'NOT SET') . "\n";
    echo "Password (hash): " . ($user['Password'] ?? 'NOT SET') . "\n";
} else {
    echo "user 不是数组，当前值: " . var_export($user, true) . "\n";
}
echo "===========================\n";
echo "</pre>";

if($user && password_verify($password, $user['Password']))
    {
        $_SESSION['user_id']=$user['user_id'];
        $_SESSION['user_name']=$user['Name'];
        $_SESSION['user_email']=$user['Email'];
        $_SESSION['user_role']=$user['role'];

        if($user['role']=='recipient')
            {
                $status_sql="SELECT Status FROM recipient WHERE Email='$email'";
                $status_result=mysqli_query($conn,$status_sql);
                $status_row=mysqli_fetch_assoc($status_result);

                if($status_row['Status']!='active')
                    {
                        $_SESSION['error']="Your account is pending approval. Please wait for admin verification";
                        session_destroy();
                        header('Location:login.php');
                        exit();
                    }
            }
            header('Location:dashboard.php');
            exit();

    }
    else
    {
        $_SESSION['error']="Invalid email or password";
        header('Location:login.php');
        exit();
    }
?>
