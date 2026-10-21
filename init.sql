-- 1. Tạo bảng Thể loại sách
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- 2. Tạo bảng Sách
CREATE TABLE IF NOT EXISTS books (
    id SERIAL PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category_id INT REFERENCES categories(id) ON DELETE SET NULL,
    publish_year INT,
    total_quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tạo bảng Độc giả
CREATE TABLE IF NOT EXISTS readers (
    id SERIAL PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    fullname VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tạo bảng Mượn / Trả sách
CREATE TABLE IF NOT EXISTS loans (
    id SERIAL PRIMARY KEY,
    reader_id INT REFERENCES readers(id) ON DELETE CASCADE,
    book_id INT REFERENCES books(id) ON DELETE CASCADE,
    borrow_date DATE DEFAULT CURRENT_DATE,
    due_date DATE DEFAULT (CURRENT_DATE + INTERVAL '14 days'),
    return_date DATE,
    status VARCHAR(20) DEFAULT 'BORROWED' -- BORROWED, RETURNED, OVERDUE
);

-- ==========================================
-- CHÈN DỮ LIỆU MẪU (SAMPLE DATA)
-- ==========================================

-- Chèn Thể loại
INSERT INTO categories (category_name, description) VALUES
('Công nghệ thông tin', 'Sách về Lập trình, Cơ sở dữ liệu, Mạng máy tính và Điện toán đám mây'),
('Điện tử - Viễn thông', 'Sách về Mạch điện tử, IoT và Hệ thống nhúng'),
('Kinh tế & Quản trị', 'Sách Kế toán, Quản trị kinh doanh và Marketing'),
('Văn học & Nghệ thuật', 'Tiểu thuyết, Thơ ca và Lịch sử văn hóa');

-- Chèn Sách
INSERT INTO books (isbn, title, author, category_id, publish_year, total_quantity, available_quantity) VALUES
('978-0131103627', 'Lập trình C cơ bản và nâng cao', 'Brian W. Kernighan', 1, 2021, 10, 8),
('978-0596007126', 'Thiết kế Hệ thống Cơ sở dữ liệu PostgreSQL', 'Michael Stonebraker', 1, 2022, 5, 3),
('978-0132350884', 'Clean Code - Mã sạch và Nghệ thuật tái cấu trúc', 'Robert C. Martin', 1, 2020, 8, 5),
('978-0262033848', 'Nhập môn Giải thuật và Cấu trúc dữ liệu', 'Thomas H. Cormen', 1, 2019, 12, 10),
('978-1119454045', 'Quản trị Mạng máy tính & An toàn thông tin', 'Andrew S. Tanenbaum', 1, 2023, 7, 7),
('978-6047721893', 'Đắc Nhân Tâm', 'Dale Carnegie', 4, 2018, 15, 12);

-- Chèn Độc giả (Sinh viên)
INSERT INTO readers (student_id, fullname, email, phone) VALUES
('DTC245160008', 'Nguyễn Ngọc Lan', 'dtc245160008@ictu.edu.vn', '0987654321'),
('DTC245160001', 'Trần Văn Minh', 'tvminh@ictu.edu.vn', '0912345678'),
('DTC245160002', 'Lê Thị An', 'ltan@ictu.edu.vn', '0934567890'),
('DTC245160003', 'Phạm Quốc Hùng', 'pqhung@ictu.edu.vn', '0978123456');

-- Chèn Phiếu mượn mẫu
INSERT INTO loans (reader_id, book_id, borrow_date, due_date, status) VALUES
(1, 1, CURRENT_DATE - INTERVAL '3 days', CURRENT_DATE + INTERVAL '11 days', 'BORROWED'),
(1, 3, CURRENT_DATE - INTERVAL '5 days', CURRENT_DATE + INTERVAL '9 days', 'BORROWED'),
(2, 2, CURRENT_DATE - INTERVAL '10 days', CURRENT_DATE + INTERVAL '4 days', 'BORROWED'),
(3, 4, CURRENT_DATE - INTERVAL '20 days', CURRENT_DATE - INTERVAL '6 days', 'OVERDUE');
