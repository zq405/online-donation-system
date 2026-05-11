<?php
include 'connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <div class="login-container">
        <h2>Create an Account</h2>
        <div id="errorMsg" class="error"></div>
        <form action="register_process.php" method="POST" id="registerForm">
            <div>
                <label>Register as:</label>
                <select name="role" id="role" required>
                    <option value="">Select Role</option>
                    <option value="donor">Donor</option>
                    <option value="recipient">Recipient</option>
                </select>
            </div>
            <div id="donorsFields">
                <label>Full Name</label>
                <input type="text" name="donor_name" autocomplete="name" placeholder="Enter you full name">
            </div>
            <div id="recipientFields" style="display:none;">
                <label>Organization Name</label>
                <input type="text" name="org_name" autocomplete="organization" placeholder="Enter Organizaiton name">
                <label>Contact Person Name</label>
                <input type="text" name="contact_person" placeholder="Enter contact person name">
                <label>Address</label>
                <input type="text" name="address" autocomplete="address-line1" placeholder="Enter address">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" id="email" autocomplete="email" placeholder="name@example.com" required>
            </div>
            <div>
                <label>Phone</label>
                <input type="tel" name="phone" id="phone" autocomplete="tel" placeholder="Enter phone number">
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Create a password(min 6 characters)" required>
            </div>
            <div>
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" placeholder="Confirm your password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="link">
            Already have an account?<a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        const roleSelect=document.getElementById('role');
        const donorsFields=document.getElementById('donorsFields');
        const recipientFields=document.getElementById('recipientFields');

        roleSelect.addEventListener('change',function()
        {
            if(this.value==='donor')
            {
                donorsFields.style.display='block';
                recipientFields.style.display='none';
            }
            else if(this.value==='recipient')
            {
                donorsFields.style.display='none';
                recipientFields.style.display='block';
            }
            else
            {
                donorsFields.style.display='none';
                recipientFields.style.display='none';
            }
        });

        document.getElementById('registerForm').addEventListener('submit',function(e)
        {
            const pwd=document.getElementById('password').value;
            const confirm=document.getElementById('confirm_password').value;
            
            if(pwd!==confirm)
            {
                e.preventDefault();
                document.getElementById('errorMsg').innerHTML='Password do not match';
            }
            else if(pwd.length<6)
            {
                e.preventDefault();
                document.getElementById('errorMsg').innerHTML='Password must be at least 6 characters';
            }
        });
    </script>
</body>
</html>