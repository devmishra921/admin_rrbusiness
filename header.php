
<style>
 /* HEADER */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap');

body {
  font-family: 'Poppins', sans-serif;
}

/* HEADER */
.site-header {
  background: linear-gradient(to right, #ff512f, #dd2476);
  padding: 12px 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  flex-wrap: wrap;
  margin-left: 30px;
}

.logo-img {
  height: 65px;
  margin-right: 15px;
}

.site-title {
  margin: 0;
  font-size: 2rem;
  color: #fff;
  font-weight: 700;
}

.site-tagline {
  margin: 0;
  font-size: 0.9rem;
  color: #ffe4e1;
  font-weight: 500;
}

/* NAVIGATION */
.main-nav {
  background: linear-gradient(to right, #dd2476, #ff512f);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.nav-links {
  list-style: none;
  display: flex;
  gap: 32px;
  margin: 0;
  padding: 14px 0;
}

.nav-links a {
  color: #fff;
  font-size: 1.15rem;
  font-weight: 600;
  text-decoration: none;
  padding: 12px 16px;
  border-radius: 6px;
  transition: all 0.3s ease-in-out;
  position: relative;
}

.nav-links a:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(255,255,255,0.2);
}

/* No underline */
.nav-links a::after {
  display: none !important;
}

/* Mobile responsive menu */
@media(max-width:768px){
  .nav-toggle {
    display: flex;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 1100;
    padding: 12px;
  }

  .nav-toggle .line {
    width: 24px;
    height: 3px;
    background: #fff;
    transition: transform .3s,opacity .3s;
  }

  .nav-links {
    flex-direction: column;
    align-items: center;
    gap: 14px;
    background: linear-gradient(to bottom, #dd2476, #ff512f);
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    max-height: 0;
    overflow: hidden;
    transition: max-height .35s ease;
  }

  .main-nav.open .nav-links {
    max-height: 500px;
    padding: 16px 0;
  }

  .main-nav.open .line:nth-child(1) {
    transform: translateY(8px) rotate(45deg);
  }

  .main-nav.open .line:nth-child(2) {
    opacity: 0;
  }

  .main-nav.open .line:nth-child(3) {
    transform: translateY(-8px) rotate(-45deg);
  }
}

</style>
<!-- header.php -->
 
<header class="site-header">
  <div class="logo-container">
    <img src="images/Logo.png" alt="R.R. Business Logo" class="logo-img">
    <div>
      <h1 class="site-title">R.R. Business</h1>
      <h5 class="site-tagline">100% Shuddh Desi Masale</h5>
    </div>
  </div>
</header>

<!-- NAVIGATION -->
<nav class="main-nav" id="navbar">
  <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
    <span class="line"></span>
    <span class="line"></span>
    <span class="line"></span>
  </button>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="products.php">Products</a></li>
    <li><a href="about.php">About Us</a></li>
    <li><a href="contact.php">Contact Us</a></li>
    <li><a href="order.php">Order Now</a></li>
    <li><a href="gallery.php">Gallery</a></li>
    <li><a href="recipes.php">Recipes</a></li>
  </ul>
</nav>

<script>
  const navToggle = document.getElementById('navToggle');
  const navbar = document.getElementById('navbar');
  navToggle?.addEventListener('click', () => {
    navbar.classList.toggle('open');
  });
</script>

