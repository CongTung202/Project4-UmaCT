# umact_api/main.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pymysql

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