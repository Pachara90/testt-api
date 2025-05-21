<?php include "includes/head.php"; ?>



<!-- Page Wrapper -->
<div id="wrapper">

    <?php include "includes/sidebar.php"; ?>

    <!-- Content Wrapper. Contains page"?>

        <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <?php include "includes/topbar.php"; ?>
            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                </div>


                <!-- Content Row -->

                <div class="row">
                    <?php
                    require_once "db.php";

                    // ดึงข้อมูลจำนวนนับผู้เข้าร่วมต่อกิจกรรม
                    $stmt = $conn->query("
    SELECT e.title, COUNT(p.id) AS total 
    FROM events e 
    LEFT JOIN participants p ON e.id = p.event_id 
    GROUP BY e.id
");
                    $eventTitles = [];
                    $eventCounts = [];

                    while ($row = $stmt->fetch_assoc()) {
                        $eventTitles[] = $row['title'];
                        $eventCounts[] = $row['total'];
                    }
                    ?>

                    <div class="col-lg-12 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">สถิติผู้เข้าร่วมอบรมแต่ละกิจกรรม</h6>
                            </div>
                            <div class="card-body" style="height: 300px;">
                                <canvas id="eventBarChart" style="height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>


                </div>


                <!-- Content Row -->
                <div class="row">

                    <!-- Content Column -->
                    <div class="col-lg-6 mb-4">

                        <!-- Project Card Example -->

                    </div>
                    <!-- /.container-fluid -->

                </div>
                <!-- End of Main Content -->

                <!-- Footer -->
                <footer class="sticky-footer bg-white">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            <span>Copyright &copy; Your Website 2021</span>
                        </div>
                    </div>
                </footer>
                <!-- End of Footer -->

            </div>
            <!-- End of Content Wrapper -->

        </div>
        <!-- End of Page Wrapper -->

        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>

        <!-- Logout Modal-->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" href="login.html">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart.js -->
        <script src="vendor/chart.js/Chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('eventBarChart').getContext('2d');
    const eventBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($eventTitles); ?>,
            datasets: [{
                label: 'จำนวนผู้เข้าร่วม',
                data: <?php echo json_encode($eventCounts); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // เพิ่มบรรทัดนี้เพื่อให้ขนาด canvas ที่ตั้งไว้มีผล
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>


        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="js/sb-admin-2.min.js"></script>

        <!-- Page level plugins -->
        <script src="vendor/chart.js/Chart.min.js"></script>

        <!-- Page level custom scripts -->
        <script src="js/demo/chart-area-demo.js"></script>
        <script src="js/demo/chart-pie-demo.js"></script>

        </body>

        </html>