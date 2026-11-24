SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS qly_ch_quan_ao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qly_ch_quan_ao;

CREATE TABLE Users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'staff'
);
CREATE TABLE Products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(100) NOT NULL,
  category VARCHAR(50),
  price DECIMAL(12,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE Orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(12,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'completed',
  FOREIGN KEY (user_id) REFERENCES Users(id)
);
CREATE TABLE OrderDetails (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES Orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES Products(id)
);

-- Tài khoản mẫu: admin / admin123 (quản lý), nhanvien1 / 123456 (nhân viên)
INSERT INTO Users (username,password,full_name,role) VALUES
('admin','$2y$12$ifAsegdE0x8qUmJBqTqPbem5wWzCdIwRcJORPiC7f/cBo5dnfQMNq','Quản lý cửa hàng','admin'),
('nhanvien1','$2y$12$TYQmLab2jiwY3e1FTqaX2OWaoSDXCWxfqlCWTZvQ5oOIhRW.COwja','Nguyễn Văn An','staff');

INSERT INTO Products (product_name,category,price,quantity,description) VALUES
('Áo thun basic trắng','Áo',150000,50,'Cotton 100%, form regular'),
('Áo sơ mi Oxford xanh','Áo',320000,30,'Sơ mi dài tay'),
('Áo hoodie nỉ xám','Áo',450000,20,'Nỉ bông dày'),
('Quần jean slim đen','Quần',480000,25,'Jean co giãn'),
('Quần kaki be','Quần',350000,35,'Kaki ống đứng'),
('Quần short thể thao','Quần',180000,40,'Vải thun lạnh'),
('Váy midi hoa nhí','Váy',390000,15,'Váy xòe dài qua gối'),
('Áo khoác dù đen','Áo khoác',520000,12,'Chống nước nhẹ'),
('Nón lưỡi trai','Phụ kiện',90000,60,'Freesize'),
('Thắt lưng da nâu','Phụ kiện',250000,18,'Da bò thật');
