<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Glowly | Home</title>
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

    html{
      scroll-behavior:smooth;
    }

    body{
      background:#fffaf7;
      color:#2d2d2d;
      overflow-x:hidden;
    }

    a{
      text-decoration:none;
      color:inherit;
    }

    ul{
      list-style:none;
    }

    .container{
      width:90%;
      max-width:1250px;
      margin:auto;
    }

    /* Header */
    header{
      position:sticky;
      top:0;
      z-index:1000;
      background:rgba(255,250,247,0.94);
      backdrop-filter:blur(12px);
      box-shadow:0 2px 18px rgba(0,0,0,0.05);
    }

    .navbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:18px 0;
      gap:20px;
    }

    .logo{
      font-size:30px;
      font-weight:800;
      color:#c96d78;
      letter-spacing:1px;
    }

    .logo span{
      color:#8c5e58;
    }

    .nav-links{
      display:flex;
      gap:28px;
      align-items:center;
    }

    .nav-links a{
      font-size:15px;
      font-weight:500;
      transition:0.3s;
      position:relative;
    }

    .nav-links a::after{
      content:"";
      position:absolute;
      left:0;
      bottom:-5px;
      width:0%;
      height:2px;
      background:#c96d78;
      transition:0.3s;
    }

    .nav-links a:hover::after{
      width:100%;
    }

    .nav-actions{
      display:flex;
      gap:12px;
      align-items:center;
    }

    .btn{
      border:none;
      outline:none;
      cursor:pointer;
      transition:0.3s;
      font-weight:600;
      border-radius:30px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }

    .btn:hover{
      transform:translateY(-3px);
    }

    .btn-outline{
      padding:11px 20px;
      background:#fff;
      color:#c96d78;
      border:1px solid #f0cfd4;
      box-shadow:0 8px 20px rgba(0,0,0,0.05);
    }

    .btn-primary{
      padding:12px 22px;
      background:linear-gradient(135deg,#d9858f,#b96b74);
      color:#fff;
      box-shadow:0 10px 25px rgba(201,109,120,0.28);
    }

    .menu-toggle{
      display:none;
      font-size:28px;
      cursor:pointer;
    }

    /* Hero */
    .hero{
      padding:70px 0 40px;
    }

    .hero-wrapper{
      display:grid;
      grid-template-columns:1.1fr 1fr;
      align-items:center;
      gap:40px;
    }

    .hero-text small{
      display:inline-block;
      padding:8px 16px;
      background:#ffe7ea;
      color:#c96d78;
      border-radius:50px;
      font-weight:600;
      margin-bottom:20px;
    }

    .hero-text h1{
      font-size:58px;
      line-height:1.1;
      color:#2e2625;
      margin-bottom:18px;
    }

    .hero-text h1 span{
      color:#c96d78;
    }

    .hero-text p{
      font-size:16px;
      color:#6f6663;
      line-height:1.8;
      margin-bottom:28px;
      max-width:580px;
    }

    .hero-buttons{
      display:flex;
      gap:16px;
      flex-wrap:wrap;
    }

    .hero-btn{
      padding:14px 28px;
      border-radius:35px;
      font-weight:600;
      font-size:15px;
      transition:0.3s;
    }

    .hero-btn.primary{
      background:linear-gradient(135deg,#d9858f,#b96b74);
      color:#fff;
      box-shadow:0 12px 25px rgba(201,109,120,0.25);
    }

    .hero-btn.secondary{
      background:#fff;
      color:#333;
      box-shadow:0 10px 20px rgba(0,0,0,0.08);
    }

    .hero-btn:hover{
      transform:translateY(-3px) scale(1.03);
    }

    .hero-image{
      position:relative;
    }

    .hero-card{
      background:linear-gradient(145deg,#ffe9eb,#fff);
      border-radius:35px;
      padding:30px;
      box-shadow:0 20px 45px rgba(0,0,0,0.08);
      position:relative;
      overflow:hidden;
      min-height:520px;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .hero-card img{
      width:100%;
      max-width:430px;
      object-fit:contain;
      animation:float 4s ease-in-out infinite;
      border-radius:24px;
    }

    .badge{
      position:absolute;
      background:#fff;
      padding:12px 16px;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.1);
      font-size:14px;
      font-weight:600;
    }

    .badge.one{
      top:28px;
      left:20px;
      color:#b96b74;
    }

    .badge.two{
      bottom:30px;
      right:24px;
      color:#5d8f6e;
    }

    @keyframes float{
      0%,100%{ transform:translateY(0); }
      50%{ transform:translateY(-12px); }
    }

    /* Highlights */
    .highlights{
      padding:25px 0 75px;
    }

    .highlight-grid{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:20px;
    }

    .highlight-card{
      background:#fff;
      padding:24px;
      border-radius:22px;
      box-shadow:0 10px 25px rgba(0,0,0,0.05);
      text-align:center;
      transition:0.3s;
    }

    .highlight-card:hover{
      transform:translateY(-8px);
    }

    .highlight-card h3{
      margin-top:12px;
      margin-bottom:8px;
      color:#463735;
      font-size:18px;
    }

    .highlight-card p{
      color:#766b68;
      font-size:14px;
      line-height:1.7;
    }

    .emoji{
      font-size:34px;
    }

    /* Common */
    .section{
      padding:80px 0;
    }

    .section-title{
      text-align:center;
      margin-bottom:50px;
    }

    .section-title span{
      color:#c96d78;
      font-weight:600;
      display:inline-block;
      margin-bottom:10px;
    }

    .section-title h2{
      font-size:40px;
      color:#2e2625;
      margin-bottom:12px;
    }

    .section-title p{
      max-width:700px;
      margin:auto;
      color:#756c69;
      line-height:1.8;
      font-size:15px;
    }

    /* Categories */
    .categories{
      background:linear-gradient(to bottom,#fffaf7,#fff1f2);
    }

    .category-grid{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:24px;
    }

    .category-card{
      position:relative;
      background:#fff;
      border-radius:28px;
      padding:32px 24px;
      box-shadow:0 15px 28px rgba(0,0,0,0.06);
      overflow:hidden;
      transition:0.35s;
      min-height:220px;
    }

    .category-card::before{
      content:"";
      position:absolute;
      top:-50px;
      right:-50px;
      width:130px;
      height:130px;
      background:linear-gradient(135deg,#ffe2e7,#ffd0d8);
      border-radius:50%;
      transition:0.4s;
    }

    .category-card::after{
      content:"";
      position:absolute;
      bottom:-35px;
      left:-35px;
      width:100px;
      height:100px;
      background:#fff3f5;
      border-radius:50%;
    }

    .category-card:hover{
      transform:translateY(-10px);
      box-shadow:0 20px 35px rgba(0,0,0,0.08);
    }

    .category-card:hover::before{
      transform:scale(1.15);
    }

    .category-content{
      position:relative;
      z-index:2;
    }

    .category-number{
      font-size:42px;
      font-weight:800;
      color:#f3c2ca;
      line-height:1;
      margin-bottom:16px;
    }

    .category-content h3{
      font-size:22px;
      color:#3c2f2d;
      margin-bottom:10px;
    }

    .category-content p{
      color:#766d69;
      font-size:14px;
      line-height:1.8;
      margin-bottom:16px;
    }

    .brand-tags{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
    }

    .brand-tags span{
      font-size:12px;
      padding:7px 12px;
      border-radius:20px;
      background:#fff1f3;
      color:#b56571;
      font-weight:600;
    }

    /* Products */
    .product-grid{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:24px;
    }

    .product-card{
      background:#fff;
      border-radius:26px;
      box-shadow:0 15px 30px rgba(0,0,0,0.05);
      overflow:hidden;
      transition:0.3s;
      position:relative;
    }

    .product-card:hover{
      transform:translateY(-10px);
    }

    .product-badge{
      position:absolute;
      top:16px;
      left:16px;
      background:#c96d78;
      color:white;
      font-size:12px;
      font-weight:600;
      padding:6px 12px;
      border-radius:30px;
      z-index:1;
    }

    .product-card img{
      width:100%;
      height:280px;
      object-fit:cover;
    }

    .product-content{
      padding:20px;
    }

    .product-content h3{
      font-size:19px;
      color:#382d2b;
      margin-bottom:8px;
    }

    .product-content p{
      font-size:14px;
      color:#6f6764;
      line-height:1.7;
      margin-bottom:12px;
    }

    .price-row{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-top:14px;
      gap:10px;
    }

    .price{
      font-size:20px;
      font-weight:700;
      color:#c96d78;
    }

    .product-btn{
      background:linear-gradient(135deg,#d9858f,#b96b74);
      color:#fff;
      padding:10px 18px;
      border:none;
      border-radius:30px;
      font-weight:600;
      cursor:pointer;
      transition:0.3s;
    }

    .product-btn:hover{
      transform:translateY(-3px) scale(1.03);
    }

    /* Brands strip */
    .brands-strip{
      padding:10px 0 80px;
    }

    .brands-box{
      background:#fff;
      border-radius:28px;
      box-shadow:0 12px 28px rgba(0,0,0,0.05);
      padding:28px;
      display:flex;
      flex-wrap:wrap;
      justify-content:center;
      gap:16px;
    }

    .brands-box span{
      padding:12px 18px;
      background:#fff3f5;
      border-radius:24px;
      color:#a95d69;
      font-weight:600;
      transition:0.3s;
    }

    .brands-box span:hover{
      transform:translateY(-4px);
      background:#ffdfe5;
    }

    /* Offer */
    .offer{
      padding:20px 0 80px;
    }

    .offer-box{
      background:linear-gradient(120deg,#c96d78,#f1b7bb);
      border-radius:32px;
      padding:50px;
      color:white;
      display:grid;
      grid-template-columns:1.2fr 1fr;
      align-items:center;
      gap:30px;
      box-shadow:0 20px 40px rgba(201,109,120,0.25);
    }

    .offer-box h2{
      font-size:42px;
      line-height:1.2;
      margin-bottom:16px;
    }

    .offer-box p{
      line-height:1.8;
      margin-bottom:22px;
      color:#fff7f8;
    }

    .offer-btn{
      background:white;
      color:#b25b68;
      padding:13px 24px;
      border-radius:30px;
      font-weight:700;
      display:inline-block;
      transition:0.3s;
    }

    .offer-btn:hover{
      transform:translateY(-3px);
    }

    .offer-box img{
      width:100%;
      max-height:340px;
      object-fit:cover;
      border-radius:24px;
    }

    /* Newsletter */
    .newsletter-box{
      background:white;
      border-radius:32px;
      padding:50px;
      text-align:center;
      box-shadow:0 15px 30px rgba(0,0,0,0.05);
    }

    .newsletter-box h2{
      font-size:38px;
      margin-bottom:14px;
      color:#2e2625;
    }

    .newsletter-box p{
      max-width:650px;
      margin:auto;
      color:#716865;
      line-height:1.8;
      margin-bottom:24px;
    }

    .newsletter-form{
      display:flex;
      justify-content:center;
      gap:14px;
      flex-wrap:wrap;
    }

    .newsletter-form input{
      width:360px;
      max-width:100%;
      padding:15px 20px;
      border-radius:35px;
      border:1px solid #e8d9d8;
      outline:none;
      font-size:15px;
    }

    .newsletter-form button{
      padding:15px 28px;
      border-radius:35px;
      border:none;
      background:linear-gradient(135deg,#d9858f,#b96b74);
      color:white;
      font-weight:700;
      cursor:pointer;
    }

    /* Footer */
    footer{
      background:#2b2322;
      color:#f3e9e8;
      padding:70px 0 25px;
      margin-top:80px;
    }

    .footer-grid{
      display:grid;
      grid-template-columns:1.5fr 1fr 1fr 1fr;
      gap:30px;
      margin-bottom:35px;
    }

    .footer-col h3,
    .footer-col h4{
      margin-bottom:18px;
    }

    .footer-col p,
    .footer-col li{
      color:#cbbfbd;
      font-size:14px;
      line-height:2;
    }

    .footer-col ul li a:hover{
      color:white;
    }

    .copyright{
      border-top:1px solid rgba(255,255,255,0.12);
      text-align:center;
      padding-top:20px;
      color:#bfaead;
      font-size:14px;
    }

    /* Reveal */
    .reveal{
      opacity:0;
      transform:translateY(40px);
      transition:all 0.8s ease;
    }

    .reveal.active{
      opacity:1;
      transform:translateY(0);
    }

    /* Responsive */
    @media (max-width:1100px){
      .hero-wrapper,
      .highlight-grid,
      .category-grid,
      .product-grid,
      .offer-box,
      .footer-grid{
        grid-template-columns:repeat(2,1fr);
      }

      .hero-text h1{
        font-size:46px;
      }
    }

    @media (max-width:768px){
      .menu-toggle{
        display:block;
      }

      .nav-links{
        position:absolute;
        top:78px;
        left:0;
        width:100%;
        background:#fffaf7;
        flex-direction:column;
        padding:20px;
        gap:18px;
        display:none;
        box-shadow:0 15px 30px rgba(0,0,0,0.08);
      }

      .nav-links.active{
        display:flex;
      }

      .nav-actions{
        display:none;
      }

      .hero-wrapper,
      .highlight-grid,
      .category-grid,
      .product-grid,
      .offer-box,
      .footer-grid{
        grid-template-columns:1fr;
      }

      .hero{
        padding-top:40px;
      }

      .hero-text h1{
        font-size:38px;
      }

      .section-title h2,
      .offer-box h2,
      .newsletter-box h2{
        font-size:30px;
      }

      .offer-box,
      .newsletter-box{
        padding:30px 20px;
      }

      .hero-card{
        min-height:auto;
      }
    }
  </style>
</head>
<body>

  <header>
    <div class="container navbar">
      <div class="logo">Glow<span>ly</span></div>

      <ul class="nav-links" id="navLinks">
        <li><a href="#">Home</a></li>
        <li><a href="#categories">Categories</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>

      <div class="nav-actions">
        <a href="signin.php" class="btn btn-outline">Sign In</a>
        <a href="signup.php" class="btn btn-primary">Sign Up</a>
      </div>

      <div class="menu-toggle" id="menuToggle">☰</div>
    </div>
  </header>

  <section class="hero">
    <div class="container hero-wrapper">
      <div class="hero-text reveal">
        <small>Glow Better. Shop Smarter.</small>
        <h1>Your Daily <span>Skincare Store</span> For Every Glow Need</h1>
        <p>
          Explore premium skincare essentials from top brands in one beautiful place.
          From facewash to moisturizers and serums, Glowly helps users discover trusted
          skincare products for every routine and every skin type.
        </p>
        <div class="hero-buttons">
          <a href="#categories" class="hero-btn primary">Browse Categories</a>
          <a href="#products" class="hero-btn secondary">Shop Products</a>
        </div>
      </div>

      <div class="hero-image reveal">
        <div class="hero-card">
          <span class="badge one">✨ Trending Skincare Picks</span>
          <img src="assets/image/homepage.avif" alt="Skincare Hero">
          <span class="badge two">🌿 Trusted Brands & Products</span>
        </div>
      </div>
    </div>
  </section>

  <section class="highlights">
    <div class="container highlight-grid">
      <div class="highlight-card reveal">
        <div class="emoji">🧴</div>
        <h3>Premium Products</h3>
        <p>Explore skincare products selected from popular and trusted beauty brands.</p>
      </div>
      <div class="highlight-card reveal">
        <div class="emoji">🌸</div>
        <h3>Top Categories</h3>
        <p>Quickly discover facewash, serum, moisturizer, sunscreen, and more.</p>
      </div>
      <div class="highlight-card reveal">
        <div class="emoji">✨</div>
        <h3>Modern Experience</h3>
        <p>Clean design, smooth animations, and an attractive shopping experience.</p>
      </div>
      <div class="highlight-card reveal">
        <div class="emoji">🚚</div>
        <h3>Easy Shopping</h3>
        <p>Browse collections easily and move toward your skincare routine faster.</p>
      </div>
    </div>
  </section>

  <section class="section categories" id="categories">
    <div class="container">
      <div class="section-title reveal">
        <span>Our Categories</span>
        <h2>Skincare Categories We Offer</h2>
        <p>
          We showcase all major skincare categories so users can easily move to the products page
          and explore items from top brands like Mamaearth, Minimalist, Dot & Key, and Pilgrim.
        </p>
      </div>

      <div class="category-grid">
        <div class="category-card reveal">
          <div class="category-content">
            <div class="category-number">01</div>
            <h3>Facewash</h3>
            <p>
              Gentle and refreshing facewash collections for daily cleansing and oil control.
            </p>
            <div class="brand-tags">
              <span>Mamaearth</span>
              <span>Minimalist</span>
              <span>Dot & Key</span>
              <span>Pilgrim</span>
            </div>
          </div>
        </div>

        <div class="category-card reveal">
          <div class="category-content">
            <div class="category-number">02</div>
            <h3>Moisturizer</h3>
            <p>
              Lightweight to rich moisturizers for hydration, repair, and everyday skin comfort.
            </p>
            <div class="brand-tags">
              <span>Mamaearth</span>
              <span>Minimalist</span>
              <span>Dot & Key</span>
              <span>Pilgrim</span>
            </div>
          </div>
        </div>

        <div class="category-card reveal">
          <div class="category-content">
            <div class="category-number">03</div>
            <h3>Serum</h3>
            <p>
              Targeted serums for glow, acne marks, hydration, brightening, and smoother texture.
            </p>
            <div class="brand-tags">
              <span>Mamaearth</span>
              <span>Minimalist</span>
              <span>Dot & Key</span>
              <span>Pilgrim</span>
            </div>
          </div>
        </div>

        <div class="category-card reveal">
          <div class="category-content">
            <div class="category-number">04</div>
            <h3>Sunscreen</h3>
            <p>
              Daily SPF essentials that help protect skin from sun damage and tanning.
            </p>
            <div class="brand-tags">
              <span>Mamaearth</span>
              <span>Minimalist</span>
              <span>Dot & Key</span>
              <span>Pilgrim</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <section class="offer">
    <div class="container">
      <div class="offer-box reveal">
        <div>
          <h2>Get 25% Off On Your First Skincare Order</h2>
          <p>
            Welcome new users with an attractive homepage offer section. You can later connect
            this with your real product pages, offers database, and cart system.
          </p>
          <a href="#" class="offer-btn">Claim Offer</a>
        </div>
        <div>
          <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80" alt="Offer Banner">
        </div>
      </div>
    </div>
  </section>


  <footer id="contact">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h3>Glowly</h3>
          <p>
            A modern skincare shopping website concept built to showcase categories,
            brands, and premium product collections in one attractive place.
          </p>
        </div>

        <div class="footer-col">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#categories">Categories</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Account</h4>
          <ul>
            <li><a href="signin.php">Sign In</a></li>
            <li><a href="signup.php">Sign Up</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Contact</h4>
          <ul>
            <li>Email: support@glowly.com</li>
            <li>Phone: +91 98765 43210</li>
            <li>Pune, Maharashtra, India</li>
          </ul>
        </div>
      </div>

      <div class="copyright">
        © 2026 Glowly. All Rights Reserved.
      </div>
    </div>
  </footer>

  <script>
    const menuToggle = document.getElementById("menuToggle");
    const navLinks = document.getElementById("navLinks");

    menuToggle.addEventListener("click", () => {
      navLinks.classList.toggle("active");
    });

    const reveals = document.querySelectorAll(".reveal");

    function revealOnScroll() {
      reveals.forEach((element) => {
        const windowHeight = window.innerHeight;
        const revealTop = element.getBoundingClientRect().top;
        const revealPoint = 100;

        if (revealTop < windowHeight - revealPoint) {
          element.classList.add("active");
        }
      });
    }

    window.addEventListener("scroll", revealOnScroll);
    window.addEventListener("load", revealOnScroll);

    const newsletterForm = document.querySelector(".newsletter-form");
    newsletterForm.addEventListener("submit", function(e){
      e.preventDefault();
      alert("Thank you for subscribing to Glowly!");
      newsletterForm.reset();
    });
  </script>

</body>
</html>