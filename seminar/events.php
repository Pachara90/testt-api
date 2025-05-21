<?php include 'includes/head.php'; ?>
<?php
require 'db.php';

$search = $_GET['search'] ?? ''; // รับคำค้นหาจาก URL

try {
    $sql = 'SELECT p.*, e.title AS event_title
            FROM participants p
            INNER JOIN events e ON p.event_id = e.id';
    if ($search) {
        $sql .= " WHERE p.fullname LIKE :search_fullname OR p.email LIKE :search_email"; // เพิ่มเงื่อนไขการค้นหา
    }
    $stmt = $conn->prepare($sql);
    if ($search) {
        $stmt->bindValue(':search_fullname', '%' . $search . '%'); // ผูกค่าคำค้นหา
        $stmt->bindValue(':search_email', '%' . $search . '%');
    }
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set
    $participants = [];
    while ($row = $result->fetch_assoc()) { // Fetch each row
        $participants[] = $row;
    }

    $sql = 'SELECT e.title, COUNT(p.id) AS participant_count, e.max_participants
            FROM events e
            LEFT JOIN participants p ON e.id = p.event_id
            GROUP BY e.id';            
    $result = mysqli_query($conn, $sql);
    $eventCounts = mysqli_fetch_all($result, MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    exit;
}
?>
<!-- Page Wrapper -->
<div id="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <?php include 'includes/topbar.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Your page content here -->
                <h1>จำนวนผู้เข้าร่วมแต่ละกิจกรรม</h1>

                <div class="container-fluid p-4">
                    <div class="card p-4 bg-white">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#registerModal" style="width: 20%; text-align: center;">
                            เพิ่มกิจกรรม
                        </button>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>กิจกรรม</th>
                                    <th>จำนวนผู้เข้าร่วม</th>
                                    <th>จำนวนผู้เข้าร่วมสูงสุด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventCounts as $count): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($count['title']) ?></td>
                                        <td><?= htmlspecialchars($count['participant_count']) ?></td>
                                        <td><?= isset($count['max_participants']) ? htmlspecialchars($count['max_participants']) : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <?php include 'includes/footer.php'; ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    <?php include 'includes/modals.php'; ?>
    <?php include 'includes/scripts.php'; ?>