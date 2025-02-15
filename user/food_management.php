<?php include './layout/n_sidebar.php' ?>
  <?php include './layout/n_nav.php' ?>
  <?php

// ฟังก์ชันดึงข้อมูลอาหารทั้งหมด
function getAllFoodItems($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM food_items ORDER BY food_name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// จัดการ POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ใช้ค่า datetime ที่ผู้ใช้เลือก หรือใช้เวลาปัจจุบันถ้าไม่ได้เลือก
    $current_time = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');

    try {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO food_items (food_name, calories, protein, carbohydrates, fat, fiber, serving_size, created_by, created_at) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['food_name'],
                    $_POST['calories'],
                    $_POST['protein'],
                    $_POST['carbohydrates'],
                    $_POST['fat'],
                    $_POST['fiber'],
                    $_POST['serving_size'],
                    $_SESSION['user_id'],
                    $current_time
                ]);
                $success = "เพิ่มรายการอาหารสำเร็จ";
                break;

            case 'edit':
                $stmt = $pdo->prepare("UPDATE food_items SET 
                    food_name = ?, calories = ?, protein = ?, 
                    carbohydrates = ?, fat = ?, fiber = ?, 
                    serving_size = ? WHERE food_id = ?");
                $stmt->execute([
                    $_POST['food_name'],
                    $_POST['calories'],
                    $_POST['protein'],
                    $_POST['carbohydrates'],
                    $_POST['fat'],
                    $_POST['fiber'],
                    $_POST['serving_size'],
                    $_POST['food_id']
                ]);
                $success = "แก้ไขข้อมูลสำเร็จ";
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM food_items WHERE food_id = ?");
                $stmt->execute([$_POST['food_id']]);
                $success = "ลบรายการอาหารสำเร็จ";
                break;
        }
    } catch(PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดึงข้อมูลอาหารทั้งหมด
$food_items = getAllFoodItems($pdo);
?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Begin Page Content -->
  <div class="container-fluid">

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">จัดการข้อมูลอาหาร</h1>
  
</div>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>จัดการข้อมูลอาหาร</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFoodModal">
                <i class="bi bi-plus-circle"></i> เพิ่มรายการอาหาร
            </button>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- ตารางแสดงรายการอาหาร -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id = "dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ชื่ออาหาร</th>
                                <th>แคลอรี่</th>
                                <th>โปรตีน (g)</th>
                                <th>คาร์โบไฮเดรต (g)</th>
                                <th>ไขมัน (g)</th>
                                <th>ใยอาหาร (g)</th>
                                <th>ปริมาณต่อหน่วย</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($food_items as $food): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($food['food_name']); ?></td>
                                <td><?php echo number_format($food['calories'], 1); ?></td>
                                <td><?php echo number_format($food['protein'], 1); ?></td>
                                <td><?php echo number_format($food['carbohydrates'], 1); ?></td>
                                <td><?php echo number_format($food['fat'], 1); ?></td>
                                <td><?php echo number_format($food['fiber'], 1); ?></td>
                                <td><?php echo htmlspecialchars($food['serving_size']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="editFood(<?php echo htmlspecialchars(json_encode($food)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $food['food_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal เพิ่มรายการอาหาร -->
    <div class="modal fade" id="addFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มรายการอาหารใหม่</h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <!-- เพิ่ม input สำหรับเลือกวันที่และเวลา -->
                        <div class="mb-3">
                            <label class="form-label">วันที่และเวลาที่สร้าง</label>
                            <input type="datetime-local" class="form-control" name="created_at" 
                                   value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ชื่ออาหาร</label>
                            <input type="text" class="form-control" name="food_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">แคลอรี่</label>
                            <input type="number" step="0.1" class="form-control" name="calories" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">โปรตีน (g)</label>
                            <input type="number" step="0.1" class="form-control" name="protein" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">คาร์โบไฮเดรต (g)</label>
                            <input type="number" step="0.1" class="form-control" name="carbohydrates" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ไขมัน (g)</label>
                            <input type="number" step="0.1" class="form-control" name="fat" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ใยอาหาร (g)</label>
                            <input type="number" step="0.1" class="form-control" name="fiber" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ปริมาณต่อหน่วย</label>
                            <input type="text" class="form-control" name="serving_size" required 
                                   placeholder="เช่น 100 กรัม, 1 จาน, 1 ถ้วย">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button> -->
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal แก้ไขข้อมูล -->
    <div class="modal fade" id="editFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขข้อมูลอาหาร</h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="food_id" id="edit_food_id">
                        <div class="mb-3">
                            <label class="form-label">ชื่ออาหาร</label>
                            <input type="text" class="form-control" name="food_name" id="edit_food_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">แคลอรี่</label>
                            <input type="number" step="0.1" class="form-control" name="calories" id="edit_calories" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">โปรตีน (g)</label>
                            <input type="number" step="0.1" class="form-control" name="protein" id="edit_protein" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">คาร์โบไฮเดรต (g)</label>
                            <input type="number" step="0.1" class="form-control" name="carbohydrates" id="edit_carbohydrates" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ไขมัน (g)</label>
                            <input type="number" step="0.1" class="form-control" name="fat" id="edit_fat" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ใยอาหาร (g)</label>
                            <input type="number" step="0.1" class="form-control" name="fiber" id="edit_fiber" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ปริมาณต่อหน่วย</label>
                            <input type="text" class="form-control" name="serving_size" id="edit_serving_size" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button> -->
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการลบรายการอาหารนี้ใช่หรือไม่?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="food_id" id="delete_food_id">
                        <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button> -->
                        <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editFood(food) {
            document.getElementById('edit_food_id').value = food.food_id;
            document.getElementById('edit_food_name').value = food.food_name;
            document.getElementById('edit_calories').value = food.calories;
            document.getElementById('edit_protein').value = food.protein;
            document.getElementById('edit_carbohydrates').value = food.carbohydrates;
            document.getElementById('edit_fat').value = food.fat;
            document.getElementById('edit_fiber').value = food.fiber;
            document.getElementById('edit_serving_size').value = food.serving_size;
            
            new bootstrap.Modal(document.getElementById('editFoodModal')).show();
        }

        function confirmDelete(foodId) {
            document.getElementById('delete_food_id').value = foodId;
            new bootstrap.Modal(document.getElementById('deleteFoodModal')).show();
        }
    </script>
</body>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<?php include './layout/u_footer.php' ?>