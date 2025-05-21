<?php include 'includes/head.php'; ?>
<?php
require 'db.php';

$search = $_GET['search'] ?? ''; // รับคำค้นหาจาก URL

try {
    $sql = 'SELECT p.*, e.title AS event_title
            FROM participants p
            INNER JOIN events e ON p.event_id = e.id';
    if ($search) {
        $sql .= " WHERE p.fullname LIKE ? OR p.email LIKE ?"; // เพิ่มเงื่อนไขการค้นหา
    }
    $stmt = $conn->prepare($sql);
    if ($search) {
        $searchTerm = '%' . $search . '%';
        $stmt->bind_param('ss', $searchTerm, $searchTerm); // Use bind_param with positional placeholders
    }
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set
    $participants = [];
    while ($row = $result->fetch_assoc()) { // Fetch each row
        $participants[] = $row;
    }
} catch (PDOException $e) {
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
                <h1>รายชื่อผู้ลงทะเบียน</h1>

                <div class="container-fluid p-4">
                    <div class="card p-4 bg-white">

                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#registerModal" style="width: 20%; text-align: center;">
                            ลงทะเบียนเข้าร่วมกิจกรรม
                        </button>

                        <form method="get" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ หรือ อีเมล" value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-outline-secondary">ค้นหา</button>
                            </div>
                        </form>

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>กิจกรรม</th>
                                    <th>อีเมล</th>
                                    <th>เบอร์โทร</th>
                                    <!-- เพิ่มคอลัมน์อื่น ๆ ตามต้องการ -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['fullname']) ?></td>
                                        <td><?= htmlspecialchars($p['event_title']) ?></td>
                                        <td><?= htmlspecialchars($p['email']) ?></td>
                                        <td><?= htmlspecialchars($p['phone']) ?></td>
                                        <!-- แสดงข้อมูลอื่น ๆ ตามต้องการ -->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button onclick="exportToExcel()" class="btn btn-success" style="width: 20%; text-align: center;">Export เป็น Excel</button>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="registerModalLabel">ลงทะเบียนเข้าร่วมกิจกรรม</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="registerForm" novalidate>
                                        <div class="mb-3">
                                            <label for="event_id" class="form-label">เลือกกิจกรรม</label>
                                            <select class="form-select" id="event_id" required></select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
                                            <input type="text" id="fullname" class="form-control" placeholder="เช่น สมชาย ใจดี" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">อีเมล</label>
                                            <input type="email" id="email" class="form-control" placeholder="example@email.com" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">เบอร์โทร</label>
                                            <input type="text" id="phone" class="form-control" placeholder="08xxxxxxxx">
                                        </div>
                                        <div id="message" class="mb-3"></div>
                                        <button type="submit" class="btn btn-primary w-100">ลงทะเบียน</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <!-- /.container-fluid -->
        </div>

        <script>
            // โหลดรายการกิจกรรม
            fetch('api/events.php')
                .then(res => res.json())
                .then(data => {
                    const eventSelect = document.getElementById('event_id');
                    data.forEach(e => {
                        const opt = document.createElement('option');
                        opt.value = e.id;
                        opt.textContent = `${e.title} (${e.date})`;
                        eventSelect.appendChild(opt);
                    });
                });

            // จัดการฟอร์มเมื่อกด submit
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const payload = {
                    event_id: document.getElementById('event_id').value,
                    fullname: document.getElementById('fullname').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value
                };

                fetch('api/participants.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        const messageDiv = document.getElementById('message');
                        messageDiv.innerHTML = `<div class="alert alert-${data.error ? 'danger' : 'success'} alert-dismissible fade show alert-top-right" role="alert">
                                  ${data.error || 'ลงทะเบียนสำเร็จ'}
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>`;
                        if (!data.error) document.getElementById('registerForm').reset();


                    });
            });

        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script>
            function exportToExcel() {
                // Get the table data
                const table = document.querySelector("table");
                const data = [];
                const headerRow = table.rows[0];
                const headers = Array.from(headerRow.cells).map(cell => cell.textContent);
                data.push(headers);

                for (let i = 1; i < table.rows.length; i++) {
                    const row = table.rows[i];
                    const rowData = Array.from(row.cells).map(cell => cell.textContent);
                    data.push(rowData);
                }

                // Create a worksheet
                const ws = XLSX.utils.aoa_to_sheet(data);

                // Create a workbook
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Participants");

                // Generate Excel file and trigger download
                XLSX.writeFile(wb, "participants.xlsx");
            }
        </script>

        <!-- End of Main Content -->
        <?php include 'includes/footer.php'; ?>
    </div>
    <!-- End of Content Wrapper -->
</div>
<!-- End of Page Wrapper -->
<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?>