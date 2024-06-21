<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer autoloaded
require '../vendor/autoload.php';

class PHP_Email_Form {
  public $to = '';
  public $from_name = '';
  public $from_email = '';
  public $subject = '';
  public $ajax = false;
  public $smtp = false;
  private $messages = [];

  public function add_message($message, $label, $max_length = 1000) {
    $this->messages[] = [
      'label' => $label,
      'message' => substr($message, 0, $max_length)
    ];
  }

  public function send() {
    if (empty($this->to)) {
      return $this->ajax ? 'Recipient email address is missing.' : die('Recipient email address is missing.');
    }

    $message = '';
    foreach ($this->messages as $msg) {
      $message .= '<strong>' . htmlspecialchars($msg['label']) . ':</strong> ' . nl2br(htmlspecialchars($msg['message'])) . '<br>';
    }

    if ($this->smtp) {
      return $this->send_smtp($message);
    } else {
      return $this->send_mail($message);
    }
  }

  private function send_mail($message) {
    $headers = 'From: ' . $this->from_name . ' <' . $this->from_email . '>' . "\r\n" .
               'Reply-To: ' . $this->from_email . "\r\n" .
               'Content-Type: text/html; charset=UTF-8' . "\r\n";

    return mail($this->to, $this->subject, $message, $headers) ? 'OK' : 'Error sending email.';
  }

  private function send_smtp($message) {
    if (empty($this->smtp['host']) || empty($this->smtp['username']) || empty($this->smtp['password']) || empty($this->smtp['port'])) {
      return 'SMTP configuration is incomplete.';
    }

    $mail = new PHPMailer(true);

    try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = $this->smtp['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $this->smtp['username'];
      $mail->Password = $this->smtp['password'];
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $this->smtp['port'];

      // Recipients
      $mail->setFrom($this->from_email, $this->from_name);
      $mail->addAddress($this->to);

      // Content
      $mail->isHTML(true);
      $mail->Subject = $this->subject;
      $mail->Body    = $message;

      $mail->send();
      return 'OK';
    } catch (Exception $e) {
      return 'Mailer Error: ' . $mail->ErrorInfo;
    }
  }
}

?>