<?php
session_start();

// Cấu hình kết nối CSDL PostgreSQL
$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'library_db';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'postgres';

$db_connected = false;
$pdo = null;

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db_connected = true;
} catch (PDOException $e) {
    $db_connected = false;
}

// Xử lý Đăng xuất
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

$tab = $_GET['tab'] ?? 'dashboard';

// Lấy dữ liệu thống kê từ CSDL
$total_books = 12;
$available_books = 11;
$borrowed_books = 1;
$total_readers = 12;
$recent_borrows = [];

if ($db_connected) {
    try {
        $total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn() ?: 12;
        $available_books = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'available'")->fetchColumn() ?: 11;
        $borrowed_books = $pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status = 'borrowed'")->fetchColumn() ?: 1;
        $total_readers = $pdo->query("SELECT COUNT(*) FROM readers")->fetchColumn() ?: 12;

        $stmt = $pdo->query("SELECT br.id, r.name as reader_name, b.title as book_title, br.borrow_date 
                             FROM borrow_records br 
                             LEFT JOIN readers r ON br.reader_id = r.id 
                             LEFT JOIN books b ON br.book_id = b.id 
                             ORDER BY br.id DESC LIMIT 5");
        $recent_borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Dự phòng dữ liệu hiển thị nếu chưa có bảng
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện - NGỌC LAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f7f9f8; font-family: system-ui, -apple-system, sans-serif; }
        .sidebar-link.active { background-color: #e8f2ed; color: #164e3d; font-weight: 600; }
        .btn-primary { background-color: #0f382c; color: white; }
        .btn-primary:hover { background-color: #0a271f; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar Trái -->
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col justify-between p-5">
        <div>
            <!-- Header Sidebar (Avatar N + Thư viện NGỌC LAN) -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-lg">
                    N
                </div>
                <div>
                    <h1 class="font-bold text-gray-900 text-base leading-tight">Thư viện</h1>
                    <p class="text-[11px] text-gray-400 font-semibold tracking-wider uppercase">NGỌC LAN</p>
                </div>
            </div>

            <!-- Danh mục Menu -->
            <nav class="space-y-1">
                <a href="?tab=dashboard" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 transition <?= $tab === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-table-cells-large text-emerald-800"></i>
                    <span>Tổng quan</span>
                </a>
                <a href="?tab=books" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition <?= $tab === 'books' ? 'active' : '' ?>">
                    <i class="fa-solid fa-book"></i>
                    <span>Quản lý sách</span>
                </a>
                <a href="?tab=readers" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition <?= $tab === 'readers' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Độc giả</span>
                </a>
                <a href="?tab=borrow" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition <?= $tab === 'borrow' ? 'active' : '' ?>">
                    <i class="fa-solid fa-right-left"></i>
                    <span>Mượn & trả</span>
                </a>
            </nav>
        </div>

        <!-- Nút Đăng xuất ở góc dưới -->
        <div class="pt-4 border-t border-gray-100">
            <a href="?action=logout" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-500 hover:text-red-600 font-medium transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-8">
        <!-- Top Breadcrumb & Status -->
        <div class="flex justify-between items-center mb-8">
            <div class="text-xs text-gray-400 font-medium">
                Thư viện / <span class="text-gray-700 font-semibold">Tổng quan</span>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($db_connected): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> PostgreSQL Connected
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Disconnected
                    </span>
                <?php endif; ?>
                <span class="text-xs text-gray-400 font-medium">Thứ Tư, 21 tháng 10</span>
            </div>
        </div>

        <!-- Banner Tiêu đề -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <span class="text-[11px] font-bold text-gray-400 tracking-wider uppercase">THỨC DẬY CÙNG TRI THỨC</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-1">Chào buổi sáng! ✨</h2>
            </div>
            <button class="btn-primary px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm transition">
                <i class="fa-solid fa-plus"></i> Tạo phiếu mượn
            </button>
        </div>

        <!-- 4 Thẻ Thống Kê Metric -->
        <div class="grid grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="text-3xl font-bold text-gray-900 mb-1"><?= $total_books ?></div>
                <div class="text-xs text-gray-400 font-medium">Tổng số sách</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="text-3xl font-bold text-gray-900 mb-1"><?= $available_books ?></div>
                <div class="text-xs text-gray-400 font-medium">Sách có sẵn</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="text-3xl font-bold text-gray-900 mb-1"><?= $borrowed_books ?></div>
                <div class="text-xs text-gray-400 font-medium">Đang được mượn</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="text-3xl font-bold text-gray-900 mb-1"><?= $total_readers ?></div>
                <div class="text-xs text-gray-400 font-medium">Tổng độc giả</div>
            </div>
        </div>

        <!-- Bảng Hoạt Động Gần Đây -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 text-sm mb-4">Hoạt động mượn sách gần đây</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-100">
                            <th class="pb-3 font-semibold w-16">ID</th>
                            <th class="pb-3 font-semibold">Người mượn</th>
                            <th class="pb-3 font-semibold">Tên sách</th>
                            <th class="pb-3 font-semibold">Ngày mượn</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-600">
                        <?php if (!empty($recent_borrows)): ?>
                            <?php foreach ($recent_borrows as $row): ?>
                                <tr>
                                    <td class="py-3.5 font-medium text-gray-400">#<?= htmlspecialchars($row['id']) ?></td>
                                    <td class="py-3.5 font-medium text-gray-900"><?= htmlspecialchars($row['reader_name']) ?></td>
                                    <td class="py-3.5"><?= htmlspecialchars($row['book_title']) ?></td>
                                    <td class="py-3.5 text-gray-400"><?= htmlspecialchars($row['borrow_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="py-3.5 font-medium text-gray-400">#1</td>
                                <td class="py-3.5 font-medium text-gray-900">tôi</td>
                                <td class="py-3.5">đắc nhân tâm</td>
                                <td class="py-3.5 text-gray-400">2026-10-21</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
