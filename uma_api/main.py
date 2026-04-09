# umact_api/main.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pymysql
from datetime import datetime
from typing import Optional

app = FastAPI(title="UmaCT API")

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

# 6. API: Lấy danh sách sản phẩm (Có JOIN để lấy tên danh mục và nhà cung cấp)
@app.get("/api/products")
def get_products():
    conn = get_db_connection()
    cursor = conn.cursor()
    sql = """
        SELECT p.*, c.name as category_name, s.name as supplier_name 
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
        sql = """
            INSERT INTO products (category_id, supplier_id, name, description, price, stock_quantity, is_active) 
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (
            product.category_id, product.supplier_id, product.name, 
            product.description, product.price, product.stock_quantity, product.is_active
        ))
        conn.commit()
        return {"status": "success", "message": "Thêm sản phẩm thành công!"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Lỗi: {str(e)}")
    finally:
        conn.close()
# 8. API: Lấy chi tiết 1 sản phẩm theo ID
@app.get("/api/products/{product_id}")
def get_product(product_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM products WHERE id = %s", (product_id,))
    product = cursor.fetchone()
    conn.close()
    if not product:
        raise HTTPException(status_code=404, detail="Không tìm thấy sản phẩm")
    return {"status": "success", "data": product}

# 9. API: Cập nhật sản phẩm (PUT)
@app.put("/api/products/{product_id}")
def update_product(product_id: int, product: ProductCreate):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        sql = """
            UPDATE products 
            SET category_id = %s, supplier_id = %s, name = %s, 
                description = %s, price = %s, stock_quantity = %s, is_active = %s
            WHERE id = %s
        """
        cursor.execute(sql, (
            product.category_id, product.supplier_id, product.name, 
            product.description, product.price, product.stock_quantity, product.is_active, product_id
        ))
        conn.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Không có gì thay đổi hoặc không tìm thấy sản phẩm")
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