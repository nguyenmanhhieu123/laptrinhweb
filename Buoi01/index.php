<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giới thiệu bản thân</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .project-item { background: #f4f4f4; padding: 10px; margin-bottom: 10px; border-left: 5px solid #3498db; }
        .info { font-weight: bold; color: #e67e22; }
    </style>
</head>
<body>

    <h1>Chào mừng bạn đến với trang của tôi!</h1>

    <section>
        <h2>Thông tin cá nhân</h2>
        <p>Họ và tên: <span class="info">Nguyễn Mạnh Hiếu</span></p>
        <p>Mã sinh viên: <span class="info">224001788</span></p>
        <p>Lớp: <span class="info">Lập trình web_CNTT2024_N1</span></p>
        <p>Email: <span class="info">hieusus30102006@gmail.com</span></p>
    </section>

    <section>
        <h2>Các dự án đã thực hiện</h2>
        
        <div class="project-item">
            <h3>1. Dự án Lập trình Web (HTML/CSS/JS)</h3>
            <p>Mô tả: Xây dựng giao diện website đặt vé du lịch tour đà lạt.</p>
            <p>Công nghệ sử dụng: HTML5, CSS3, JavaScript.</p>
        </div>

    </section>

    <footer>
        <p>Ngày hôm nay là: <?php echo date("d/m/Y"); ?></p>
    </footer>

</body>
</html>