<?php 

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

function sanitizeAndValidateEmail($email)
{
    $email=trim($email);
    $email=filter_var($email,FILTER_SANITIZE_EMAIL);
    if(isValidEmail($email))
        {
            return $email;
        }

    return '';
}

?>