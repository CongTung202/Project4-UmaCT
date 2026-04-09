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