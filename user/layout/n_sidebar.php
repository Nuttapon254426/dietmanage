<?php
session_start();
include '../config.php';

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user role from session
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href=".././vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href=".././css/sb-admin-2.min.css" rel="stylesheet">
    <link href=".././vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="./user_dashboard.php">
               
                <div class="sidebar-brand-text mx-3">ระบบการจัดการการรับประทานอาหาร</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <?php if($user_role == 'user'): ?>
            <li class="nav-item active">
                <a class="nav-link" href="./user_dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>บันทึกมื้ออาหารประจำวัน</span></a>
            </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>เมนู</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">รายการ:</h6>
                        <?php if($user_role == 'user'): ?>
                        <a class="collapse-item" href="./calorietracking.php">การติดตามแคลอรี่</a>
                        <a class="collapse-item" href="./food_management.php">การจัดการข้อมูลอาหาร</a>
                        <?php endif; ?>
                        <?php if($user_role == 'nutritionist'): ?>
                        <a class="collapse-item" href="./nutrition_plan.php">จัดการแผนโภชนาการ</a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
          

            <!-- Divider -->
         

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

     
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
