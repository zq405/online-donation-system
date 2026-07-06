<?php
session_start();
include 'connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #donorsFields{transition:all 0.3s ease;}
        .input-group
        {
            position: relative;
        }
        .input-group .input-icon
        {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }

        .input-error
        {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }
        .input-success
        {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
        }
        .input-warning
        {
            border-color: #f59e0b !important;
            background-color: #fffbeb !important;
        }
        .validation-message
        {
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }
        .validation-message.input-error
        {
            color: #dc2626;
            display: block;
        }
        .validation-message.success
        {
            color: #22c55e;
            display: block;
        }
        .validation-message.warning
        {
            color: #f59e0b;
            display: block;
        }

        .password-strength
        {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .password-strength .bar
        {
            height: 4px;
            flex: 1;
            background: #e0e0e0;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        .password-strength .bar.weak
        {
            background: #dc2626;
        }
        .password-strength .bar.medium
        {
            background: #f59e0b;
        }
        .password-strength .bar.strong
        {
            background: #22c55e;
        }
        .strength-text
        {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
        }
        .strength-text.weak{color: #dc2626;}
        .strength-text.medium{color: #f59e0b;}
        .strength-text.strong{color: #22c55e;}
    </style>
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
                </select>
            </div>
            <div id="donorsFields">
                <label>Username</label>
                <input type="text" name="donor_name" autocomplete="name" placeholder="Enter Username">
            </div>
            <div>
                <label for="email">Email</label>
                <div class="input-group">
                    <input type="email" name="email" id="email" autocomplete="email" placeholder="name@example.com" required oninput="validateEmail()" onblur="validateEmail()">
                    <span class="input-icon" id="emailIcon">📧</span>
                </div>
                <div class="validation-message" id="emailMessage"></div>
            </div>
            <div>
                <label for="phone">Phone</label>
                <div class="input-group">
                    <input type="tel" name="phone" id="phone" autocomplete="tel" placeholder="Enter phone number" required oninput="validatePhone()" onblur="validatePhone()">
                    <span class="input-icon" id="phoneIcon">📱</span>
                </div>
                <div class="validation-message" id="phoneMessage"></div>
            </div>
            <div>
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Create a password(min 6 characters)" required oninput="checkPasswordStrength()">
                    <span class="input-icon" id="pwdIcon">🔒</span>
                </div>
                <div class="password-strength" id="strengthBar">
                    <div class="bar" id="bar1"></div>
                    <div class="bar" id="bar2"></div>
                    <div class="bar" id="bar3"></div>
                </div>
                <div class="strength-text" id="strengthText">Enter a password</div>
            </div>
            <div>
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" placeholder="Confirm your password" required oninput="validateConfirmPassword()">
                    <span class="input-icon" id="confirmIcon">🔒</span>
                </div>        
                <div class="validation-message" id="confirmMessage"></div>
            </div>
            <button type="submit" id="regiusterBtn">Register</button>
        </form>
        <div class="link">
            Already have an account?<a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        function validateEmail()
        {
            const emailInput=document.getElementById('email');
            const emailMessage=document.getElementById('emailMessage');
            const emailIcon=document.getElementById('emailIcon');
            const email=emailInput.value.trim();
            const emailPattern=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const commonDomains=['gmail.com','yahoo.com','hotmail.com','outlook.com','live.com','icloud.com','mail.com'];

            if(email==='')
            {
                emailInput.className='';
                emailMessage.className='validation-message';
                emailMessage.textContent='';
                emailIcon.textContent='📧';
                return false;
            }

            if(!emailPattern.test(email))
            {
                emailInput.className='input-error';
                emailMessage.className='validation-message error';
                emailMessage.textContent='Invalid email format. Example: name@domain.com';
                emailIcon.textContent='❌';
                return false;
            }

            const domain=email.split('@')[1].toLowerCase();
            const isCommonDomain=commonDomains.some(function(d)
            {
                return domain===d || domain.endsWith('.'+d);
            });
            if(!isCommonDomain)
            {
                emailInput.className='input-warning';
                emailMessage.className='validation-message warning';
                emailMessage.textContent='Please double check your email address'
                emailIcon.textContent='⚠️'
                return true;
            }

            emailInput.className='input-success';
            emailMessage.className='validation-message success';
            emailMessage.textContent='Valid email address';
            emailIcon.textContent='✓';
            return true;
        }

        function validatePhone()
        {
            const phoneInput=document.getElementById('phone');
            const phoneMessage=document.getElementById('phoneMessage');
            const phoneIcon=document.getElementById('phoneIcon');
            const phone=phoneInput.value.trim();
            const numbersOnlyPattern=/^[0-9]+$/;

            if(phone==='')
            {
                phoneInput.className='';
                phoneMessage.className='validation-message';
                phoneMessage.textContent='';
                phoneIcon.textContent='📱';
                return false;
            }

            if(!numbersOnlyPattern.test(phone))
            {
                phoneInput.className='input-error';
                phoneMessage.className='validation-message error';
                phoneMessage.textContent='Phone number must contain numbers only';
                phoneIcon.textContent='❌';
                return false;
            }

            if(phone.length<7)
            {
                phoneInput.className='input-warning';
                phoneMessage.className='validation-message warning';
                phoneMessage.textContent='Please enter a valid phone number';
                phoneIcon.textContent='⚠️';
                return false;
            }

            phoneInput.className='input-success';
            phoneMessage.className='validation-message success';
            phoneMessage.textContent='Valid phone number';
            phoneIcon.textContent='✓';
            return true;
        }

        function checkPasswordStrength()
        {
            const password = document.getElementById('password').value;
            const bar1 = document.getElementById('bar1');
            const bar2 = document.getElementById('bar2');
            const bar3 = document.getElementById('bar3');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            
            bar1.className = 'bar';
            bar2.className = 'bar';
            bar3.className = 'bar';
            
        
            if (password.length === 0) 
            {
                strengthText.textContent = 'Enter a password';
                strengthText.className = 'strength-text';
                return;
            }
            
            if (strength <= 2) 
            {
                bar1.className = 'bar weak';
                bar2.className = 'bar';
                bar3.className = 'bar';
                strengthText.textContent = '❌ Weak password - add uppercase, numbers, or special characters';
                strengthText.className = 'strength-text weak';
            } 
            else if (strength <= 4) 
            {
                bar1.className = 'bar medium';
                bar2.className = 'bar medium';
                bar3.className = 'bar';
                strengthText.textContent = '⚠️ Medium password - try adding more variety';
                strengthText.className = 'strength-text medium';
            } 
            else 
            {
                bar1.className = 'bar strong';
                bar2.className = 'bar strong';
                bar3.className = 'bar strong';
                strengthText.textContent = '✓ Strong password!';
                strengthText.className = 'strength-text strong';
            }
        }

        function validateConfirmPassword()
        {
            const password=document.getElementById('password').value;
            const confirm=document.getElementById('confirm_password').value;
            const confirmMessage=document.getElementById('confirmMessage');
            const confirmIcon=document.getElementById('confirmIcon');

            if(confirm==='')
            {
                confirmMessage.className='validation-message';
                confirmMessage.textContent='';
                confirmIcon.textContent='🔒';
                return false;
            }

            if(password===confirm)
            {
                confirmMessage.className='validation-message success';
                confirmMessage.textContent='Password Match';
                confirmIcon.textContent='✓';
                return true;
            }
            else
            {
                confirmMessage.className='validation-message error';
                confirmMessage.textContent='Password do not match';
                confirmIcon.textContent='❌';
                return false;
            }
            
        }

        document.getElementById('registerForm').addEventListener('submit',function(e){
            const errorMsg=document.getElementById('errorMsg');
            const isEmailValid=validateEmail();
            if (!isEmailValid) 
            {
                e.preventDefault();
                errorMsg.style.display = 'block';
                errorMsg.innerHTML = 'Please enter a valid email address.';
                document.getElementById('email').focus();
                return;
            }

            const isPhoneValid=validatePhone();
            if (!isPhoneValid)
            {
                e.preventDefault();
                errorMsg.style.display = 'block';
                errorMsg.innerHTML = 'Please enter a valid phone number (numbers only).';
                document.getElementById('phone').focus();
                return;
            }
            
            const password = document.getElementById('password').value;
            if (password.length < 6) 
            {
                e.preventDefault();
                errorMsg.style.display = 'block';
                errorMsg.innerHTML = 'Password must be at least 6 characters long.';
                document.getElementById('password').focus();
                return;
            }
            
            const isConfirmValid = validateConfirmPassword();
            if (!isConfirmValid) 
            {
                e.preventDefault();
                errorMsg.style.display = 'block';
                errorMsg.innerHTML = 'Passwords do not match.';
                document.getElementById('confirm_password').focus();
                return;
            }
            
            errorMsg.style.display = 'none';
        });
    </script>
</body>
</html>