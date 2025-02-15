<?php include './layout/n_sidebar.php' ?>
<?php include './layout/n_nav.php' ?>
<?php

// ฟังก์ชันคำนวณแคลอรี่รายวัน
function getDailyCalories($pdo, $userId, $date) {
    $sql = "SELECT 
                m.meal_type,
                SUM(f.calories * m.serving_amount) as meal_calories
            FROM meal_records m 
            JOIN food_items f ON m.food_id = f.food_id 
            WHERE m.user_id = ? AND DATE(m.meal_date) = ?
            GROUP BY m.meal_type";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ฟังก์ชันคำนวณแคลอรี่รายสัปดาห์
function getWeeklyCalories($pdo, $userId) {
    $sql = "SELECT 
                DATE(m.meal_date) as date,
                SUM(f.calories * m.serving_amount) as total_calories
            FROM meal_records m 
            JOIN food_items f ON m.food_id = f.food_id 
            WHERE m.user_id = ? 
            AND m.meal_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
            GROUP BY DATE(m.meal_date)
            ORDER BY date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// เพิ่มฟังก์ชันใหม่เพื่อดึงแผนโภชนาการที่ใช้งานอยู่
function getActiveNutritionPlan($pdo, $userId) {
    $sql = "SELECT target_calories 
            FROM nutrition_plans 
            WHERE user_id = ? 
            AND CURRENT_DATE BETWEEN start_date AND end_date 
            ORDER BY plan_id DESC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['target_calories'] : 2000; // ค่าเริ่มต้นถ้าไม่มีแผน
}

// ดึงข้อมูลแคลอรี่
$today_calories = getDailyCalories($pdo, $_SESSION['user_id'], date('Y-m-d'));
$weekly_calories = getWeeklyCalories($pdo, $_SESSION['user_id']);
$target_calories = getActiveNutritionPlan($pdo, $_SESSION['user_id']);

// คำนวณแคลอรี่รวมวันนี้
$total_today = 0;
foreach ($today_calories as $meal) {
    $total_today += $meal['meal_calories'];
}

// สร้างข้อมูลสำหรับกราฟ
$chart_labels = [];
$chart_data = [];
foreach ($weekly_calories as $day) {
    $chart_labels[] = date('d/m', strtotime($day['date']));
    $chart_data[] = round($day['total_calories']);
}
?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Begin Page Content -->
  <div class="container-fluid">

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">ติดตามการบริโภคแคลอรี่</h1>
   
</div>

<body>
    <div class="row">
      
        
        <!-- การ์ดแสดงแคลอรี่วันนี้ -->
        <div class="row mb-4">
        <div class="col-xl-10 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>แคลอรี่วันนี้ (<?php echo date('d/m/Y'); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <?php
                                $meal_types_th = [
                                    'breakfast' => 'มื้อเช้า',
                                    'lunch' => 'มื้อกลางวัน',
                                    'dinner' => 'มื้อเย็น',
                                    'snack' => 'ของว่าง'
                                ];

                                foreach ($today_calories as $meal) {
                                    $meal_type_th = $meal_types_th[$meal['meal_type']] ?? $meal['meal_type'];
                                    echo "<div class='d-flex justify-content-between mb-2'>";
                                    echo "<span>{$meal_type_th}</span>";
                                    echo "<span>" . number_format($meal['meal_calories'], 0) . " แคลอรี่</span>";
                                    echo "</div>";
                                }
                                ?>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>รวมทั้งหมด</strong>
                                    <strong><?php echo number_format($total_today, 0); ?> แคลอรี่</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- เพิ่มวงกลมแสดงเปอร์เซ็นต์ของเป้าหมาย -->
                                <div class="progress-circle" style="width: 120px; height: 120px;">
                                    <canvas id="todayProgress"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- การ์ดแสดงสถิติ -->
            <div class="col-xl-10 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>สถิติย้อนหลัง 7 วัน</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางประวัติการบริโภคย้อนหลัง -->
        <div class="card col-xl-10 col-lg-8 ">
            <div class="card-header">
                <h5>ประวัติการบริโภคย้อนหลัง</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>แคลอรี่รวม</th>
                                <th>เทียบกับเป้าหมาย</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($weekly_calories as $day): ?>
                                <?php 
                                $percentage = ($day['total_calories'] / $target_calories) * 100;
                                $status_class = $percentage > 100 ? 'text-danger' : 'text-success';
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                                    <td><?php echo number_format($day['total_calories'], 0); ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $status_class; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min($percentage, 100); ?>%">
                                                <?php echo round($percentage); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="<?php echo $status_class; ?>">
                                        <?php echo $percentage > 100 ? 'เกินเป้าหมาย' : 'อยู่ในเกณฑ์'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
 

    <script>
        // กราฟวงกลมแสดงเปอร์เซ็นต์ของวันนี้
        const todayProgress = new Chart(document.getElementById('todayProgress'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [<?php echo $total_today; ?>, <?php echo max(0, $target_calories - $total_today); ?>],
                    backgroundColor: ['#36A2EB', '#E0E0E0']
                }]
            },
            options: {
                cutout: '80%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // กราฟแท่งแสดงแคลอรี่รายวัน
        const weeklyChart = new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'แคลอรี่รวมรายวัน',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'แคลอรี่'
                        }
                    }
                }
            }
        });

    
    </script>
</body>


</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<?php include './layout/u_footer.php' ?>