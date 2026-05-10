<?php
ob_start();
session_start();
require 'config/db.php';
require 'config/mail.php';

$message = "";
$success = false;
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["verify_account"])) {
    $email = trim($_POST["email"] ?? "");
    $code = trim($_POST["code"] ?? "");

    if (empty($email) || empty($code)) {
        $message = "Please enter email and verification code.";
    } else {
        $stmt = $conn->prepare("SELECT verification_code, verification_expires_at, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ((int)$user["is_verified"] === 1) {
                $message = "Email already verified. Please sign in.";
            } elseif (empty($user["verification_expires_at"]) || strtotime($user["verification_expires_at"]) < time()) {
                $message = "Verification code expired. Please resend code.";
            } elseif (password_verify($code, $user["verification_code"])) {
                $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_expires_at = NULL WHERE email = ?");
                $update->bind_param("s", $email);

                if ($update->execute()) {
                    $success = true;
                    $message = "Email verified successfully. You can now sign in.";
                } else {
                    $message = "Could not verify account.";
                }
            } else {
                $message = "Invalid verification code.";
            }
        } else {
            $message = "User not found.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["resend_code"])) {
    $email = trim($_POST["email"] ?? "");

    if (empty($email)) {
        $message = "Email is required.";
    } else {
        $stmt = $conn->prepare("SELECT first_name, last_name, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ((int)$user["is_verified"] === 1) {
                $message = "Account already verified.";
            } else {
                $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
                $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
                $expiry = date("Y-m-d H:i:s", time() + 600);

                $update = $conn->prepare("UPDATE users SET verification_code = ?, verification_expires_at = ? WHERE email = ?");
                $update->bind_param("sss", $hashedOtp, $expiry, $email);

                if ($update->execute()) {
                    $fullName = trim($user["first_name"] . " " . $user["last_name"]);
                    if (sendVerificationEmail($email, $fullName, $otp)) {
                        $message = "New verification code sent.";
                    } else {
                        $message = "Could not resend code.";
                    }
                } else {
                    $message = "Could not update verification code.";
                }
            }
        } else {
            $message = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Verify Email</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background:linear-gradient(135deg,#fff7f8,#fff0f4);
      padding:20px;
    }
    .box{
      width:100%;
      max-width:500px;
      background:#fff;
      border-radius:28px;
      padding:36px;
      box-shadow:0 20px 60px rgba(0,0,0,0.08);
    }
    h1{font-size:30px;margin-bottom:10px;color:#222;}
    p{color:#6b7280;line-height:1.8;margin-bottom:18px;}
    input{
      width:100%;
      padding:14px 16px;
      border:1px solid #ecd7e0;
      border-radius:16px;
      margin-bottom:14px;
      outline:none;
    }
    input:focus{
      border-color:#ff4f93;
      box-shadow:0 0 0 4px rgba(255,79,147,0.10);
    }
    button{
      width:100%;
      padding:14px;
      border:none;
      border-radius:16px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      font-weight:700;
      cursor:pointer;
      margin-bottom:12px;
    }
    .secondary{
      background:#fff;
      color:#ff2f7e;
      border:1px solid #f2d3df;
    }
    .msg{
      margin-bottom:14px;
      font-weight:600;
      color:<?php echo $success ? "'#16a34a'" : "'#e11d48'"; ?>;
    }
    a{
      display:inline-block;
      margin-top:10px;
      color:#ff2f7e;
      text-decoration:none;
      font-weight:600;
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>Verify Your Email</h1>
    <p>Enter the 6-digit verification code sent to your email address.</p>

    <?php if (!empty($message)) : ?>
      <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!$success) : ?>
      <form method="POST">
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
        <input type="text" name="code" maxlength="6" placeholder="Enter verification code" required>
        <button type="submit" name="verify_account">Verify Account</button>
      </form>

      <form method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <button type="submit" name="resend_code" class="secondary">Resend Code</button>
      </form>
    <?php else : ?>
      <a href="signin.php">Go to Sign In</a>
    <?php endif; ?>
  </div>
</body>
</html>
<?php ob_end_flush(); ?>