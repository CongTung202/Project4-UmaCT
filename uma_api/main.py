# umact_api/main.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pymysql
from datetime import datetime
from typing import Optional
import cloudinary
import cloudinary.uploader
from fastapi import UploadFile, File
import json
import unicodedata

app = FastAPI(title="UmaCT API")

# Cấu hình Cloudinary (Thay bằng thông tin của bạn)
cloudinary.config( 
  cloud_name = "dhefmthim", 
  api_key = "614126996368587", 
  api_secret = "iONJY3A_CCj9q6bfKPCpDrzPZtQ",
  secure = True
)
# Hàm kết nối Database
def get_db_connection():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='123456',
        database='umact_db',
        cursorclass=pymysql.cursors.DictCursor # Trả về dạng Dictionary (JSON)
    )

# Model mô tả dữ liệu đầu vào khi thêm Danh mục
class CategoryCreate(BaseModel):
    name: str
    slug: str

# 1. API: Lấy danh sách danh mục (Read)
@app.get("/api/categories")
def get_categories():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM categories ORDER BY id DESC")
    categories = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": categories}

# 2. API: Thêm danh mục mới (Create)
@app.post("/api/categories")
def create_category(category: CategoryCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "INSERT INTO categories (name, slug) VALUES (%s, %s)"
        cursor.execute(sql, (category.name, category.slug))
        conn.commit()
        return {"status": "success", "message": "Thêm thành công!"}
    except pymysql.IntegrityError:
        raise HTTPException(status_code=400, detail="Slug đã tồn tại!")
    finally:
        conn.close()
        # 3. API: Lấy thông tin 1 danh mục theo ID (Để đổ dữ liệu cũ vào form Edit)
@app.get("/api/categories/{category_id}")
def get_category(category_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM categories WHERE id = %s", (category_id,))
    category = cursor.fetchone()
    conn.close()
    if not category:
        raise HTTPException(status_code=404, detail="Không tìm thấy danh mục")
    return {"status": "success", "data": category}

# 4. API: Cập nhật danh mục (Update - PUT)
@app.put("/api/categories/{category_id}")
def update_category(category_id: int, category: CategoryCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "UPDATE categories SET name = %s, slug = %s WHERE id = %s"
        cursor.execute(sql, (category.name, category.slug, category_id))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không có gì thay đổi hoặc không tìm thấy danh mục")
        return {"status": "success", "message": "Cập nhật thành công!"}
    except pymysql.IntegrityError:
        raise HTTPException(status_code=400, detail="Slug đã tồn tại cho danh mục khác!")
    finally:
        conn.close()

# 5. API: Xóa danh mục (Delete)
@app.delete("/api/categories/{category_id}")
def delete_category(category_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "DELETE FROM categories WHERE id = %s"
        cursor.execute(sql, (category_id,))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không tìm thấy danh mục để xóa")
        return {"status": "success", "message": "Xóa thành công!"}
    except pymysql.IntegrityError:
        # Bắt lỗi Khóa ngoại: Nếu danh mục đang chứa sản phẩm thì CSDL sẽ không cho xóa
        raise HTTPException(status_code=400, detail="Không thể xóa! Danh mục này đang chứa sản phẩm.")
    finally:
        conn.close()
        # Model mô tả dữ liệu đầu vào cho Sản phẩm
class ProductCreate(BaseModel):
    category_id: int
    supplier_id: int
    name: str
    description: str = ""
    price: float
    stock_quantity: int
    is_active: bool = True
    images: str = "[]"

# 6. API: Lấy danh sách sản phẩm (Có JOIN để lấy tên danh mục và nhà cung cấp)
@app.get("/api/products")
def get_products():
    conn = get_db_connection()
    cursor = conn.cursor()
    sql = """
        SELECT p.*, c.name as category_name, s.name as supplier_name,
               (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id LIMIT 1) as main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.id DESC
    """
    cursor.execute(sql)
    products = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": products}

# 7. API: Thêm sản phẩm mới
@app.post("/api/products")
def create_product(product: ProductCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # BƯỚC 1: Lưu thông tin vào bảng products (KHÔNG có cột images)
        sql_product = """
            INSERT INTO products (category_id, supplier_id, name, description, price, stock_quantity, is_active) 
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(sql_product, (
            product.category_id, product.supplier_id, product.name, 
            product.description, product.price, product.stock_quantity, product.is_active
        ))
        
        # Lấy ID của sản phẩm vừa được tạo
        product_id = cursor.lastrowid
        
        # BƯỚC 2: Lưu danh sách ảnh vào bảng product_images
        image_urls = json.loads(product.images) # Biến chuỗi JSON thành List trong Python
        if image_urls:
            sql_images = "INSERT INTO product_images (product_id, image_url) VALUES (%s, %s)"
            # Tạo danh sách các tuple dữ liệu: [(1, 'link1'), (1, 'link2')]
            img_data = [(product_id, url) for url in image_urls]
            cursor.executemany(sql_images, img_data) # Insert nhiều dòng cùng lúc

        conn.commit()
        return {"status": "success", "message": "Thêm sản phẩm thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Lỗi: {str(e)}")
    finally:
        conn.close()
def remove_accents(input_str):
    if not input_str: return ""
    nfkd_form = unicodedata.normalize('NFKD', input_str)
    return u"".join([c for c in nfkd_form if not unicodedata.combining(c)]).lower()

# 43. API: Tìm kiếm sản phẩm thông minh (Live Search)
@app.get("/api/products/search")
def search_products(q: str = ""):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # SỬA LỖI: Dùng Subquery lồng bảng product_images để lấy ảnh đầu tiên
        sql = """
            SELECT p.id, p.name, p.price, 
                   (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id LIMIT 1) as main_image 
            FROM products p 
            WHERE p.is_active = 1
        """
        cursor.execute(sql)
        products = cursor.fetchall()

        if not q.strip():
            return {"status": "success", "data": []}

        normalized_q = remove_accents(q)
        results = []
        
        for p in products:
            normalized_name = remove_accents(p['name'])
            if normalized_q in normalized_name:
                # Không cần gỡ rối JSON nữa vì SQL đã trả về sẵn cột main_image
                results.append(p)

        return {"status": "success", "data": results[:6]}
    finally:
        conn.close()
# 8. API: Lấy chi tiết 1 sản phẩm theo ID
@app.get("/api/products/{product_id}")
def get_product(product_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # 1. Lấy thông tin chung của sản phẩm
    cursor.execute("SELECT * FROM products WHERE id = %s", (product_id,))
    product = cursor.fetchone()
    
    if not product:
        conn.close()
        raise HTTPException(status_code=404, detail="Không tìm thấy sản phẩm")
        
    # 2. Lấy danh sách ảnh từ bảng product_images
    cursor.execute("SELECT image_url FROM product_images WHERE product_id = %s", (product_id,))
    images = cursor.fetchall()
    
    # Ép mảng ảnh thành chuỗi JSON giống hệt cách PHP gửi lên để file edit.php của bạn đọc được luôn
    image_list = [img['image_url'] for img in images]
    product['images'] = json.dumps(image_list) 
    
    conn.close()
    return {"status": "success", "data": product}

# 9. API: Cập nhật sản phẩm (PUT)
@app.put("/api/products/{product_id}")
def update_product(product_id: int, product: ProductCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # BƯỚC 1: Cập nhật bảng products
        sql_product = """
            UPDATE products 
            SET category_id = %s, supplier_id = %s, name = %s, 
                description = %s, price = %s, stock_quantity = %s, is_active = %s
            WHERE id = %s
        """
        cursor.execute(sql_product, (
            product.category_id, product.supplier_id, product.name, 
            product.description, product.price, product.stock_quantity, product.is_active, product_id
        ))
        
        # BƯỚC 2: Cập nhật bảng product_images (Xóa hết ảnh cũ, thêm lại ảnh mới cho dễ)
        cursor.execute("DELETE FROM product_images WHERE product_id = %s", (product_id,))
        
        image_urls = json.loads(product.images)
        if image_urls:
            sql_images = "INSERT INTO product_images (product_id, image_url) VALUES (%s, %s)"
            img_data = [(product_id, url) for url in image_urls]
            cursor.executemany(sql_images, img_data)

        conn.commit()
        return {"status": "success", "message": "Cập nhật thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Lỗi: {str(e)}")
    finally:
        conn.close()

# 10. API: Xóa sản phẩm (DELETE)
@app.delete("/api/products/{product_id}")
def delete_product(product_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "DELETE FROM products WHERE id = %s"
        cursor.execute(sql, (product_id,))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không tìm thấy sản phẩm để xóa")
        return {"status": "success", "message": "Xóa thành công!"}
    except pymysql.IntegrityError:
        raise HTTPException(status_code=400, detail="Không thể xóa! Sản phẩm này đang nằm trong đơn hàng của khách.")
    finally:
        conn.close()
# Model mô tả dữ liệu đầu vào cho Voucher
class VoucherCreate(BaseModel):
    code: str
    discount_amount: float
    min_order_value: Optional[float] = 0
    usage_limit: Optional[int] = 0
    expiration_date: Optional[datetime] = None

# 11. API: Lấy danh sách Voucher
@app.get("/api/vouchers")
def get_vouchers():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM vouchers ORDER BY id DESC")
    vouchers = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": vouchers}

# 12. API: Thêm Voucher mới
@app.post("/api/vouchers")
def create_voucher(voucher: VoucherCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = """
            INSERT INTO vouchers (code, discount_amount, min_order_value, usage_limit, expiration_date) 
            VALUES (%s, %s, %s, %s, %s)
        """
        # Nếu expiration_date được gửi lên, Python sẽ tự hiểu định dạng
        cursor.execute(sql, (
            voucher.code, voucher.discount_amount, voucher.min_order_value, 
            voucher.usage_limit, voucher.expiration_date
        ))
        conn.commit()
        return {"status": "success", "message": "Thêm mã giảm giá thành công!"}
    except pymysql.IntegrityError:
        raise HTTPException(status_code=400, detail="Mã giảm giá (Code) này đã tồn tại!")
    finally:
        conn.close()
# 13. API: Lấy chi tiết 1 Voucher theo ID (Để đổ vào form Sửa)
@app.get("/api/vouchers/{voucher_id}")
def get_voucher(voucher_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM vouchers WHERE id = %s", (voucher_id,))
    voucher = cursor.fetchone()
    conn.close()
    if not voucher:
        raise HTTPException(status_code=404, detail="Không tìm thấy mã giảm giá")
    return {"status": "success", "data": voucher}

# 14. API: Cập nhật Voucher (PUT)
@app.put("/api/vouchers/{voucher_id}")
def update_voucher(voucher_id: int, voucher: VoucherCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = """
            UPDATE vouchers 
            SET code = %s, discount_amount = %s, min_order_value = %s, 
                usage_limit = %s, expiration_date = %s
            WHERE id = %s
        """
        cursor.execute(sql, (
            voucher.code, voucher.discount_amount, voucher.min_order_value, 
            voucher.usage_limit, voucher.expiration_date, voucher_id
        ))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không có gì thay đổi hoặc không tìm thấy mã giảm giá")
        return {"status": "success", "message": "Cập nhật thành công!"}
    except pymysql.IntegrityError:
        raise HTTPException(status_code=400, detail="Mã giảm giá (Code) bị trùng lặp!")
    finally:
        conn.close()

# 15. API: Xóa Voucher (DELETE)
@app.delete("/api/vouchers/{voucher_id}")
def delete_voucher(voucher_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "DELETE FROM vouchers WHERE id = %s"
        cursor.execute(sql, (voucher_id,))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không tìm thấy mã giảm giá để xóa")
        return {"status": "success", "message": "Xóa thành công!"}
    except pymysql.IntegrityError:
        # Bắt lỗi nếu mã này đã được khách hàng sử dụng trong hóa đơn
        raise HTTPException(status_code=400, detail="Không thể xóa! Mã này đã được sử dụng trong hệ thống.")
    finally:
        conn.close()
# Model mô tả dữ liệu khi Admin cập nhật trạng thái đơn hàng
class OrderStatusUpdate(BaseModel):
    status: str

# 16. API: Lấy danh sách tất cả đơn hàng
@app.get("/api/orders")
def get_orders():
    conn = get_db_connection()
    cursor = conn.cursor()
    # Lấy thông tin đơn hàng kèm tên người mua
    sql = """
        SELECT o.*, u.full_name, u.username 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    """
    cursor.execute(sql)
    orders = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": orders}

# 17. API: Lấy chi tiết 1 đơn hàng (Bao gồm thông tin chung và danh sách sản phẩm)
@app.get("/api/orders/{order_id}")
def get_order_detail(order_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # 1. Lấy thông tin chung của đơn hàng
    sql_order = """
        SELECT o.*, u.full_name, u.email, u.phone 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = %s
    """
    cursor.execute(sql_order, (order_id,))
    order_info = cursor.fetchone()
    
    if not order_info:
        conn.close()
        raise HTTPException(status_code=404, detail="Không tìm thấy đơn hàng")
        
    # 2. Lấy danh sách sản phẩm trong đơn hàng đó (order_items)
    sql_items = """
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = %s
    """
    cursor.execute(sql_items, (order_id,))
    items = cursor.fetchall()
    conn.close()
    
    # Gộp chung vào 1 response
    return {
        "status": "success", 
        "data": {
            "order_info": order_info,
            "items": items
        }
    }

# 18. API: Cập nhật trạng thái đơn hàng
@app.put("/api/orders/{order_id}/status")
def update_order_status(order_id: int, status_update: OrderStatusUpdate):
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # Kiểm tra trạng thái hợp lệ
    valid_statuses = ['PENDING', 'PAID', 'SHIPPING', 'COMPLETED', 'CANCELLED']
    if status_update.status not in valid_statuses:
         raise HTTPException(status_code=400, detail="Trạng thái không hợp lệ")
         
    try:
        sql = "UPDATE orders SET status = %s WHERE id = %s"
        cursor.execute(sql, (status_update.status, order_id))
        conn.commit()
        return {"status": "success", "message": "Cập nhật trạng thái thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
# 19. API: Xóa đơn hàng (DELETE)
@app.delete("/api/orders/{order_id}")
def delete_order(order_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # 1. Xóa lịch sử dùng voucher của đơn hàng này (nếu có) để tránh lỗi Khóa ngoại
        cursor.execute("DELETE FROM user_voucher_usage WHERE order_id = %s", (order_id,))
        
        # 2. Xóa đơn hàng (Bảng order_items sẽ tự động xóa theo nhờ ON DELETE CASCADE của bạn)
        sql = "DELETE FROM orders WHERE id = %s"
        cursor.execute(sql, (order_id,))
        conn.commit()
        
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không tìm thấy đơn hàng để xóa")
            
        return {"status": "success", "message": "Xóa đơn hàng thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
# Model đầu vào cho Cập nhật trạng thái và Phân quyền
class UserStatusUpdate(BaseModel):
    status: str

class UserRoleUpdate(BaseModel):
    role_id: int

# 20. API: Lấy danh sách Người dùng
@app.get("/api/users")
def get_users():
    conn = get_db_connection()
    cursor = conn.cursor()
    sql = """
        SELECT u.id, u.username, u.full_name, u.email, u.phone, u.status, u.created_at, 
               u.role_id, r.role_name 
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.id DESC
    """
    cursor.execute(sql)
    users = cursor.fetchall()
    
    # Lấy luôn danh sách roles để làm dropdown phân quyền bên PHP
    cursor.execute("SELECT * FROM roles")
    roles = cursor.fetchall()
    
    conn.close()
    return {"status": "success", "data": {"users": users, "roles": roles}}

# 21. API: Cập nhật trạng thái (Khóa/Mở khóa)
@app.put("/api/users/{user_id}/status")
def update_user_status(user_id: int, status_update: UserStatusUpdate):
    if status_update.status not in ['ACTIVE', 'BANNED']:
        raise HTTPException(status_code=400, detail="Trạng thái không hợp lệ")
        
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("UPDATE users SET status = %s WHERE id = %s", (status_update.status, user_id))
        conn.commit()
        return {"status": "success", "message": f"Đã đổi trạng thái thành {status_update.status}"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()

# 22. API: Cập nhật quyền (Role)
@app.put("/api/users/{user_id}/role")
def update_user_role(user_id: int, role_update: UserRoleUpdate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("UPDATE users SET role_id = %s WHERE id = %s", (role_update.role_id, user_id))
        conn.commit()
        return {"status": "success", "message": "Cập nhật quyền thành công"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
# 23. API: Lấy chi tiết 1 người dùng và lịch sử mua hàng
@app.get("/api/users/{user_id}")
def get_user_detail(user_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # 1. Lấy thông tin cá nhân và quyền
    sql_user = """
        SELECT u.id, u.username, u.full_name, u.email, u.phone, u.address, 
               u.avatar_url, u.status, u.created_at, r.role_name 
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = %s
    """
    cursor.execute(sql_user, (user_id,))
    user_info = cursor.fetchone()
    
    if not user_info:
        conn.close()
        raise HTTPException(status_code=404, detail="Không tìm thấy người dùng")
        
    # 2. Lấy lịch sử đơn hàng của người dùng này
    sql_orders = """
        SELECT id, total_price, payment_method, status, created_at 
        FROM orders 
        WHERE user_id = %s 
        ORDER BY created_at DESC
    """
    cursor.execute(sql_orders, (user_id,))
    user_orders = cursor.fetchall()
    
    conn.close()
    
    return {
        "status": "success", 
        "data": {
            "info": user_info,
            "orders": user_orders
        }
    }
# Model mô tả dữ liệu đầu vào cho Nhà cung cấp
class SupplierCreate(BaseModel):
    name: str
    contact_info: str = None
    address: str = None

# 24. API: Lấy danh sách nhà cung cấp
@app.get("/api/suppliers")
def get_suppliers():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM suppliers ORDER BY id DESC")
    suppliers = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": suppliers}

# 25. API: Thêm nhà cung cấp mới
@app.post("/api/suppliers")
def create_supplier(supplier: SupplierCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "INSERT INTO suppliers (name, contact_info, address) VALUES (%s, %s, %s)"
        cursor.execute(sql, (supplier.name, supplier.contact_info, supplier.address))
        conn.commit()
        return {"status": "success", "message": "Thêm nhà cung cấp thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
# 26. API: Lấy chi tiết 1 nhà cung cấp theo ID
@app.get("/api/suppliers/{supplier_id}")
def get_supplier(supplier_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM suppliers WHERE id = %s", (supplier_id,))
    supplier = cursor.fetchone()
    conn.close()
    if not supplier:
        raise HTTPException(status_code=404, detail="Không tìm thấy nhà cung cấp")
    return {"status": "success", "data": supplier}

# 27. API: Cập nhật thông tin nhà cung cấp (PUT)
@app.put("/api/suppliers/{supplier_id}")
def update_supplier(supplier_id: int, supplier: SupplierCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = """
            UPDATE suppliers 
            SET name = %s, contact_info = %s, address = %s
            WHERE id = %s
        """
        cursor.execute(sql, (supplier.name, supplier.contact_info, supplier.address, supplier_id))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không có gì thay đổi hoặc không tìm thấy nhà cung cấp")
        return {"status": "success", "message": "Cập nhật thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()

# 28. API: Xóa nhà cung cấp (DELETE)
@app.delete("/api/suppliers/{supplier_id}")
def delete_supplier(supplier_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "DELETE FROM suppliers WHERE id = %s"
        cursor.execute(sql, (supplier_id,))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không tìm thấy nhà cung cấp để xóa")
        return {"status": "success", "message": "Xóa thành công!"}
    except pymysql.IntegrityError:
        # Bắt lỗi Khóa ngoại: Nếu nhà cung cấp này đang phân phối sản phẩm trong CSDL
        raise HTTPException(status_code=400, detail="Không thể xóa! Nhà cung cấp này đang chứa sản phẩm trong hệ thống.")
    finally:
        conn.close()
# Model mô tả dữ liệu đầu vào cho Bài viết
class ArticleCreate(BaseModel):
    title: str
    content: str
    author_id: int

# 29. API: Lấy danh sách bài viết
@app.get("/api/articles")
def get_articles():
    conn = get_db_connection()
    cursor = conn.cursor()
    sql = """
        SELECT a.*, u.full_name as author_name, u.username 
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        ORDER BY a.created_at DESC
    """
    cursor.execute(sql)
    articles = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": articles}

# 30. API: Thêm bài viết mới
@app.post("/api/articles")
def create_article(article: ArticleCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "INSERT INTO articles (title, content, author_id) VALUES (%s, %s, %s)"
        cursor.execute(sql, (article.title, article.content, article.author_id))
        conn.commit()
        return {"status": "success", "message": "Thêm bài viết thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()

# 31. API: Lấy chi tiết 1 bài viết (Để Sửa)
@app.get("/api/articles/{article_id}")
def get_article(article_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM articles WHERE id = %s", (article_id,))
    article = cursor.fetchone()
    conn.close()
    if not article:
        raise HTTPException(status_code=404, detail="Không tìm thấy bài viết")
    return {"status": "success", "data": article}

# 32. API: Cập nhật bài viết
@app.put("/api/articles/{article_id}")
def update_article(article_id: int, article: ArticleCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "UPDATE articles SET title = %s, content = %s, author_id = %s WHERE id = %s"
        cursor.execute(sql, (article.title, article.content, article.author_id, article_id))
        conn.commit()
        return {"status": "success", "message": "Cập nhật bài viết thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()

# 33. API: Xóa bài viết
@app.delete("/api/articles/{article_id}")
def delete_article(article_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "DELETE FROM articles WHERE id = %s"
        cursor.execute(sql, (article_id,))
        conn.commit()
        return {"status": "success", "message": "Xóa bài viết thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
# 34. API: Nhận file từ PHP và upload lên Cloudinary
@app.post("/api/upload")
def upload_image(file: UploadFile = File(...)):
    try:
        # Tải file thẳng lên Cloudinary
        result = cloudinary.uploader.upload(file.file)
        
        # Trả về đường link URL an toàn (https) của bức ảnh
        return {"status": "success", "url": result.get("secure_url")}
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Lỗi Upload: {str(e)}")
# Model mô tả dữ liệu đầu vào cho Banner
class BannerCreate(BaseModel):
    image_url: str
    link: str = None
    position: str = None

# 35. API: Lấy danh sách banner
@app.get("/api/banners")
def get_banners():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM banners ORDER BY id DESC")
    banners = cursor.fetchall()
    conn.close()
    return {"status": "success", "data": banners}

# 36. API: Thêm banner mới
@app.post("/api/banners")
def create_banner(banner: BannerCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "INSERT INTO banners (image_url, link, position) VALUES (%s, %s, %s)"
        cursor.execute(sql, (banner.image_url, banner.link, banner.position))
        conn.commit()
        return {"status": "success", "message": "Thêm banner thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()

# 37. API: Lấy chi tiết 1 banner
@app.get("/api/banners/{banner_id}")
def get_banner(banner_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM banners WHERE id = %s", (banner_id,))
    banner = cursor.fetchone()
    conn.close()
    if not banner:
        raise HTTPException(status_code=404, detail="Không tìm thấy banner")
    return {"status": "success", "data": banner}

# 38. API: Cập nhật banner
@app.put("/api/banners/{banner_id}")
def update_banner(banner_id: int, banner: BannerCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "UPDATE banners SET image_url = %s, link = %s, position = %s WHERE id = %s"
        cursor.execute(sql, (banner.image_url, banner.link, banner.position, banner_id))
        conn.commit()
        return {"status": "success", "message": "Cập nhật thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()

# 39. API: Xóa banner
@app.delete("/api/banners/{banner_id}")
def delete_banner(banner_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = "DELETE FROM banners WHERE id = %s"
        cursor.execute(sql, (banner_id,))
        conn.commit()
        return {"status": "success", "message": "Xóa thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
# 40. API: Thống kê tổng hợp cho Dashboard
@app.get("/api/dashboard/stats")
def get_dashboard_stats():
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # 1. Các con số tổng quan
        cursor.execute("SELECT COUNT(id) as total FROM users")
        total_users = cursor.fetchone()['total']
        
        cursor.execute("SELECT COUNT(id) as total FROM products")
        total_products = cursor.fetchone()['total']
        
        cursor.execute("SELECT COUNT(id) as total FROM orders")
        total_orders = cursor.fetchone()['total']
        
        cursor.execute("SELECT SUM(total_price) as total FROM orders WHERE status IN ('PAID', 'COMPLETED')")
        result = cursor.fetchone()
        total_revenue = result['total'] if result and result['total'] else 0
        
        # 2. Lấy doanh thu 6 tháng gần nhất (Dành cho biểu đồ)
        sql_chart = """
            SELECT DATE_FORMAT(created_at, '%m/%Y') as month, SUM(total_price) as revenue
            FROM orders
            WHERE status IN ('PAID', 'COMPLETED')
            GROUP BY month
            ORDER BY MAX(created_at) DESC
            LIMIT 6
        """
        cursor.execute(sql_chart)
        chart_data = cursor.fetchall()
        chart_data.reverse() # Đảo ngược lại để tháng cũ xếp trước, tháng mới xếp sau
        
        # 3. Lấy 5 đơn hàng mới nhất
        sql_recent_orders = """
            SELECT o.id, o.total_price, o.status, o.created_at, u.full_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC LIMIT 5
        """
        cursor.execute(sql_recent_orders)
        recent_orders = cursor.fetchall()
        
        return {
            "status": "success", 
            "data": {
                "summary": {
                    "users": total_users,
                    "products": total_products,
                    "orders": total_orders,
                    "revenue": total_revenue
                },
                "chart": chart_data,
                "recent_orders": recent_orders
            }
        }
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        conn.close()
class UserRegister(BaseModel):
    username: str
    password: str
    full_name: str
    email: str

class UserLogin(BaseModel):
    username: str
    password: str
# 41. API: Đăng ký tài khoản mới (LƯU MẬT KHẨU THÔ)
@app.post("/api/register")
def register_user(user: UserRegister):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # Kiểm tra username tồn tại chưa
        cursor.execute("SELECT id FROM users WHERE username = %s", (user.username,))
        if cursor.fetchone():
            raise HTTPException(status_code=400, detail="Tên tài khoản đã tồn tại!")

        # Lưu trực tiếp user.password (Không mã hóa)
        sql = "INSERT INTO users (username, password, full_name, email, role_id) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(sql, (user.username, user.password, user.full_name, user.email, 2))
        conn.commit()
        return {"status": "success", "message": "Đăng ký thành công!"}
    finally:
        conn.close()

# 42. API: Đăng nhập (SO SÁNH MẬT KHẨU THÔ)
@app.post("/api/login")
def login_user(user: UserLogin):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("SELECT * FROM users WHERE username = %s", (user.username,))
        db_user = cursor.fetchone()
        
        # Kiểm tra tài khoản tồn tại VÀ so sánh chuỗi mật khẩu trực tiếp
        if not db_user or user.password != db_user['password']:
            raise HTTPException(status_code=401, detail="Tài khoản hoặc mật khẩu không chính xác!")
        
        if db_user['status'] == 'BANNED':
            raise HTTPException(status_code=403, detail="Tài khoản của bạn đã bị khóa!")

        # Ẩn mật khẩu trước khi trả dữ liệu về cho PHP để bảo mật session
        db_user.pop('password')
        return {"status": "success", "data": db_user}
    finally:
        conn.close()
class FavoriteToggle(BaseModel):
    user_id: int
    product_id: int

# 44. API: Thêm/Bỏ Yêu thích (Toggle Favorite)
@app.post("/api/favorites/toggle")
def toggle_favorite(data: FavoriteToggle):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # Kiểm tra xem sản phẩm đã được user này yêu thích chưa
        cursor.execute("SELECT id FROM favorites WHERE user_id = %s AND product_id = %s", (data.user_id, data.product_id))
        fav = cursor.fetchone()
        
        if fav:
            # Nếu đã có -> Xóa đi (Bỏ yêu thích)
            cursor.execute("DELETE FROM favorites WHERE id = %s", (fav['id'],))
            conn.commit()
            return {"status": "success", "action": "removed", "message": "Đã bỏ yêu thích"}
        else:
            # Nếu chưa có -> Thêm vào CSDL
            cursor.execute("INSERT INTO favorites (user_id, product_id) VALUES (%s, %s)", (data.user_id, data.product_id))
            conn.commit()
            return {"status": "success", "action": "added", "message": "Đã thêm vào yêu thích"}
    finally:
        conn.close()
class CartItem(BaseModel):
    user_id: int
    product_id: int
    quantity: int

# 45. API: Lấy giỏ hàng của người dùng (Có JOIN để lấy thông tin sản phẩm)
@app.get("/api/cart/{user_id}")
def get_user_cart(user_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = """
            SELECT c.id as cart_id, c.quantity, p.*,
                   (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id LIMIT 1) as main_image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = %s
        """
        cursor.execute(sql, (user_id,))
        items = cursor.fetchall()
        return {"status": "success", "data": items}
    finally:
        conn.close()

# 46. API: Thêm/Cập nhật sản phẩm vào giỏ hàng DB
@app.post("/api/cart/add")
def add_to_cart_db(item: CartItem):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # Sử dụng lệnh ON DUPLICATE KEY UPDATE để tự động cộng dồn nếu sản phẩm đã có
        sql = """
            INSERT INTO cart (user_id, product_id, quantity) 
            VALUES (%s, %s, %s)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        """
        cursor.execute(sql, (item.user_id, item.product_id, item.quantity))
        conn.commit()
        return {"status": "success", "message": "Đã cập nhật giỏ hàng trong DB"}
    finally:
        conn.close()

# 47. API: Xóa 1 sản phẩm khỏi giỏ hàng DB
@app.delete("/api/cart/remove/{user_id}/{product_id}")
def remove_from_cart_db(user_id: int, product_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("DELETE FROM cart WHERE user_id = %s AND product_id = %s", (user_id, product_id))
        conn.commit()
        return {"status": "success", "message": "Đã xóa khỏi giỏ hàng"}
    finally:
        conn.close()
