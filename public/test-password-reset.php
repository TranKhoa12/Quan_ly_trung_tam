<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Quên Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Test Chức Năng Quên Mật Khẩu</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5>Hướng dẫn test:</h5>
                            <ol>
                                <li><a href="/Quan_ly_trung_tam/public/login" target="_blank">Mở trang đăng nhập</a></li>
                                <li>Nhấp vào link "Quên mật khẩu?"</li>
                                <li>Nhập username hoặc email: <code>admin</code> hoặc <code>admin@example.com</code></li>
                                <li>Nhấn "Gửi yêu cầu đặt lại mật khẩu"</li>
                                <li>Sao chép link reset password hiển thị</li>
                                <li>Mở link đó để đặt lại mật khẩu</li>
                            </ol>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Tài khoản test:</h5>
                                <ul>
                                    <li><strong>Admin:</strong> admin / admin@example.com</li>
                                    <li><strong>Staff:</strong> staff1 / staff1@example.com</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Các link test:</h5>
                                <ul>
                                    <li><a href="/Quan_ly_trung_tam/public/login" target="_blank">Trang đăng nhập</a></li>
                                    <li><a href="/Quan_ly_trung_tam/public/forgot-password" target="_blank">Quên mật khẩu</a></li>
                                    <li><a href="/Quan_ly_trung_tam/public/dashboard" target="_blank">Dashboard</a></li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <h5>Kiểm tra database:</h5>
                        <?php
                        // Database connection
                        try {
                            $pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            // Check users table
                            echo "<h6>Users:</h6>";
                            $stmt = $pdo->query("SELECT id, username, full_name, email, role FROM users");
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            echo "<table class='table table-sm'>";
                            echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th></tr>";
                            foreach ($users as $user) {
                                echo "<tr>";
                                echo "<td>{$user['id']}</td>";
                                echo "<td>{$user['username']}</td>";
                                echo "<td>{$user['full_name']}</td>";
                                echo "<td>{$user['email']}</td>";
                                echo "<td>{$user['role']}</td>";
                                echo "</tr>";
                            }
                            echo "</table>";

                            // Check password reset tokens
                            echo "<h6>Password Reset Tokens:</h6>";
                            $stmt = $pdo->query("SELECT prt.*, u.username FROM password_reset_tokens prt LEFT JOIN users u ON prt.user_id = u.id ORDER BY prt.created_at DESC LIMIT 10");
                            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($tokens) > 0) {
                                echo "<table class='table table-sm'>";
                                echo "<tr><th>ID</th><th>User</th><th>Token</th><th>Expires</th><th>Used</th><th>Created</th></tr>";
                                foreach ($tokens as $token) {
                                    echo "<tr>";
                                    echo "<td>{$token['id']}</td>";
                                    echo "<td>{$token['username']}</td>";
                                    echo "<td>" . substr($token['token'], 0, 20) . "...</td>";
                                    echo "<td>{$token['expires_at']}</td>";
                                    echo "<td>" . ($token['used'] ? 'Yes' : 'No') . "</td>";
                                    echo "<td>{$token['created_at']}</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "<p>Chưa có token nào được tạo.</p>";
                            }

                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>Lỗi database: " . $e->getMessage() . "</div>";
                        }
                        ?>

                        <div class="mt-3">
                            <button onclick="location.reload()" class="btn btn-secondary">Refresh</button>
                            <a href="/Quan_ly_trung_tam/public/login" class="btn btn-primary">Đến trang đăng nhập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>