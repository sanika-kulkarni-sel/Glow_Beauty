<?php
session_start();
require 'config/db.php';
require 'config/mail.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$order_id = isset($_GET["order_id"]) ? (int)$_GET["order_id"] : 0;
$message = "";

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["verify_payment_otp"])) {
    $otp = trim($_POST["otp"] ?? "");

    if (empty($otp)) {
        $message = "Please enter OTP.";
    } elseif (empty($order["payment_otp_expires_at"]) || strtotime($order["payment_otp_expires_at"]) < time()) {
        $message = "OTP expired. Please request a new one.";
    } elseif (!password_verify($otp, $order["payment_otp"])) {
        $message = "Invalid OTP.";
    } else {
        $update = $conn->prepare("UPDATE orders SET payment_status = 'paid', status = 'confirmed', payment_otp = NULL, payment_otp_expires_at = NULL WHERE id = ?");
        $update->bind_param("i", $order_id);

        if ($update->execute()) {
            $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clearCart->bind_param("i", $_SESSION["user_id"]);
            $clearCart->execute();

            $userStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
            $userStmt->bind_param("i", $_SESSION["user_id"]);
            $userStmt->execute();
            $userData = $userStmt->get_result()->fetch_assoc();

            sendOrderConfirmationEmail(
                $userData["email"],
                trim($userData["first_name"] . " " . $userData["last_name"]),
                $order["id"],
                $order["transaction_id"],
                $order["total_amount"],
                $order["payment_method"]
            );

            header("Location: payment-success.php?order_id=" . $order_id);
            exit();
        } else {
            $message = "Could not verify payment.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["resend_payment_otp"])) {
    $userStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $userStmt->bind_param("i", $_SESSION["user_id"]);
    $userStmt->execute();
    $userData = $userStmt->get_result()->fetch_assoc();

    $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    $expiryTime = date("Y-m-d H:i:s", time() + 300);

    $update = $conn->prepare("UPDATE orders SET payment_otp = ?, payment_otp_expires_at = ? WHERE id = ?");
    $update->bind_param("ssi", $hashedOtp, $expiryTime, $order_id);

    if ($update->execute()) {
        if (sendPaymentOtpEmail(
            $userData["email"],
            trim($userData["first_name"] . " " . $userData["last_name"]),
            $otp,
            $order["total_amount"],
            $order["payment_method"]
        )) {
            $message = "New OTP sent successfully.";
        } else {
            $message = "Could not resend OTP.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Verify Payment OTP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fff7f8,#fff0f4);padding:20px;}
    .box{width:100%;max-width:500px;background:#fff;border-radius:28px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,0.08);}
    h1{font-size:30px;margin-bottom:10px;color:#222;}
    p{color:#6b7280;line-height:1.8;margin-bottom:18px;}
    input{width:100%;padding:14px 16px;border:1px solid #ecd7e0;border-radius:16px;margin-bottom:14px;outline:none;}
    button{width:100%;padding:14px;border:none;border-radius:16px;background:linear-gradient(135deg,#ff4f93,#ff2f7e);color:#fff;font-weight:700;cursor:pointer;margin-bottom:12px;}
    .secondary{background:#fff;color:#ff2f7e;border:1px solid #f2d3df;}
    .msg{margin-bottom:14px;font-weight:600;color:#e11d48;}
  </style>
</head>
<body>
  <div class="box">
    <h1>Verify Payment OTP</h1>
    <p>Enter the 6-digit OTP sent to your registered email to complete payment.</p>

    <?php if (!empty($message)) : ?>
      <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="otp" maxlength="6" placeholder="Enter payment OTP" required>
      <button type="submit" name="verify_payment_otp">Verify Payment</button>
    </form>

    <form method="POST">
      <button type="submit" name="resend_payment_otp" class="secondary">Resend OTP</button>
    </form>
  </div>
</body>
</html><?php
session_start();
require 'config/db.php';
require 'config/mail.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$order_id = isset($_GET["order_id"]) ? (int)$_GET["order_id"] : 0;
$message = "";

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["verify_payment_otp"])) {
    $otp = trim($_POST["otp"] ?? "");

    if (empty($otp)) {
        $message = "Please enter OTP.";
    } elseif (empty($order["payment_otp_expires_at"]) || strtotime($order["payment_otp_expires_at"]) < time()) {
        $message = "OTP expired. Please request a new one.";
    } elseif (!password_verify($otp, $order["payment_otp"])) {
        $message = "Invalid OTP.";
    } else {
        $update = $conn->prepare("UPDATE orders SET payment_status = 'paid', status = 'confirmed', payment_otp = NULL, payment_otp_expires_at = NULL WHERE id = ?");
        $update->bind_param("i", $order_id);

        if ($update->execute()) {
            $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clearCart->bind_param("i", $_SESSION["user_id"]);
            $clearCart->execute();

            $userStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
            $userStmt->bind_param("i", $_SESSION["user_id"]);
            $userStmt->execute();
            $userData = $userStmt->get_result()->fetch_assoc();

            sendOrderConfirmationEmail(
                $userData["email"],
                trim($userData["first_name"] . " " . $userData["last_name"]),
                $order["id"],
                $order["transaction_id"],
                $order["total_amount"],
                $order["payment_method"]
            );

            header("Location: payment-success.php?order_id=" . $order_id);
            exit();
        } else {
            $message = "Could not verify payment.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["resend_payment_otp"])) {
    $userStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $userStmt->bind_param("i", $_SESSION["user_id"]);
    $userStmt->execute();
    $userData = $userStmt->get_result()->fetch_assoc();

    $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    $expiryTime = date("Y-m-d H:i:s", time() + 300);

    $update = $conn->prepare("UPDATE orders SET payment_otp = ?, payment_otp_expires_at = ? WHERE id = ?");
    $update->bind_param("ssi", $hashedOtp, $expiryTime, $order_id);

    if ($update->execute()) {
        if (sendPaymentOtpEmail(
            $userData["email"],
            trim($userData["first_name"] . " " . $userData["last_name"]),
            $otp,
            $order["total_amount"],
            $order["payment_method"]
        )) {
            $message = "New OTP sent successfully.";
        } else {
            $message = "Could not resend OTP.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Verify Payment OTP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fff7f8,#fff0f4);padding:20px;}
    .box{width:100%;max-width:500px;background:#fff;border-radius:28px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,0.08);}
    h1{font-size:30px;margin-bottom:10px;color:#222;}
    p{color:#6b7280;line-height:1.8;margin-bottom:18px;}
    input{width:100%;padding:14px 16px;border:1px solid #ecd7e0;border-radius:16px;margin-bottom:14px;outline:none;}
    button{width:100%;padding:14px;border:none;border-radius:16px;background:linear-gradient(135deg,#ff4f93,#ff2f7e);color:#fff;font-weight:700;cursor:pointer;margin-bottom:12px;}
    .secondary{background:#fff;color:#ff2f7e;border:1px solid #f2d3df;}
    .msg{margin-bottom:14px;font-weight:600;color:#e11d48;}
  </style>
</head>
<body>
  <div class="box">
    <h1>Verify Payment OTP</h1>
    <p>Enter the 6-digit OTP sent to your registered email to complete payment.</p>

    <?php if (!empty($message)) : ?>
      <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="otp" maxlength="6" placeholder="Enter payment OTP" required>
      <button type="submit" name="verify_payment_otp">Verify Payment</button>
    </form>

    <form method="POST">
      <button type="submit" name="resend_payment_otp" class="secondary">Resend OTP</button>
    </form>
  </div>
</body>
</html>