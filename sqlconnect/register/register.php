<?php

use PHPMailer\PHPMailer\PHPMailer;
$error = NULL;

if(isset($_POST['submit'])){
    //Get form data
    $u = $_POST['u'];
    $p = $_POST['p'];
    $p2 = $_POST['p2'];
    $em = $_POST['e'];
    $vkey = md5(time().$u);
    
    require_once "../../sqlconnect/PHPMailer/PHPMailer.php";
    require_once "../../sqlconnect/PHPMailer/SMTP.php";
    require_once "../../sqlconnect/PHPMailer/Exception.php";

    if(strlen($u) < 8) {
        $error = "<p style='color:red;'>Your username must be at least 8 characters</p>";
    }
    if ($p2 != $p)
    {
        $error .= "<p style='color:red;'>Your passwords do not match</p>";
    }
    if($error != NULL)
    {
        echo($error);
        exit();
    }
    if($error == NULL) {
        //Form is valid

        //Connect to the database

        $con = mysqli_connect('bxh4dwfceuoiprsc0heb-mysql.services.clever-cloud.com', 'u1exqv0w4ilg7eep', 'hxQbTSldOIX8Kq9VPsJw', 'bxh4dwfceuoiprsc0heb');
        if(mysqli_connect_errno())
        {
            echo "<p style='color:red;'>1: Connection failed</p>"; //error code #1 = connection failed
            exit();
        }
        //check if name exists
        $namecheckquery = "SELECT username FROM players WHERE username='" . $u . "';";

        $emailcheckquery = "SELECT email FROM players WHERE email='" . $em . "';";

        $namecheck = mysqli_query($con, $namecheckquery) or die("<p>2: Name check query failed</p>"); //error code #2 - name check query failed

        $emailcheck = mysqli_query($con, $emailcheckquery) or die("<p>8: Email check query failed</p>"); //error code # 8 - email check query failed

        if(mysqli_num_rows($namecheck) > 0)
        {
            echo "<p style='color:red;'>3: Name already exists</p>";//error code # 3  name exists cannot register
            exit();
        }

        if(mysqli_num_rows($emailcheck) > 0)
        {
            echo "<p style='color:red;'>9: Email already exists</p>";//error code # 9  email exists cannot register
            exit();
        }
        //add user to the table
        $salt = "\$5\$rounds=5000\$" . "ruylopez" . $u . "\$";//sha 256 encryption
        //echo($salt);
        $hash = crypt($p, $salt);
        //echo($hash);
        //$insertuserquery = "INSERT INTO players (username, hashe, salt) VALUES ('" . $u . "', '" . $hash . "', '" . $salt . "');";
        $insertuserquery = "INSERT INTO players (username, hashe, salt, email, vkey) VALUES ('$u' , '$hash' , '$salt' , '$em' , '$vkey')";
        //echo($insertuserquery);
        if($con->query($insertuserquery) === TRUE)
        {
            //echo "New record created succesfully";
        }
        else{
            //THE KEYYYYY:    
            echo "<p style='color:red;'>Error: " . $sql . "<br>" . $con->error . "</p>";
            exit();
            //mysqli_query($con, $insertuserquery) or die("4: Insert player query failed"); //error code #4 - insert query failed
        }
        //mysqli_query($con, "INSERT INTO `players`(`id`, `username`, `password`) VALUES ('4', '$u', '$p');") or die("4: Failed To Write User Data To Database!"); // Error #4 Failed To Write User Data To Database
        //$to = $em;
        $subject = "XRClass Email Verification";
        $message = "<p>Hello </p>". $u . '\n' . "<p>,\nPlease verify your account using this link: </p>"."<p><a href='https://app-3ee887a2-9c63-4544-b696-68a1e1cb208a.cleverapps.io/sqlconnect/verify/verify.php?vkey=$vkey'>Register Account</a></p>";
        //$headers = "From: xrinclass@gmail.com";
        //$headers .= "MIME-Version: 1.0" . "\r\n";
        //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        //
        //mail($to,$subject,$message,$headers) or die("FailedMAIL: " . "Error: " . "<br>" . $con->error);

        //MAIL USING <PHPMAILER class=""

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail = new PHPMailer();
            //echo "dsjidsoa";
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';//'smtp.yandex.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'xrinclass@gmail.com';//'support@xrclass.ml';                     // SMTP username
            $mail->Password   = 'Houseman1';                               // SMTP password
            $mail->SMTPSecure = "ssl";//PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 465;  
            $mail->SMTPOptions = array (
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' =>true
                )
            );                                  // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        
            //Recipients
            $mail->setFrom('xrinclass@gmail.com', $u);
            //$mail->addAddress('xrinclass@gmail.com');     // Add a recipient
            $mail->addAddress($em);               // Name is optional
            $mail->addReplyTo('xrinclass@gmail.com', 'XRClass Support');
            //$mail->addCC('cc@example.com');
            $mail->addBCC('hb22holla@gmail.com');
        
            // Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        
            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            //echo 'Message has been sent';
        } catch (Exception $e) {
            echo "<p style='color:red;'>B: INVALID EMAIL</p>";//"A: Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }


        //header('location:thankyou.php');
        echo("<p style='color:green;'>Successfully Created your account. Please log on to your email and verify before you <a href='https://xrclass.ml/login'>log in</a>.</p>");
    }
    exit();
}
?>