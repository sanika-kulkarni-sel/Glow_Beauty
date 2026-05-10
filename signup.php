<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>


<?php
ob_start();
session_start();
require 'config/db.php';
require 'config/mail.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $message = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ((int)$user["is_verified"] === 1) {
                $message = "Email already registered. Please sign in.";
            } else {
                $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
                $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
                $expiry = date("Y-m-d H:i:s", time() + 600);

                $update = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, verification_code = ?, verification_expires_at = ? WHERE email = ?");
                $update->bind_param("ssssss", $first_name, $last_name, $phone, $hashedOtp, $expiry, $email);

                if ($update->execute()) {
                    $fullName = trim($first_name . " " . $last_name);
                    if (sendVerificationEmail($email, $fullName, $otp)) {
                        header("Location: verify-email.php?email=" . urlencode($email));
                        exit();
                    } else {
                        $message = "Could not resend verification email.";
                    }
                } else {
                    $message = "Could not update verification details.";
                }
            }
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
            $expiry = date("Y-m-d H:i:s", time() + 600);

            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, is_verified, verification_code, verification_expires_at) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
            $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $hashedPassword, $hashedOtp, $expiry);

            if ($stmt->execute()) {
                $fullName = trim($first_name . " " . $last_name);
                if (sendVerificationEmail($email, $fullName, $otp)) {
                    header("Location: verify-email.php?email=" . urlencode($email));
                    exit();
                } else {
                    $message = "Account created, but verification email failed.";
                }
            } else {
                $message = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glowly | Sign Up</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family:'Poppins',sans-serif;
    }

    body{
      min-height:100vh;
      background:linear-gradient(135deg,#fff7f8,#fff0f4);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:30px 15px;
      color:#2b2b2b;
    }

    a{
      text-decoration:none;
      color:inherit;
    }

    .page-wrap{
      width:100%;
      max-width:1150px;
      display:grid;
      grid-template-columns:1fr 1fr;
      background:#fff;
      border-radius:32px;
      overflow:hidden;
      box-shadow:0 20px 60px rgba(0,0,0,0.08);
    }

    .left-panel{
      background:linear-gradient(135deg,#ff4f93,#ff7aae);
      color:#fff;
      padding:60px 50px;
      position:relative;
      overflow:hidden;
      display:flex;
      flex-direction:column;
      justify-content:center;
    }

    .left-panel::before{
      content:"";
      position:absolute;
      width:260px;
      height:260px;
      border-radius:50%;
      background:rgba(255,255,255,0.12);
      top:-70px;
      right:-70px;
    }

    .left-panel::after{
      content:"";
      position:absolute;
      width:220px;
      height:220px;
      border-radius:50%;
      background:rgba(255,255,255,0.08);
      bottom:-80px;
      left:-80px;
    }

    .brand{
      font-size:36px;
      font-weight:800;
      letter-spacing:1px;
      margin-bottom:18px;
      position:relative;
      z-index:1;
    }

    .brand span{
      color:#ffe2ef;
    }

    .left-panel h1{
      font-size:42px;
      line-height:1.2;
      margin-bottom:16px;
      position:relative;
      z-index:1;
    }

    .left-panel p{
      font-size:15px;
      line-height:1.9;
      color:#fff5f8;
      max-width:440px;
      position:relative;
      z-index:1;
      margin-bottom:30px;
    }

    .benefits{
      position:relative;
      z-index:1;
      display:grid;
      gap:14px;
    }

    .benefit{
      background:rgba(255,255,255,0.14);
      border:1px solid rgba(255,255,255,0.18);
      padding:14px 16px;
      border-radius:18px;
      font-size:14px;
      backdrop-filter:blur(8px);
    }

    .right-panel{
      padding:50px 42px;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#fff;
    }

    .form-box{
      width:100%;
      max-width:430px;
    }

    .form-box h2{
      font-size:34px;
      color:#2b2b2b;
      margin-bottom:10px;
    }

    .form-box .subtext{
      color:#6b7280;
      font-size:14px;
      line-height:1.7;
      margin-bottom:28px;
    }

    .input-group{
      margin-bottom:18px;
    }

    .input-group label{
      display:block;
      font-size:14px;
      font-weight:600;
      color:#374151;
      margin-bottom:8px;
    }

    .input-group input{
      width:100%;
      padding:15px 16px;
      border-radius:16px;
      border:1px solid #ecd7e0;
      outline:none;
      font-size:14px;
      transition:0.25s;
      background:#fffafd;
    }

    .input-group input:focus{
      border-color:#ff4f93;
      box-shadow:0 0 0 4px rgba(255,79,147,0.10);
    }

    .row{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:14px;
    }

    .checkbox-wrap{
      display:flex;
      align-items:flex-start;
      gap:10px;
      margin:12px 0 22px;
      color:#6b7280;
      font-size:13px;
      line-height:1.6;
    }

    .checkbox-wrap input{
      margin-top:3px;
      accent-color:#ff4f93;
    }

    .submit-btn{
      width:100%;
      border:none;
      cursor:pointer;
      padding:15px 18px;
      border-radius:18px;
      background:linear-gradient(135deg,#ff4f93,#ff2f7e);
      color:#fff;
      font-size:15px;
      font-weight:700;
      box-shadow:0 14px 28px rgba(255,79,147,0.24);
      transition:0.3s;
    }

    .submit-btn:hover{
      transform:translateY(-3px);
    }

    .bottom-text{
      text-align:center;
      margin-top:20px;
      color:#6b7280;
      font-size:14px;
    }

    .bottom-text a{
      color:#ff2f7e;
      font-weight:600;
    }

    .home-link{
      display:inline-block;
      margin-top:18px;
      color:#ff2f7e;
      font-size:14px;
      font-weight:600;
    }

    @media (max-width:950px){
      .page-wrap{
        grid-template-columns:1fr;
      }

      .left-panel{
        padding:42px 30px;
      }

      .right-panel{
        padding:40px 22px;
      }
    }

    @media (max-width:600px){
      .row{
        grid-template-columns:1fr;
      }

      .left-panel h1{
        font-size:32px;
      }

      .form-box h2{
        font-size:28px;
      }
    }
  </style>
</head>
<body>

  <div class="page-wrap">
    <div class="left-panel">
      <div class="brand">Glow<span>ly</span></div>
      <h1>Create Your Beauty Account</h1>
      <p>
        Join Glowly and explore skincare products from trusted brands like Mamaearth,
        Minimalist, Dot & Key, and Pilgrim in one beautiful shopping experience.
      </p>

      <div class="benefits">
        <div class="benefit">✨ Easy access to premium skincare products</div>
        <div class="benefit">🛍️ Save favorites and build your cart faster</div>
        <div class="benefit">💖 Smooth shopping dashboard for your account</div>
      </div>
    </div>

    <div class="right-panel">
      <div class="form-box">
        <h2>Sign Up</h2>
        <p class="subtext">Fill in your details to create your Glowly account.</p>

        <?php if (!empty($message)) : ?>
    <p style="color:#e11d48; font-weight:600; margin-bottom:15px;">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?> 


        <form action="#" method="POST">
          <div class="row">
            <div class="input-group">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required>
            </div>

            <div class="input-group">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required>
            </div>
          </div>

          <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
          </div>

          <div class="input-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
          </div>

          <div class="row">
            <div class="input-group">
              <label for="password">Password</label>
              <input type="password" id="password" name="password" placeholder="Create password" required>
            </div>

            <div class="input-group">
              <label for="confirm_password">Confirm Password</label>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
            </div>
          </div>

          <div class="checkbox-wrap">
            <input type="checkbox" id="terms" required>
            <label for="terms">
              I agree to the terms and conditions and allow Glowly to create my account.
            </label>
          </div>

          <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="bottom-text">
          Already have an account? <a href="signin.php">Sign In</a>
        </div>

        <a href="index.php" class="home-link">← Back to Home</a>
      </div>
    </div>
  </div>

</body>
</html>