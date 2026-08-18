<?php
/**
 * form.php - Màn hình "Báo hỏng thiết bị" + "Lịch sử bảo trì"
 * Chỉ hiển thị giao diện (form nhập + bảng lịch sử). Xử lý dữ liệu nằm ở store.php
 */
session_start();

// ===== KẾT NỐI DB (tự tạo database/bảng nếu chưa có) =====
$host = 'localhost';
$ten_db = 'qlpttb_buoi2';
$user = 'root';
$mk = ''; // XAMPP mặc định để rỗng

try {
    $pdoGoc = new PDO("mysql:host={$host};charset=utf8mb4", $user, $mk, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdoGoc->exec("CREATE DATABASE IF NOT EXISTS {$ten_db} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo = new PDO("mysql:host={$host};dbname={$ten_db};charset=utf8mb4", $user, $mk, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS bao_hong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ma_thiet_bi VARCHAR(20) NOT NULL,
        ten_thiet_bi VARCHAR(150) NOT NULL,
        nguoi_bao_hong VARCHAR(100) NOT NULL,
        mo_ta_loi TEXT NOT NULL,
        muc_do_uu_tien ENUM('Cao', 'Trung bình', 'Thấp') NOT NULL,
        han_xu_ly VARCHAR(50) NOT NULL,
        trang_thai VARCHAR(100) NOT NULL,
        ngay_bao_hong DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $soDong = $pdo->query("SELECT COUNT(*) AS tong FROM bao_hong")->fetch()['tong'];
    if ($soDong == 0) {
        $pdo->exec("INSERT INTO bao_hong
            (ma_thiet_bi, ten_thiet_bi, nguoi_bao_hong, mo_ta_loi, muc_do_uu_tien, han_xu_ly, trang_thai)
            VALUES
            ('TB001', 'Máy chiếu Epson EB-X05', 'Nguyễn Văn A', 'Máy chiếu không lên hình, đèn báo đỏ nhấp nháy', 'Cao', 'Trong 24 giờ', 'Khẩn cấp - Ngừng cho mượn'),
            ('TB002', 'Laptop Dell Vostro 15', 'Trần Thị B', 'Pin laptop chai, không giữ được nguồn quá 10 phút', 'Trung bình', 'Trong 3 ngày', 'Chờ bảo trì - Ngừng cho mượn'),
            ('TB003', 'Bàn phím cơ Logitech', 'Lê Văn C', 'Một vài phím bị liệt, gõ không ăn', 'Thấp', 'Trong 7 ngày', 'Theo dõi - Vẫn có thể cân nhắc')");
    }
} catch (PDOException $e) {
    die('Lỗi kết nối cơ sở dữ liệu. Hãy chắc chắn MySQL trong XAMPP đang chạy. Chi tiết: ' . $e->getMessage());
}

// ===== HÀM DÙNG ĐỂ HIỂN THỊ =====

/** Quy tắc: thiết bị hỏng/đang bảo trì thì KHÔNG được cho mượn. */
function khongDuocChoMuon(string $trangThai): bool
{
    return (strpos($trangThai, 'Ngừng cho mượn') !== false);
}

/** Lấy MẢNG toàn bộ phiếu báo hỏng, mới nhất lên trước. */
function layDanhSachBaoHong(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM bao_hong ORDER BY ngay_bao_hong DESC, id DESC")->fetchAll();
}

// ===== LẤY THÔNG BÁO/LỖI TỪ store.php (nếu vừa redirect về) =====
$loiValidate = $_SESSION['loi_validate'] ?? [];
$duLieuCu = $_SESSION['du_lieu_cu'] ?? [];
$thongBaoThanhCong = $_SESSION['thong_bao_thanh_cong'] ?? '';
unset($_SESSION['loi_validate'], $_SESSION['du_lieu_cu'], $_SESSION['thong_bao_thanh_cong']);

// ===== DỮ LIỆU HIỂN THỊ BẢNG LỊCH SỬ =====
$danhSachBaoHong = layDanhSachBaoHong($pdo);

$soLuongTheoMuc = ['Cao' => 0, 'Trung bình' => 0, 'Thấp' => 0];
foreach ($danhSachBaoHong as $phieu) {
    if (isset($soLuongTheoMuc[$phieu['muc_do_uu_tien']])) {
        $soLuongTheoMuc[$phieu['muc_do_uu_tien']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Báo hỏng thiết bị</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f4f6f9; color: #1f2937; }
.header { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; padding: 28px 20px; text-align: center; }
.header h1 { margin: 0 0 6px; font-size: 26px; }
.subtitle { margin: 0; opacity: 0.9; font-size: 14px; }
.container { max-width: 980px; margin: 30px auto; padding: 0 16px 60px; display: flex; flex-direction: column; gap: 24px; }
.card { background: #fff; border-radius: 10px; padding: 22px 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
.card h2 { margin-top: 0; font-size: 19px; border-left: 4px solid #2563eb; padding-left: 10px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 14px; }
label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
.required { color: #dc2626; }
input[type="text"], select, textarea { width: 100%; padding: 9px 11px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; font-family: inherit; }
input:focus, select:focus, textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
.btn-submit { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: #1d4ed8; }
.thong-bao { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
.thong-bao ul { margin: 6px 0 0 18px; padding: 0; }
.thong-bao-loi { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.thong-bao-thanh-cong { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.thong-ke { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.badge { padding: 6px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; }
.badge-cao { background: #fee2e2; color: #b91c1c; }
.badge-tb { background: #fef3c7; color: #92400e; }
.badge-thap { background: #dcfce7; color: #166534; }
.badge-tong { background: #e0e7ff; color: #3730a3; }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
th, td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
th { background: #f9fafb; font-weight: 700; white-space: nowrap; }
tbody tr:hover { background: #f9fafb; }
.tag { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.tag-cao { background: #fee2e2; color: #b91c1c; }
.tag-tb { background: #fef3c7; color: #92400e; }
.tag-thap { background: #dcfce7; color: #166534; }
.tag-khong-cho-muon { background: #fee2e2; color: #b91c1c; }
.tag-cho-muon { background: #dcfce7; color: #166534; }
.rong { color: #6b7280; font-style: italic; }
@media (max-width: 640px) { .grid-2 { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<header class="header">
    <h1>🛠️ Báo hỏng thiết bị</h1>
    <p class="subtitle">Hệ thống quản lý phòng thực hành và thiết bị &middot; Người 4: Báo hỏng &amp; Bảo trì</p>
</header>

<main class="container">

    <section class="card">
        <h2>Phiếu báo hỏng thiết bị</h2>

        <?php if (!empty($loiValidate)): ?>
            <div class="thong-bao thong-bao-loi">
                <strong>Vui lòng kiểm tra lại:</strong>
                <ul>
                    <?php foreach ($loiValidate as $loi): ?>
                        <li><?= htmlspecialchars($loi) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($thongBaoThanhCong): ?>
            <div class="thong-bao thong-bao-thanh-cong"><?= htmlspecialchars($thongBaoThanhCong) ?></div>
        <?php endif; ?>

        <form method="POST" action="store.php" id="form-bao-hong" novalidate>
            <div class="grid-2">
                <div class="form-group">
                    <label for="ma_thiet_bi">Mã thiết bị <span class="required">*</span></label>
                    <input type="text" id="ma_thiet_bi" name="ma_thiet_bi"
                           value="<?= htmlspecialchars($duLieuCu['ma_thiet_bi'] ?? '') ?>" placeholder="VD: TB004">
                </div>
                <div class="form-group">
                    <label for="ten_thiet_bi">Tên thiết bị <span class="required">*</span></label>
                    <input type="text" id="ten_thiet_bi" name="ten_thiet_bi"
                           value="<?= htmlspecialchars($duLieuCu['ten_thiet_bi'] ?? '') ?>" placeholder="VD: Máy chiếu Epson EB-X05">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="nguoi_bao_hong">Người báo hỏng <span class="required">*</span></label>
                    <input type="text" id="nguoi_bao_hong" name="nguoi_bao_hong"
                           value="<?= htmlspecialchars($duLieuCu['nguoi_bao_hong'] ?? '') ?>" placeholder="Họ tên sinh viên/cán bộ">
                </div>
                <div class="form-group">
                    <label for="muc_do_uu_tien">Mức độ ưu tiên <span class="required">*</span></label>
                    <select id="muc_do_uu_tien" name="muc_do_uu_tien">
                        <option value="">-- Chọn mức độ --</option>
                        <?php foreach (['Cao', 'Trung bình', 'Thấp'] as $muc): ?>
                            <option value="<?= $muc ?>" <?= (($duLieuCu['muc_do_uu_tien'] ?? '') === $muc) ? 'selected' : '' ?>><?= $muc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="mo_ta_loi">Mô tả lỗi <span class="required">*</span></label>
                <textarea id="mo_ta_loi" name="mo_ta_loi" rows="3"
                          placeholder="Mô tả chi tiết hiện tượng hỏng hóc..."><?= htmlspecialchars($duLieuCu['mo_ta_loi'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Gửi báo hỏng</button>
        </form>
    </section>

    <section class="card">
        <h2>Lịch sử báo hỏng / bảo trì</h2>

        <div class="thong-ke">
            <span class="badge badge-cao">Cao: <?= $soLuongTheoMuc['Cao'] ?></span>
            <span class="badge badge-tb">Trung bình: <?= $soLuongTheoMuc['Trung bình'] ?></span>
            <span class="badge badge-thap">Thấp: <?= $soLuongTheoMuc['Thấp'] ?></span>
            <span class="badge badge-tong">Tổng: <?= count($danhSachBaoHong) ?></span>
        </div>

        <?php if (empty($danhSachBaoHong)): ?>
            <p class="rong">Chưa có phiếu báo hỏng nào.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Mã TB</th><th>Tên thiết bị</th><th>Người báo hỏng</th>
                    <th>Mô tả lỗi</th><th>Ưu tiên</th><th>Hạn xử lý</th><th>Trạng thái</th>
                    <th>Cho mượn?</th><th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 1; ?>
                <?php foreach ($danhSachBaoHong as $phieu): ?>
                    <?php $khongChoMuon = khongDuocChoMuon($phieu['trang_thai']); ?>
                    <tr>
                        <td><?= $stt++ ?></td>
                        <td><?= htmlspecialchars($phieu['ma_thiet_bi']) ?></td>
                        <td><?= htmlspecialchars($phieu['ten_thiet_bi']) ?></td>
                        <td><?= htmlspecialchars($phieu['nguoi_bao_hong']) ?></td>
                        <td><?= htmlspecialchars($phieu['mo_ta_loi']) ?></td>
                        <td>
                            <span class="tag tag-<?= $phieu['muc_do_uu_tien'] === 'Cao' ? 'cao' : ($phieu['muc_do_uu_tien'] === 'Trung bình' ? 'tb' : 'thap') ?>">
                                <?= htmlspecialchars($phieu['muc_do_uu_tien']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($phieu['han_xu_ly']) ?></td>
                        <td><?= htmlspecialchars($phieu['trang_thai']) ?></td>
                        <td>
                            <?php if ($khongChoMuon): ?>
                                <span class="tag tag-khong-cho-muon">Không</span>
                            <?php else: ?>
                                <span class="tag tag-cho-muon">Có</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($phieu['ngay_bao_hong']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </section>

</main>

<script>
document.getElementById('form-bao-hong').addEventListener('submit', function (e) {
    var loi = [];
    var maThietBi = document.getElementById('ma_thiet_bi').value.trim();
    var tenThietBi = document.getElementById('ten_thiet_bi').value.trim();
    var nguoiBaoHong = document.getElementById('nguoi_bao_hong').value.trim();
    var mucDoUuTien = document.getElementById('muc_do_uu_tien').value;
    var moTaLoi = document.getElementById('mo_ta_loi').value.trim();

    if (maThietBi === '') loi.push('Mã thiết bị không được để trống.');
    if (tenThietBi === '') loi.push('Tên thiết bị không được để trống.');
    if (nguoiBaoHong === '') loi.push('Người báo hỏng không được để trống.');
    if (mucDoUuTien === '') loi.push('Vui lòng chọn mức độ ưu tiên.');
    if (moTaLoi === '') loi.push('Vui lòng mô tả lỗi thiết bị.');

    if (loi.length > 0) {
        e.preventDefault();
        alert('Vui lòng kiểm tra lại:\n- ' + loi.join('\n- '));
    }
});
</script>
</body>
</html>