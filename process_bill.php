<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $customer_name = $_POST['customer_name'];
  $phone         = $_POST['phone'];
  $address       = $_POST['address'];
  $product_id    = $_POST['product_id'];
  $quantity      = intval($_POST['quantity']);
  $gst_percent   = floatval($_POST['gst_percent']);
  $bill_date     = date('Y-m-d');

  // Fetch product
  $product_q = $conn->query("SELECT * FROM products WHERE id = $product_id");
  $product = $product_q->fetch_assoc();
  $product_name = $product['name'];
  $rate = floatval($product['price']);
  $amount = $rate * $quantity;
  $gst_amount = ($amount * $gst_percent) / 100;
  $total = $amount + $gst_amount;

  // Save bill to database
  $item_data = "$product_name x $quantity @ ₹$rate";
  $stmt = $conn->prepare("INSERT INTO bills (customer_name, phone, address, bill_date, total_amount, items) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssds", $customer_name, $phone, $address, $bill_date, $total, $item_data);
  $stmt->execute();
  $bill_id = $stmt->insert_id;

  // Optional: Update stock (if inventory table exists)
  $conn->query("UPDATE inventory SET quantity = quantity - $quantity WHERE product_id = $product_id");

 /* ----------  MODERN PDF  ---------- */
require_once __DIR__.'/fpdf/fpdf.php';   // सिर्फ़ एक बार

/*==========  INPUT & CALCULATION  ==========*/
$product_q   = $conn->query("SELECT name,price,gst_percent FROM products WHERE id=$product_id");
$product     = $product_q->fetch_assoc();
$product_name= $product['name'];
$rate        = (float)$product['price'];
$gst_percent = (float)$product['gst_percent'];      // db से पक्का
$amount      = $rate * $quantity;
$cgst_rate   = $gst_percent / 2;
$sgst_rate   = $gst_percent / 2;
$cgst_amt    = $amount * $cgst_rate / 100;
$sgst_amt    = $amount * $sgst_rate / 100;
$total       = $amount + $cgst_amt + $sgst_amt;

/*==========  SAVE BILL (same as पहले)  ==========*/
$item_data = "$product_name x $quantity @ ₹$rate";   // चाहें तो GST जोड़ें
/* … prepared-stmt insert जैसी आपकी पुरानी code … */

/*==========  MODERN GST PDF  ==========*/
class PDF extends FPDF{
  function Header(){
    if(file_exists('images/Logo.png')) $this->Image('images/Logo.png',10,8,25);
    $this->SetFont('Arial','B',15); $this->Cell(0,8,'R.R. BUSINESS',0,1,'C');
    $this->SetFont('Arial','',9);   $this->Cell(0,4,'Spices & Agro Products (GSTIN: 09ABCDE1234F1Z5)',0,1,'C');
    $this->Ln(2); $this->SetDrawColor(168,50,50); $this->Line(10,$this->GetY(),200,$this->GetY()); $this->Ln(4);
    $this->SetFont('Arial','B',13);$this->Cell(0,7,'TAX INVOICE',0,1,'C'); $this->Ln(2);
  }
  function Footer(){
    $this->SetY(-15); $this->SetFont('Arial','I',8); $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
  }
}

$pdf = new PDF(); $pdf->AddPage();

/*--- Customer / Invoice Meta ---*/
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6,"Customer: $customer_name",0,0);
$pdf->Cell(0,6,"Invoice #: $bill_id",0,1);
$pdf->Cell(100,6,"Mobile  : $phone",0,0);
$pdf->Cell(0,6,"Date     : $bill_date",0,1);
$pdf->MultiCell(0,6,"Address : $address");
$pdf->Ln(4);

/*--- Item table with CGST/SGST ---*/
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(168,50,50); $pdf->SetTextColor(255);
$pdf->Cell(60,8,'Item',1,0,'C',true);
$pdf->Cell(20,8,'Qty',1,0,'C',true);
$pdf->Cell(25,8,'Rate',1,0,'C',true);
$pdf->Cell(25,8,'Amount',1,0,'C',true);
$pdf->Cell(30,8,"CGST ($cgst_rate%)",1,0,'C',true);
$pdf->Cell(30,8,"SGST ($sgst_rate%)",1,1,'C',true);

$pdf->SetFont('Arial','',10); $pdf->SetTextColor(0);
$pdf->Cell(60,8,$product_name,1);
$pdf->Cell(20,8,$quantity,1,0,'C');
$pdf->Cell(25,8,number_format($rate,2),1,0,'R');
$pdf->Cell(25,8,number_format($amount,2),1,0,'R');
$pdf->Cell(30,8,number_format($cgst_amt,2),1,0,'R');
$pdf->Cell(30,8,number_format($sgst_amt,2),1,1,'R');

/*--- Totals ---*/
$pdf->SetFont('Arial','B',10);
$pdf->Cell(130,8,'Sub-Total',1,0,'R',true);         // amount
$pdf->Cell(60,8,number_format($amount,2),1,1,'R',true);
$pdf->Cell(130,8,'CGST',1,0,'R',true);
$pdf->Cell(60,8,number_format($cgst_amt,2),1,1,'R',true);
$pdf->Cell(130,8,'SGST',1,0,'R',true);
$pdf->Cell(60,8,number_format($sgst_amt,2),1,1,'R',true);
$pdf->Cell(130,8,'Grand Total',1,0,'R',true);
$pdf->Cell(60,8,number_format($total,2),1,1,'R',true);

/*--- Save & Redirect ---*/
$filename = "bills/BILL_$bill_id.pdf";
if(!is_dir('bills')) mkdir('bills');
$pdf->Output('F',$filename);
echo "<script>alert('Bill generated successfully!'); location.href='$filename';</script>";
exit;

}
?>
