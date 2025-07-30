<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>RR Business | Generate Barcode</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- JsBarcode -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<style>
/* -------- DASHBOARD BASE STYLE (copied from dashboard.php) -------- */
:root{--brand:#a83232;--accent:#ffcb6b;--bg:#f5f7fa;--card-bg:rgba(255,255,255,0.85);--glass:rgba(255,255,255,0.6);--shadow:0 10px 30px rgba(0,0,0,.08);--radius:18px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:#333;line-height:1.4}
header{position:sticky;top:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:linear-gradient(120deg,var(--brand),#ff5d5d);color:#fff;box-shadow:var(--shadow)}
header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
header h1{font-size:1.5rem;font-weight:600;letter-spacing:1px;text-shadow:0 1px 2px rgba(0,0,0,.3)}
header #clock{font-family:'Poppins',sans-serif;font-weight:500}
nav{background:#fff;box-shadow:var(--shadow)}
nav ul{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;list-style:none;padding:10px 12px;max-width:1200px;margin:auto}
nav a{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-weight:500;text-decoration:none;color:#333;transition:.3s}
nav a:hover,nav a.active{background:var(--brand);color:#fff}
/* -------- PAGE SPECIFIC -------- */
.container {
  width: calc(100% - 80px); /* Full width with 40px margin on both sides */
  margin: 48px auto;
  padding: 40px;
  background: var(--card-bg);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  animation: fadeIn .6s ease;
}
.form-card{background:#fff;border-radius:var(--radius);padding:28px;box-shadow:var(--shadow)}
.form-card label{display:block;margin-top:14px;font-weight:600}
.form-card input, .form-card button{width:100%;padding:10px 12px;margin-top:6px;border:1px solid #ccc;border-radius:10px;font-family:inherit}
.form-card button{background:var(--brand);color:#fff;font-weight:600;border:none;cursor:pointer;transition:.3s}
.form-card button:hover{background:#d42020}
#status{margin-top:14px;font-weight:600}
#preview{margin-top:24px;text-align:center;display:none}
#preview svg{border:1px solid #ccc;padding:8px;border-radius:10px;background:#fff}
#actions{margin-top:14px;display:flex;gap:12px;justify-content:center}
#actions a,#actions button{flex:1;padding:10px;border:none;border-radius:8px;font-weight:600;cursor:pointer;color:#fff}
#actions a{background:#007bff;text-decoration:none}
#actions button{background:#28a745}
footer{background:rgb(143,51,51);color:#ccc;padding:30px 20px;font-size:.95rem;margin-top:80px}
.footer-container{display:flex;flex-wrap:wrap;justify-content:space-between;max-width:1200px;margin:auto;gap:60px}
.footer-col{flex:1 1 50px;min-width:240px}
.footer-col h4{color:var(--accent);margin-bottom:16px;font-size:1.1rem}
.footer-col ul{list-style:none;padding:0}
.footer-col li{margin-bottom:10px;display:flex;align-items:center;gap:6px}
.footer-col a{color:#ccc;text-decoration:none;transition:.3s}
.footer-col a:hover{color:#fff;text-shadow:0 0 4px #fff}
.footer-bottom{text-align:center;margin-top:40px;font-size:.9rem;color:#aaa}
</style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
  <img src="images/Logo.png" class="logo" alt="R.R. Business Logo">
  <h1>R.R. Business – Barcode Generator</h1>
  <span id="clock"></span>
</header>

<!-- ===== NAV ===== -->
<nav>
  <ul>
    <li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
    <li><a href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li>
    <li><a href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li>
    <li><a href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li>
    <li><a href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li>
    <li><a class="active" href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li>
    <li><a href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li>
    <li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li>
    <li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</nav>
<!-- ===== MAIN ===== -->
<main class="container">
  <div class="form-card">
    <h2 style="text-align:center;font-size:1.4rem;font-weight:700;margin-bottom:10px">Generate Product Barcode</h2>

    <form id="prodForm">
      <label>Product Name</label><input name="product_name" required>
      <label>HSN Code</label><input name="hsn" value="09103030" required>
      <label>Net Weight</label><input name="net_weight" required>
      <label>MRP (₹)</label><input type="number" step="0.01" name="mrp" required>
      <label>Manufacturing Date</label><input type="date" name="mfg_date" required>
      <label>Expiry Date</label><input type="date" name="expiry_date" required>
      <button type="submit">Generate Barcode</button>
    </form>

    <div id="status"></div>

    <div id="preview">
      <svg id="barcodeSVG"></svg>
      <div id="actions">
        <a id="downloadLink" href="#" download="barcode.png">Download</a>
        <button id="printBtn">Print</button>
      </div>
    </div>
  </div>
</main>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="footer-container">
    <div class="footer-col"><h4>📞 Contact Us</h4><ul>
      <li><i class="fa fa-phone"></i> +91 76788 53017</li>
      <li><i class="fa fa-envelope"></i> support@rrbusiness.com</li>
      <li><i class="fa fa-envelope-open"></i> care@rrbusiness.com</li></ul></div>
    <div class="footer-col"><h4>🔗 Quick Links</h4><ul>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="admin_pannel.php">Manage Products</a></li>
      <li><a href="view_order.php">Orders</a></li>
      <li><a href="customer_queries.php">Queries</a></li></ul></div>
    <div class="footer-col"><h4>📱 Follow Us</h4><ul>
      <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
      <li><a href="https://www.instagram.com/rrbusiness2025" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
      <li><a href="https://wa.me/917678853017"><i class="fab fa-whatsapp"></i> WhatsApp</a></li></ul></div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <?=date('Y')?> <strong>R.R. Business</strong> — All Rights Reserved</p>
    <p>🚀 Developed by <strong>V.G Technologies Pvt. Ltd.</strong></p>
  </div>
</footer>

<!-- ===== SCRIPTS ===== -->
<script>
/* live clock */
function updateClock(){document.getElementById('clock').textContent=new Date().toLocaleString();}
updateClock(); setInterval(updateClock,1000);

/* BARCODE logic (DB‑free) */
const form=document.getElementById('prodForm'),statusDiv=document.getElementById('status'),
preview=document.getElementById('preview'),svg=document.getElementById('barcodeSVG'),
download=document.getElementById('downloadLink'),printBtn=document.getElementById('printBtn');

function showStatus(msg,color){statusDiv.textContent=msg;statusDiv.style.color=color;}

form.addEventListener('submit',async e=>{
 e.preventDefault(); showStatus('Generating…','blue');
 const res = await fetch('save_barcode.php',{method:'POST',body:new FormData(form)});
 let out; try{ out=await res.json(); }catch(err){
   showStatus('Server sent invalid JSON!','red'); console.log(await res.text()); return;
 }
 if(!out.success){showStatus('Error: '+out.message,'red');return;}

 JsBarcode('#barcodeSVG',String(out.barcode_string),{format:'CODE128',width:2,height:100,displayValue:true});

 /* PNG download prep */
 const svgData=new XMLSerializer().serializeToString(svg);
 const pngURL=await new Promise(r=>{
   const c=document.createElement('canvas'),ctx=c.getContext('2d'),img=new Image();
   img.onload=()=>{c.width=img.width;c.height=img.height;ctx.drawImage(img,0,0);r(c.toDataURL('image/png'));};
   img.src='data:image/svg+xml;base64,'+btoa(svgData);
 });
 download.href=pngURL;

 preview.style.display='block';
 showStatus('✅ Barcode generated!','green');
});
printBtn.addEventListener('click',()=>{const w=window.open('','_blank');w.document.write(`<svg>${svg.innerHTML}</svg>`);w.document.close();w.print();});
</script>
</body>
</html>
