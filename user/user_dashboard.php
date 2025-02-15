  
  <?php include './layout/n_sidebar.php' ?>
  <?php include './layout/n_nav.php' ?>
  <?php

// ดึงข้อมูลรายการอาหารทั้งหมดจากฐานข้อมูล
$stmt = $pdo->prepare("SELECT food_id, food_name, calories, serving_size FROM food_items");
$stmt->execute();
$food_items = $stmt->fetchAll();

// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $food_id = $_POST['food_id'];
        $meal_type = $_POST['meal_type'];
        $serving_amount = $_POST['serving_amount'];
        $meal_date = $_POST['meal_date'];
        $meal_time = $_POST['meal_time'];
        $notes = $_POST['notes'];
        
        $sql = "INSERT INTO meal_records (user_id, food_id, meal_type, serving_amount, meal_date, meal_time, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'],
            $food_id,
            $meal_type,
            $serving_amount,
            $meal_date,
            $meal_time,
            $notes
        ]);
        
        $success_message = "บันทึกมื้ออาหารเรียบร้อยแล้ว!";
    } catch(PDOException $e) {
        $error_message = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดึงบันทึกมื้ออาหารของวันนี้
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT m.*, f.food_name, f.calories 
    FROM meal_records m 
    JOIN food_items f ON m.food_id = f.food_id 
    WHERE m.user_id = ? AND m.meal_date = ? 
    ORDER BY m.meal_time ASC
");
$stmt->execute([$_SESSION['user_id'], $today]);
$today_meals = $stmt->fetchAll();
?>
  <!-- Begin Page Content -->
  <div class="container-fluid">

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">บันทึกมื้ออาหารประจำวัน</h1>
 
</div>


<div class="row">
       
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- ฟอร์มบันทึกมื้ออาหาร -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="food_id" class="form-label">เลือกอาหาร :</label>
                            <select class="form-select" id="food_id" name="food_id" required>
                                <option value="">เลือกรายการอาหาร</option>
                                <?php foreach ($food_items as $food): ?>
                                    <option value="<?php echo $food['food_id']; ?>">
                                        <?php echo $food['food_name']; ?> 
                                        (<?php echo $food['calories']; ?> แคลอรี่/<?php echo $food['serving_size']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="meal_type" class="form-label">มื้ออาหาร :</label>
                            <select class="form-select" id="meal_type" name="meal_type" required>
                                <option value="breakfast">มื้อเช้า</option>
                                <option value="lunch">มื้อกลางวัน</option>
                                <option value="dinner">มื้อเย็น</option>
                                <option value="snack">ของว่าง</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="serving_amount" class="form-label">จำนวนที่รับประทาน</label>
                            <input type="number" step="0.1" class="form-control" id="serving_amount" 
                                   name="serving_amount" required min="0.1">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="meal_date" class="form-label">วันที่</label>
                            <input type="date" class="form-control" id="meal_date" 
                                   name="meal_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="meal_time" class="form-label">เวลา</label>
                            <input type="time" class="form-control" id="meal_time" 
                                   name="meal_time" required value="<?php echo date('H:i'); ?>">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">บันทึกเพิ่มเติม</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">บันทึกมื้ออาหาร</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการอาหารวันนี้ -->
        <div class="card">
            <div class="card-header">
                <h5>รายการอาหารวันนี้ (<?php echo date('d/m/Y'); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($today_meals) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>เวลา</th>
                                    <th>มื้อ</th>
                                    <th>รายการอาหาร</th>
                                    <th>ปริมาณ</th>
                                    <th>แคลอรี่</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_calories = 0;
                                foreach ($today_meals as $meal): 
                                    $meal_calories = $meal['calories'] * $meal['serving_amount'];
                                    $total_calories += $meal_calories;
                                    
                                    // แปลงประเภทมื้ออาหารเป็นภาษาไทย
                                    $meal_type_th = [
                                        'breakfast' => 'มื้อเช้า',
                                        'lunch' => 'มื้อกลางวัน',
                                        'dinner' => 'มื้อเย็น',
                                        'snack' => 'ของว่าง'
                                    ][$meal['meal_type']];
                                ?>
                                    <tr>
                                        <td><?php echo date('H:i', strtotime($meal['meal_time'])); ?></td>
                                        <td><?php echo $meal_type_th; ?></td>
                                        <td><?php echo $meal['food_name']; ?></td>
                                        <td><?php echo $meal['serving_amount']; ?></td>
                                        <td><?php echo number_format($meal_calories, 0); ?></td>
                                        <td><?php echo $meal['notes']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-info">
                                    <td colspan="4" class="text-end"><strong>รวมแคลอรี่ทั้งหมด:</strong></td>
                                    <td><strong><?php echo number_format($total_calories, 0); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">ยังไม่มีการบันทึกมื้ออาหารสำหรับวันนี้</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // กำหนดค่า flatpickr สำหรับ date picker
        flatpickr("#meal_date", {
            dateFormat: "Y-m-d",
            defaultDate: "today"
        });

        // กำหนดค่า flatpickr สำหรับ time picker
        flatpickr("#meal_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            defaultDate: new Date()
        });
    </script>
</body>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<?php include './layout/u_footer.php' ?>