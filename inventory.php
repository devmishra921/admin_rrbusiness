<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.html"); exit; }
require 'db_connect.php';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Inventory | R.R. Business</title>

<!-- ===== CSS & LIBS ===== -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{--brand:#a83232;--accent:#ffcb6b;--bg:#f5f7fa;--card-bg:rgba(255,255,255,.9);--shadow:0 10px 30px rgba(0,0,0,.08);--radius:18px;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:#333;overflow-x:hidden}
/* ===== HEADER ===== */
header{position:sticky;top:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:linear-gradient(120deg,var(--brand),#ff5d5d);color:#fff;box-shadow:var(--shadow)}
header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
header h1{font-size:1.5rem;font-weight:600;letter-spacing:1px;text-shadow:0 1px 2px rgba(0,0,0,.3)}
header #clock{font-family:'Poppins',sans-serif;font-weight:500}
nav{background:#fff;box-shadow:var(--shadow)}
nav ul{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;list-style:none;padding:10px 12px}
nav a{padding:8px 14px;border-radius:10px;text-decoration:none;font-weight:500;color:#333}
nav a.active,nav a:hover{background:var(--brand);color:#fff}

/* --- नया wrapper --- */
.page-wrapper{padding:0 25px;}          /* दोनों तरफ 25px space */

/* कार्ड‑लुक */
#invTable thead{background:var(--brand);color:#fff}
.btn-brand{background:var(--brand);color:#fff}.btn-brand:hover{background:#842020}

html, body {
  height: 100%;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
}
.page-wrapper {
  flex: 1;
  padding: 0 25px;
}
.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
}
/* ==== FOOTER ==== */
footer {
  background: rgb(143,51,51);
  color: #ccc;
  padding: 30px 20px;
  font-size: .95rem;
}

.footer-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  max-width: 1200px;
  margin: auto;
  gap: 60px;
}

.footer-col {
  flex: 1 1 50px;
  min-width: 240px;
}

.footer-col h4 {
  color: var(--accent);
  margin-bottom: 16px;
  font-size: 1.1rem;
}

.footer-col ul {
  list-style: none;
  padding: 0;
}

.footer-col li {
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.footer-col a {
  color: #ccc;
  text-decoration: none;
  transition: .3s;
}

.footer-col a:hover {
  color: #fff;
  text-shadow: 0 0 4px #fff;
}

.footer-bottom {
  text-align: center;
  margin-top: 40px;
  font-size: .9rem;
  color: #aaa;
}
</style>
</head>
<body>
<div class="main-wrapper">
<header>
  <img src="images/Logo.png" alt="R.R. Business Logo" class="logo" style="height:60px">
  <h1>R.R. Business Inventory</h1>
  <span id="clock"></span>
</header>

<nav>
  <ul>
    <li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
    <li><a class="active" href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li>
    <li><a href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li>
    <li><a href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li>
    <li><a href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li>
    <li><a href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li>
    <li><a href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li>
    <li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li>
    <li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</nav>

<!-- ==== MAIN CONTENT ==== -->
<div class="page-wrapper"><!-- 25px padding wrapper शुरू -->

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="m-0">Inventory</h2>
    <div class="d-flex gap-2">
      <button id="downloadBtn" class="btn btn-outline-dark">
        <i class="bi bi-download me-1"></i> Download CSV
      </button>
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle me-1"></i> Add Item
      </button>
    </div>
  </div>

  <!-- ===== DATA TABLE ===== -->
  <table id="invTable" class="table table-bordered table-hover w-100">
    <thead>
      <tr>
        <th>ID</th><th>Product ID</th><th>Name</th><th>Code</th><th>Qty</th>
        <th>Unit Price (₹)</th><th>Purchase (₹)</th><th>GST %</th><th>HSN</th>
        <th>Unit</th><th>Net Wt.</th><th>Re‑order</th><th>Status</th>
        <th>Total (₹)</th><th>Updated</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
      $sql = "SELECT i.*, p.name AS product_name
              FROM inventory i
              LEFT JOIN products p ON p.id = i.product_id
              ORDER BY i.id DESC";
      $res = $conn->query($sql);
      while ($row = $res->fetch_assoc()):
    ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['product_id'] ?></td>
        <td><?= htmlspecialchars($row['product_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($row['product_code']) ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= number_format($row['unit_price'],2) ?></td>
        <td><?= number_format($row['purchase_price'],2) ?></td>
        <td><?= $row['gst_percent'] ?></td>
        <td><?= htmlspecialchars($row['hsn_code']) ?></td>
        <td><?= $row['unit'] ?></td>
        <td><?= $row['net_weight'] ?></td>
        <td><?= $row['reorder_level'] ?></td>
        <td><?= $row['status'] ?></td>
        <td><?= number_format($row['total_value'],2) ?></td>
        <td><?= $row['last_updated'] ?></td>
        <td>
          <button class="btn btn-sm btn-brand editBtn" data-id="<?= $row['id'] ?>">
            <i class="bi bi-pencil-square"></i>
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

</div><!-- page-wrapper End -->

<!-- ===== MODAL (Add/Edit) ===== -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:calc(100% - 50px); margin:25px;">
    <form class="modal-content" id="invForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="inv_id">

<div class="col-md-3">
  <label class="form-label">Product ID*</label>
  <input type="number" name="product_id" id="product_id" class="form-control" required>
</div>

<div class="col-md-3">
  <label class="form-label">Product Code*</label>
  <input type="text" name="product_code" id="product_code" class="form-control" required>
</div>

<div class="col-md-3">
  <label class="form-label">Quantity*</label>
  <input type="number" step="0.001" name="quantity" id="quantity" class="form-control" required>
</div>

<div class="col-md-3">
  <label class="form-label">Unit Price (₹)</label>
  <input type="number" step="0.01" name="unit_price" id="unit_price" class="form-control">
</div>

<div class="col-md-3">
  <label class="form-label">Purchase Price (₹)</label>
  <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control">
</div>

<div class="col-md-3">
  <label class="form-label">GST %</label>
  <input type="number" step="0.01" name="gst_percent" id="gst_percent" class="form-control" value="5.00">
</div>

<div class="col-md-3">
  <label class="form-label">HSN Code</label>
  <input type="text" name="hsn_code" id="hsn_code" class="form-control">
</div>

<div class="col-md-3">
  <label class="form-label">Unit</label>
  <select name="unit" id="unit" class="form-select">
    <option value="kg">kg</option>
    <option value="g">g</option>
    <option value="packet">packet</option>
    <option value="pcs">pcs</option>
  </select>
</div>

<div class="col-md-3">
  <label class="form-label">Net Weight</label>
  <input type="number" step="0.001" name="net_weight" id="net_weight" class="form-control" value="1.000">
</div>

<div class="col-md-3">
  <label class="form-label">Reorder Level</label>
  <input type="number" step="0.001" name="reorder_level" id="reorder_level" class="form-control" value="0.000">
</div>

<div class="col-md-3">
  <label class="form-label">Status</label>
  <select name="status" id="status" class="form-select">
    <option value="active">active</option>
    <option value="inactive">inactive</option>
  </select>
</div>
     
<!-- ===== SAVE BUTTON ===== -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-brand w-100">
          <i class="bi bi-save"></i> Save
        </button>
      </div>
    </form>          <!-- </form> CLOSE -->
  </div>             <!-- /.modal-dialog -->
</div>               <!-- /.modal -->
      </div>

<!-- ===== FOOTER START ===== -->
<footer>
  <div class="footer-container">
    <div class="footer-col">
      <h4>📞 Contact Us</h4>
      <ul>
        <li><i class="fa fa-phone"></i> +91 76788 53017</li>
        <li><i class="fa fa-envelope"></i> support@rrbusiness.com</li>
        <li><i class="fa fa-envelope-open"></i> care@rrbusiness.com</li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>🔗 Quick Links</h4>
      <ul>
        <li><a href="dashboard.php"> Dashboard</a></li>
        <li><a href="admin_pannel.php"> Manage Products</a></li>
        <li><a href="view_order.php"> Orders</a></li>
        <li><a href="customer_queries.php"> Queries</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>📱 Follow Us</h4>
      <ul>
        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
        <li><a href="https://www.instagram.com/rrbusiness2025" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
        <li><a href="https://wa.me/917678853017"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> <strong>R.R. Business</strong> — All Rights Reserved</p>
    <p>🚀 Developed by <strong>V.G Technologies Pvt. Ltd.</strong></p>
  </div>
</footer>
<!-- ===== SCRIPTS ===== -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
function updateClock(){document.getElementById('clock').textContent=new Date().toLocaleString();}
setInterval(updateClock,1000);updateClock();

$(function(){
  // Initialize DataTable with full functionality
  const table = $('#invTable').DataTable({
    scrollX: true,
    paging: true,
    searching: true,
    ordering: true,
    responsive: true,
    language: {
      search: "🔍 Search:",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "No entries available",
      zeroRecords: "No matching records found"
    }
  });

  // Reset form when Add Item clicked
  $('[data-bs-target="#addModal"]').on('click',()=>{ 
    $('#invForm')[0].reset(); 
    $('#inv_id').val(''); 
    $('.modal-title').text('Add Item'); 
  });

  // Load data into form for editing
  $(document).on('click','.editBtn',function(){
    $.getJSON('inventory_get.php',{id:$(this).data('id')}, d=>{
      if(d.error){ alert(d.error); return; }
      Object.entries(d).forEach(([k,v])=>$('#'+k).val(v));
      $('.modal-title').text('Edit Item'); $('#addModal').modal('show');
    });
  });

  // Submit form to save inventory
  $('#invForm').on('submit',function(e){
    e.preventDefault();
    $.post('inventory_save.php', $(this).serialize())
      .done(()=>location.reload())
      .fail(xhr => {
        if(xhr.responseText.includes('Duplicate entry')){
          alert('Save failed: Product code already exists. Please use a unique code or edit the existing entry.');
        } else {
          alert('Save failed: '+xhr.responseText);
        }
      });
  });

  // ===== Inventory CSV Download =====
  $('#downloadBtn').on('click', function() {
    let rows=[];
    $('#invTable thead tr, #invTable tbody tr').each(function(){
      let cols=[];
      $(this).find('th,td').each(function(){
        cols.push('"'+$(this).text().trim().replace(/"/g,'""')+'"');
      });
      rows.push(cols.join(','));
    });
    let blob=new Blob([rows.join('\n')],{type:'text/csv;charset=utf-8;'});
    let link=document.createElement('a');
    link.href=URL.createObjectURL(blob);
    link.download='inventory_rrbusiness.csv';
    link.click();
  });
});
</script>

</body>
</html>
