
# hooks.py
# "yearly": [
#         "your_app.letien_code.update_years_of_service_and_age"
#     ] 
# bench execute erpnext.letien_code.update_years_of_service_and_age --site vnc

import frappe
from datetime import datetime


# Cập nhật hằng năm, tự động tăng tuổi + thâm niên + reset ngày nghỉ năm về 0
def update_years_of_service():
    employees = frappe.get_all('Employee', fields=['name', 'custom_ngày_ký_hđlđ_chính_thức','custom_số_tuổi','custom_nghỉ_phép_năm'])
    current_date = datetime.now()
    for emp in employees:
        if emp.custom_ngày_ký_hđlđ_chính_thức:
            effective_date = datetime.strptime(emp.custom_ngày_ký_hđlđ_chính_thức, '%Y-%m-%d')
            time_diff = current_date - effective_date 
            years_of_service = time_diff.days / 365.25  
            frappe.db.set_value('Employee', emp.name, 'custom_thâm_niên', round(years_of_service, 2))
            
        if emp.custom_số_tuổi is not None:
            new_age = int(emp.custom_số_tuổi) + 1
            frappe.db.set_value('Employee', emp.name, 'custom_số_tuổi', new_age)
            
        if current_date.month == 1 and current_date.day == 1:
            frappe.db.set_value('Employee', emp.name, 'custom_nghỉ_phép_năm', 0)
            
    frappe.db.commit() 

# Cập nhật hàng tháng tự động tăng ngày nghỉ hằng năm lên +1 sau mỗi tháng <Đẫ test console ok>
def update_month_of_service():
    employees = frappe.get_all('Employee', fields=['name', 'custom_nghỉ_phép_năm'])
    for emp in employees:
        if emp.custom_nghỉ_phép_năm is not None:
            new_ngay_nghi = int(emp.custom_nghỉ_phép_năm) + 1
            frappe.db.set_value('Employee', emp.name, 'custom_nghỉ_phép_năm',new_ngay_nghi)
            
    frappe.db.commit() 
    
# API CALL tự đông trừ đi ngày nghỉ nếu tính phép
@frappe.whitelist()
def update_leave_balance(ma_nhan_vien, leave_days):
    try:
        # Lấy thông tin nhân viên
        employee = frappe.get_doc("Employee", ma_nhan_vien)
        
        # Cập nhật trường custom_nghỉ_phép_năm
        if employee.custom_nghỉ_phép_năm is None:
            employee.custom_nghỉ_phép_năm = 0
        else:
            employee.custom_nghỉ_phép_năm = float(employee.custom_nghỉ_phép_năm)
        
        # Cập nhật custom_nghi_phep_nam
        employee.custom_nghỉ_phép_năm -= float(leave_days)
        employee.save()
        
        return {"status": "success", "message": "Leave balance updated successfully"}
    except Exception as e:
        return {"status": "error", "message": str(e)}
    
# Tự động cập nhật projject code cho tương đống với mã project pr
def replace_proj_code(doc,method):
    if (doc.custom_project_code and doc.custom_project_code.startswith('PROJ-####')):
        doc.custom_project_code = doc.custom_project_code.replace('PROJ-####',doc.name)

# Tự động cập nhật chuyển rạng thái của projject từ đang làm sang quá hạn khi ngày kết thúc < ngyaf hiện tại <test console OK>
def update_overdue_projects():
    today = nowdate();
    overdue_projects = frappe.get_all('Project', filters={'expected_end_date': ('<', today), 'workflow_state':'Pending'})
    for project in overdue_projects:
        project_doc = frappe.get_doc('Project', project.name)
        if project_doc.workflow_state != 'Overdue':
            project_doc.workflow_state = 'Overdue';project_doc.save(ignore_permissions=True);
            frappe.db.commit();