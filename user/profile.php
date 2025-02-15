  
  <?php include './layout/n_sidebar.php' ?>
  <?php include './layout/n_nav.php' ?>
<?php

// ดึงข้อมูลผู้ใช้ปัจจุบัน
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

$success = '';
$error = '';

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        switch ($_POST['action']) {
            case 'update_profile':
                // อัพเดทข้อมูลส่วนตัว
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, 
                        email = ?, 
                        height = ?, 
                        weight = ?, 
                        age = ?, 
                        gender = ?,
                        updated_at = '2025-02-14 18:47:08'
                    WHERE user_id = ?
                ");
                $stmt->execute([
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['height'],
                    $_POST['weight'],
                    $_POST['age'],
                    $_POST['gender'],
                    $user['user_id']
                ]);
                $success = "อัพเดทข้อมูลส่วนตัวสำเร็จ";
                break;

            case 'change_password':
                // ตรวจสอบรหัสผ่านเดิม
                if ($_POST['current_password'] !== $user['password']) {
                    $error = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
                    break;
                }

                // ตรวจสอบรหัสผ่านใหม่
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $error = "รหัสผ่านใหม่ไม่ตรงกัน";
                    break;
                }

                // อัพเดทรหัสผ่าน
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET password = ?, 
                        updated_at = NOW()      WHERE user_id = ?
                ");
                $stmt->execute([$_POST['new_password'], $user['user_id']]);
                $success = "เปลี่ยนรหัสผ่านสำเร็จ";
                break;
        }

        // ดึงข้อมูลผู้ใช้ใหม่หลังอัพเดท
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $user = $stmt->fetch();

    } catch(PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Begin Page Content -->
  <div class="container-fluid">

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">จัดการโปรไฟล์</h1>
   
</div>

<body>
<div class="row">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
     
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- ข้อมูลส่วนตัว -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลส่วนตัว</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">อายุ</label>
                                <input type="number" class="form-control" name="age" 
                                       value="<?php echo $user['age']; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">น้ำหนัก (kg)</label>
                                <input type="number" step="0.1" class="form-control" name="weight" 
                                       value="<?php echo $user['weight']; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ส่วนสูง (cm)</label>
                                <input type="number" step="0.1" class="form-control" name="height" 
                                       value="<?php echo $user['height']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เพศ</label>
                            <select class="form-select" name="gender" required>
                                <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>ชาย</option>
                                <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>หญิง</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> บันทึกข้อมูล
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- เปลี่ยนรหัสผ่าน -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">เปลี่ยนรหัสผ่าน</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label class="form-label">รหัสผ่านปัจจุบัน</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" name="new_password" 
                                   minlength="8" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" name="confirm_password" 
                                   minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> เปลี่ยนรหัสผ่าน
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// เพิ่ม JavaScript สำหรับตรวจสอบรหัสผ่าน
document.querySelector('form[action="change_password"]').addEventListener('submit', function(e) {
    const newPassword = this.querySelector('[name="new_password"]').value;
    const confirmPassword = this.querySelector('[name="confirm_password"]').value;

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('รหัสผ่านใหม่ไม่ตรงกัน');
    }
});
</script>
</body>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<?php include './layout/u_footer.php' ?>