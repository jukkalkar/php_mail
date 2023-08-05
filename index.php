
<?php
require 'phpmailer/phpmailer/src/PHPMailer.php';
require 'phpmailer/phpmailer/src/SMTP.php';
require 'phpmailer/phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
function validateForm($name, $contact, $email, $subject, $message) {
    $errors = array();
    if (empty($name)) {
        $errors['name'] = "Full name is required.";
    }
    if (empty($contact) || !preg_match('/^\d{10}$/', $contact)) {
        $errors['contact'] = "Please enter a valid 10-digit phone number.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if (empty($subject)) {
        $errors['subject'] = "Subject is required.";
    }
    if (empty($message)) {
        $errors['message'] = "Message is required.";
    }
    return $errors;
}
function sendEmail($fullname, $number, $email, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        include('mailconfig.php');
        $mail->setFrom('abubakarjukkalkar7@gmail.com', 'AJ');
        $mailSubject = $subject;
        $template = file_get_contents('template.php');
        $template = str_replace('{{firstname}}', $fullname, $template);
        $template = str_replace('{{contact}}', $number, $template);
        $template = str_replace('{{email}}', $email, $template);
        $template = str_replace('{{subject}}', $subject, $template);
        $template = str_replace('{{message}}', $message, $template);
        $mail->addAddress('jukkalkaraiampvpi@gmail.com');
        $mail->Subject = $subject;
        $mail->Body = $template;
        $mail->isHTML(true);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
$name = $contact = $email = $subject = $message = $message_result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    date_default_timezone_set('Asia/Kolkata');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');
    $errors = validateForm($name, $contact, $email, $subject, $message);

    if (empty($errors)) {
        include('config.php'); // Include your database connection file
        $sql = "INSERT INTO contact_form (name, contact, email, subject, message, ip_address, timestamp) VALUES ('$name', '$contact', '$email', '$subject', '$message', '$ip_address', '$timestamp')";
        if ($conn->query($sql) === true) {
            if (sendEmail($name, $contact, $email, $subject, $message)) {
                $message_result = 'Data stored in the database and email sent successfully.';
            } else {
                $message_result = 'Data stored in the database, but email sending failed.';
            }
        } else {
            $message_result = 'Error storing data in the database: ' . $conn->error;
        }
        header("Location: success.php?message=" . urlencode($message_result));
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<head>
    <style>
.form-group{
    margin-top: 6px;
}

    </style>
</head>
<body>
    <form action="index.php" method="post">
        <h2>Contact form</h2>
        <div class="form-group">
            <input style="width:600px;" type="text" id="fullname" name="name" placeholder="Full name*" required value="<?php echo $name; ?>">
            <?php if (isset($errors['name'])) echo '<small>' . $errors['name'] . '</small>'; ?>
        </div>
        <div class="form-group">
            <input style="width:290px;" type="tel" name="contact" id="phone" placeholder="Phone number*" minlength="10" maxlength="10" required value="<?php echo $contact; ?>">
            <input style="width:290px; margin-left:10px;" type="email" name="email" placeholder="Email*" required value="<?php echo $email; ?>">
            <?php if (isset($errors['contact'])) echo '<small>' . $errors['contact'] . '</small>'; ?>
            <?php if (isset($errors['email'])) echo '<small>' . $errors['email'] . '</small>'; ?>
        </div>
        <div class="form-group">
            <input style="width:600px;" type="text" id="subject" name="subject" placeholder="Subject*" required value="<?php echo $subject; ?>">
            <?php if (isset($errors['subject'])) echo '<small>' . $errors['subject'] . '</small>'; ?>
        </div>
        <div class="form-group">
            <input style="width:600px; height:200px" type="text" name="message" placeholder="Message" required value="<?php echo $message; ?>">
            <?php if (isset($errors['message'])) echo '<small>' . $errors['message'] . '</small>'; ?>
        </div>
        <div class="form-group submit-btn">
            <input type="submit" value="Submit">
        </div>
    </form>

    <?php if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message = htmlspecialchars($message);
} echo '<p>' . $message . '</p>'; ?>
</body>
</html>
