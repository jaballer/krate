<?php
  if (isset($_POST['send'])) {
    $to = 'jabaltorres@gmail.com'; // Use your own email address
    $subject = 'Feedback from my site';

    $message = 'Name: ' . $_POST['name'] . "\r\n";
    $message .= 'Email: ' . $_POST['email'] . "\r\n";
    $message .= 'Comments: ' . $_POST['comments'];

    $headers = "From: noreply@capital-j.com\r\n";
    $headers = "Reply-To: :" . $email . "\r\n";
    $headers .= 'Content-Type: text/plain; charset=utf-8';

    $success = mail($to, $subject, $message, $headers);
  }
?>

<body>
  <?php if ($success) { ?>
  <h1>Thank You</h1>
  Your message has been sent.
  <?php } else { ?>
  <h1>Oops!</h1>
  Sorry, there was a problem sending your message.
  <?php } ?>
</body>