# Tự động quá hạn các task khi chưa xong! => before submit
if doc.workflow_state == 'Completed':  
    tasks = frappe.get_all('Task', filters={'project': doc.name}, fields=['name', 'workflow_state'])
        
    for task in tasks:
        if task.workflow_state != 'Completed': 
            task_doc = frappe.get_doc("Task", task.name)
            
            # Sử dụng phương thức của doc để thay đổi trạng thái workflow
            task_doc.update({
                'workflow_state': 'Overdue',
                'status': 'Overdue'
            })
            
            task_doc.save(ignore_permissions=True)
            # Submit the document if needed
            task_doc.submit()