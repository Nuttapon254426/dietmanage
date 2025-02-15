<?php include './layout/n_sidebar.php' ?>
  <?php include './layout/n_nav.php' ?>
  <?php

// ฟังก์ชันดึงข้อมูลผู้ใช้ทั้งหมด (ยกเว้น admin และ nutritionist)
function getAllUsers($pdo) {
    $stmt = $pdo->prepare("SELECT user_id, username, full_name, height, weight, age, gender FROM users WHERE role = 'user'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ฟังก์ชันดึงแผนโภชนาการทั้งหมด
function getNutritionPlans($pdo, $nutritionist_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, u.full_name as user_name 
        FROM nutrition_plans p 
        JOIN users u ON p.user_id = u.user_id 
        WHERE p.nutritionist_id = ? 
        ORDER BY p.created_at DESC");
    $stmt->execute([$nutritionist_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("
                    INSERT INTO nutrition_plans (
                        user_id, nutritionist_id, plan_name, start_date, 
                        end_date, target_calories, notes, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, '2025-02-14 17:42:44')");
                $stmt->execute([
                    $_POST['user_id'],
                    $_SESSION['user_id'],
                    $_POST['plan_name'],
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $_POST['target_calories'],
                    $_POST['notes']
                ]);
                $success = "เพิ่มแผนโภชนาการสำเร็จ";
                break;

            case 'edit':
                $stmt = $pdo->prepare("
                    UPDATE nutrition_plans 
                    SET plan_name = ?, start_date = ?, end_date = ?, 
                        target_calories = ?, notes = ? 
                    WHERE plan_id = ? AND nutritionist_id = ?");
                $stmt->execute([
                    $_POST['plan_name'],
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $_POST['target_calories'],
                    $_POST['notes'],
                    $_POST['plan_id'],
                    $_SESSION['user_id']
                ]);
                $success = "แก้ไขแผนโภชนาการสำเร็จ";
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM nutrition_plans WHERE plan_id = ? AND nutritionist_id = ?");
                $stmt->execute([$_POST['plan_id'], $_SESSION['user_id']]);
                $success = "ลบแผนโภชนาการสำเร็จ";
                break;
        }
    } catch(PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

$users = getAllUsers($pdo);
$nutrition_plans = getNutritionPlans($pdo, $_SESSION['user_id']);
?>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Begin Page Content -->
  <div class="container-fluid">

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">จัดการแผนโภชนาการ</h1>
  
</div>

<body>
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-4">
          
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="bi bi-plus-circle"></i> สร้างแผนโภชนาการใหม่
            </button>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- แสดงแผนโภชนาการทั้งหมด -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ชื่อแผน</th>
                                <th>ผู้ใช้งาน</th>
                                <th>วันที่เริ่ม</th>
                                <th>วันที่สิ้นสุด</th>
                                <th>เป้าหมายแคลอรี่</th>
                                <th>หมายเหตุ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nutrition_plans as $plan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                                <td><?php echo htmlspecialchars($plan['user_name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($plan['start_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($plan['end_date'])); ?></td>
                                <td><?php echo number_format($plan['target_calories']); ?> แคลอรี่</td>
                                <td><?php echo htmlspecialchars($plan['notes']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="editPlan(<?php echo htmlspecialchars(json_encode($plan)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $plan['plan_id']; ?>)">
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

    <!-- Modal เพิ่มแผนโภชนาการ -->
    <div class="modal fade" id="addPlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">สร้างแผนโภชนาการใหม่</h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">เลือกผู้ใช้งาน</label>
                                <select class="form-select" name="user_id" required>
                                    <option value="">เลือกผู้ใช้งาน</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['user_id']; ?>">
                                            <?php echo htmlspecialchars($user['full_name']); ?> 
                                            (<?php echo $user['age']; ?> ปี, 
                                            <?php echo $user['weight']; ?> kg)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ชื่อแผน</label>
                                <input type="text" class="form-control" name="plan_name" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">วันที่เริ่ม</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เป้าหมายแคลอรี่ต่อวัน</label>
                            <input type="number" class="form-control" name="target_calories" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">คำแนะนำและหมายเหตุ</label>
                            <textarea class="form-control" name="notes" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal แก้ไขแผนโภชนาการ -->
    <div class="modal fade" id="editPlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขแผนโภชนาการ</h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="plan_id" id="edit_plan_id">

                        <div class="mb-3">
                            <label class="form-label">ชื่อแผน</label>
                            <input type="text" class="form-control" name="plan_name" id="edit_plan_name" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">วันที่เริ่ม</label>
                                <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" name="end_date" id="edit_end_date" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เป้าหมายแคลอรี่ต่อวัน</label>
                            <input type="number" class="form-control" name="target_calories" id="edit_target_calories" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">คำแนะนำและหมายเหตุ</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="4"></textarea>
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
    <div class="modal fade" id="deletePlanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการลบแผนโภชนาการนี้ใช่หรือไม่?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="plan_id" id="delete_plan_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Include Bootstrap JS and fix inline modal functions -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Corrected modal handling functions
    function editPlan(plan) {
        // Set form values in edit modal
        document.getElementById('edit_plan_id').value = plan.plan_id;
        document.getElementById('edit_plan_name').value = plan.plan_name;
        document.getElementById('edit_start_date').value = plan.start_date;
        document.getElementById('edit_end_date').value = plan.end_date;
        document.getElementById('edit_target_calories').value = plan.target_calories;
        document.getElementById('edit_notes').value = plan.notes;
        // Show modal using Bootstrap's modal show method
        new bootstrap.Modal(document.getElementById('editPlanModal')).show();
    }

    function confirmDelete(planId) {
        // Set hidden plan id in the delete modal form
        document.getElementById('delete_plan_id').value = planId;
        new bootstrap.Modal(document.getElementById('deletePlanModal')).show();
    }
    </script>
</body>
</div>
<!-- /.container-fluid -->

</div>

<!-- End of Main Content -->
<?php include './layout/u_footer.php' ?>