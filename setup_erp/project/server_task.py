# Không cho tạo task với project đã xong! => before insert
if doc.project:
    project_status = frappe.db.get_value('Project', doc.project, 'status')
        
    if project_status == 'Completed':
        frappe.throw('Không thể tạo Task mới vì Project đã hoàn thành.')