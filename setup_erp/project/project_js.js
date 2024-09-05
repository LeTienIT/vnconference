frappe.ui.form.on('Project', {
    onload: function(frm){
        if(frm.is_new())
        {
            frm.set_value('custom_project_code', 'X.X.X.X.X.X');  
            frm.set_value('project_name', 'X.X.X.X.X.X');  
        }
        else
        {
            frm.set_df_property('custom_is_group','read_only',1);
            frm.set_df_property('custom_project_original_code','read_only',1);
            frm.set_df_property('custom_numerical_order','read_only',1);
        }
        frappe.call({
            method: 'frappe.client.get_list',
            args: {
                doctype: 'Project',
                filters: {'custom_is_group': 1},
                fields: ['name']
            },
            callback: function(r) {
                if (r.message) {
                    let options = 'PROJ-####\n';
                    options += r.message.map(project => project.name).join('\n');
                    frm.set_df_property('custom_project_original_code', 'options', options);
                }
            }
        });
    },
	refresh(frm) {
	    ['expected_start_date', 'expected_end_date', 'custom_khách_hàng', 'custom_người_phụ_trách', 'custom_khách_sạnbệnh_viện', 'custom_tỉnh'].forEach(field => {
	        frm.fields_dict[field].df.onchange = function(){
	            update_project_name(frm);
	        };
	    });
	    toggle_fields(frm);
	    update_custom_project_code(frm);
	},
	custom_is_group: function(frm) {
	    if (frm.is_new()) {
            toggle_fields(frm);
        }
    },
    custom_project_original_code: function(frm) {
        if (frm.is_new()) {
            update_custom_project_code(frm);
        }
    },
    custom_numerical_order: function(frm) {
        update_custom_project_code(frm);
    },
    custom_thời_gian_trong_ngày: function(frm) {
        update_custom_project_code(frm);
    },
    custom_thời_gian: function(frm) {
        update_custom_project_code(frm);
    },
    custom_có_kỹ_thuật: function(frm) {
        update_custom_project_code(frm);
    },
    custom_có_kéo_mạng: function(frm) {
        update_custom_project_code(frm);
    },
    custom_khách_sạn: function(frm) {
        update_custom_project_code(frm);
    },
    custom_ăn: function(frm) {
        update_custom_project_code(frm);
    },
    custom_vé_máy_bay: function(frm) {
        update_custom_project_code(frm);
    },
    custom_dịch_vụ_khác: function(frm) {
        update_custom_project_code(frm);
    },
    custom_cộng_tác_viên: function(frm) {
        update_custom_project_code(frm);
    },
    custom_số_khách: function(frm) {
        update_custom_project_code(frm);
    }
});
function toggle_fields(frm) {
    if (frm.doc.custom_is_group) {
        frm.set_df_property('custom_project_original_code', 'hidden', 0);
        frm.set_df_property('custom_numerical_order', 'hidden', 0);
    } else {
        frm.set_df_property('custom_project_original_code', 'hidden', 1);
        frm.set_df_property('custom_numerical_order', 'hidden', 1);
    }
}
function update_project_name(frm){
    let expected_start_date = frm.doc.expected_start_date || 'X';
    let expected_end_date = frm.doc.expected_end_date || 'X';
    let custom_khách_hàng = frm.doc.custom_khách_hàng || 'X';
    let custom_người_phụ_trách = frm.doc.custom_người_phụ_trách || 'X';
    let custom_khách_sạnbệnh_viện = frm.doc.custom_khách_sạnbệnh_viện || 'X';
    let custom_tỉnh = frm.doc.custom_tỉnh ||'X';
    
    expected_start_date = String(expected_start_date);
    expected_end_date = String(expected_end_date);
    custom_khách_hàng = String(custom_khách_hàng);
    custom_người_phụ_trách = String(custom_người_phụ_trách);
    custom_khách_sạnbệnh_viện = String(custom_khách_sạnbệnh_viện);
    custom_tỉnh = String(custom_tỉnh);
    
    let new_project_name = `${expected_start_date}.${expected_end_date}.${custom_khách_hàng}.${custom_người_phụ_trách}.${custom_khách_sạnbệnh_viện}.${custom_tỉnh}`;
    frm.set_value('project_name', new_project_name);
}
function update_custom_project_code(frm) {
    if(frm.is_new())
    {
        let project_code = frm.doc.custom_project_original_code || '';
    
        if (frm.doc.custom_numerical_order) {
            project_code += '.' + frm.doc.custom_numerical_order;
        }
    
        if (frm.doc.custom_thời_gian_trong_ngày) {
            project_code += '.' + frm.doc.custom_thời_gian_trong_ngày;
        }
        if (frm.doc.custom_thời_gian) {
            project_code += '.' + frm.doc.custom_thời_gian;
        }
    
        const checkboxes = [
            'custom_có_kỹ_thuật',
            'custom_có_kéo_mạng',
            'custom_khách_sạn',
            'custom_ăn',
            'custom_xe',
            'custom_vé_máy_bay',
            'custom_dịch_vụ_khác',
            'custom_cộng_tác_viên'
        ];
        const checkboxes_value = [
            'Kỹ thuật',
            'Kéo mạng',
            'Khách sạn',
            'Ăn',
            'Xe',
            'Vé máy bay',
            'Dịch vụ khác',
            'Cộng tác viên'
        ];
    
        checkboxes.forEach((field,index) => {
            if (frm.doc[field]) {
                project_code += '.' + checkboxes_value[index];
            }
        });
    
        if (frm.doc.custom_số_khách) {
            project_code += '.' + frm.doc.custom_số_khách;
        }
    
        frm.set_value('custom_project_code', project_code);
    }
}


